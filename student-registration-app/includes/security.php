<?php
/**
 * Shared security helpers used on every page.
 * Include this BEFORE any HTML output.
 */
require_once __DIR__ . '/../config/db.php';

/* ---------------------------------------------------------------
 * 1. Hardened session start (Task 2: session-ID regeneration)
 * ------------------------------------------------------------- */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,   // set true once served over HTTPS
        'httponly' => true,    // JS cannot read the session cookie (XSS mitigation)
        'samesite' => 'Lax',   // CSRF mitigation for cross-site requests
    ]);
    session_start();
}

/* ---------------------------------------------------------------
 * 2. Security headers (Task 3: misconfiguration + XSS via CSP)
 * ------------------------------------------------------------- */
function send_security_headers(): void
{
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // Only send HSTS once you're actually on HTTPS in your demo:
    // header('Strict-Transport-Security: max-age=63072000; includeSubDomains');
    header_remove('X-Powered-By'); // don't advertise PHP version (misconfiguration)
}

/* ---------------------------------------------------------------
 * 3. CSRF token helpers (Task 3: CSRF protection)
 * ------------------------------------------------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function verify_csrf(?string $submitted): bool
{
    if (empty($_SESSION['csrf_token']) || empty($submitted)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submitted);
}

function require_csrf(): void
{
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        log_event('CSRF_REJECTED', $_SESSION['user_email'] ?? null, 'Bad/missing token on ' . ($_SERVER['REQUEST_URI'] ?? ''));
        http_response_code(403);
        die('Request could not be verified. Please go back and try again.');
    }
}

/* ---------------------------------------------------------------
 * 4. Output encoding helper (Task 3: contextual output encoding / XSS)
 * ------------------------------------------------------------- */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------------
 * 5. Audit logging (Task 3: who/what/when, no secrets)
 * ------------------------------------------------------------- */
function log_event(string $eventType, ?string $username, ?string $detail = null): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    try {
        $stmt = get_db()->prepare(
            'INSERT INTO audit_log (event_type, username, ip_address, detail) VALUES (:t, :u, :ip, :d)'
        );
        $stmt->execute([
            ':t'  => $eventType,
            ':u'  => $username,
            ':ip' => $ip,
            ':d'  => $detail,
        ]);
    } catch (Throwable $e) {
        error_log('Audit log write failed: ' . $e->getMessage());
    }
}

/* ---------------------------------------------------------------
 * 6. Rate limiting / temporary lockout (Task 2 extra control)
 *    Simple DB-backed lockout tied to the users table.
 * ------------------------------------------------------------- */
const MAX_FAILED_ATTEMPTS = 5;
const LOCKOUT_MINUTES      = 15;

function is_locked_out(array $user): bool
{
    if (empty($user['locked_until'])) {
        return false;
    }
    return strtotime($user['locked_until']) > time();
}

function register_failed_attempt(int $userId, int $currentFailedAttempts): void
{
    $pdo = get_db();
    $attempts = $currentFailedAttempts + 1;

    if ($attempts >= MAX_FAILED_ATTEMPTS) {
        $lockedUntil = date('Y-m-d H:i:s', time() + (LOCKOUT_MINUTES * 60));
        $stmt = $pdo->prepare(
            'UPDATE users SET failed_attempts = :a, locked_until = :l WHERE id = :id'
        );
        $stmt->execute([':a' => $attempts, ':l' => $lockedUntil, ':id' => $userId]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET failed_attempts = :a WHERE id = :id');
        $stmt->execute([':a' => $attempts, ':id' => $userId]);
    }
}
function reset_failed_attempts(int $userId): void
{
    $stmt = get_db()->prepare(
        'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id'
    );
    $stmt->execute([':id' => $userId]);
}

/* ---------------------------------------------------------------
 * 7. Auth guards
 * ------------------------------------------------------------- */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function require_role(string $role): void
{
    require_login();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        log_event('ACCESS_DENIED', $_SESSION['user_email'] ?? null, "Attempted to access $role-only resource");
        http_response_code(403);
        die('You do not have permission to view this page.');
    }
}
