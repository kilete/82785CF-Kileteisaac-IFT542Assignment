<?php
/**
 * Automated security test run for the IFT542 Student Registration app.
 *
 * WHAT THIS PROVES (maps to assignment "Evidence to Submit" requirements):
 *   - Valid login works
 *   - Invalid credentials are rejected with a generic message
 *   - SQL-injection-style input does not change query meaning / bypass login
 *   - Stored passwords are not plaintext (Argon2id hash format in DB)
 *   - CSRF-unprotected requests to a state-changing endpoint are rejected
 *   - A student session cannot reach the admin panel (access control)
 *   - Repeated failed logins trigger a temporary lockout (rate limiting)
 *
 * USAGE
 *   1. Make sure Apache + MySQL are running in XAMPP and the app is
 *      reachable at the URL below (edit if your path differs).
 *   2. From a terminal:  php tests/run_tests.php
 *   3. Read the PASS/FAIL summary at the end. Redirect to a file for your
 *      evidence folder:  php tests/run_tests.php > ../evidence/test-results.txt
 *
 * NOTE: This script creates one throwaway fictitious test account per run
 * (random email) so it never touches your seeded demo accounts or needs
 * you to pre-generate hashes. Safe to run repeatedly.
 */

require_once __DIR__ . '/TestHarness.php';
require_once __DIR__ . '/../config/db.php';

// Adjust this if your XAMPP path/vhost differs.
$BASE_URL = 'http://studentapp.local';

$t = new TestHarness($BASE_URL);

// ------------------------------------------------------------------
// Setup: register a fresh, fictitious test account through the real
// registration flow (so the password is hashed exactly as it would be
// for a real user).
// ------------------------------------------------------------------
$rand       = bin2hex(random_bytes(4));
$testEmail  = "testuser.$rand@example.local";
$testPass   = 'TestPass!2026xyz';
$testMatric = '2020/1/' . str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT) . 'CS';

$t->resetSession();
[, $regPage] = $t->get('/register.php');
$csrf = $t->extractCsrf($regPage);
$t->assertTrue('Setup: registration page loads with CSRF token', $csrf !== null);

[$code, $body] = $t->post('/register.php', [
    'csrf_token' => $csrf,
    'matric_no'  => $testMatric,
    'full_name'  => 'Automated Test User',
    'email'      => $testEmail,
    'password'   => $testPass,
]);
$t->assertTrue('Setup: throwaway test account registers successfully', str_contains($body, 'Account created'));

// ------------------------------------------------------------------
// Test 1: Valid login works
// ------------------------------------------------------------------
$t->resetSession();
[, $loginPage] = $t->get('/login.php');
$csrf = $t->extractCsrf($loginPage);

[$code, $body] = $t->post('/login.php', [
    'csrf_token' => $csrf,
    'email'      => $testEmail,
    'password'   => $testPass,
]);
$t->assertTrue('Valid login redirects (302) to dashboard', $code === 302, "HTTP $code");

[$code, $dashBody] = $t->get('/dashboard.php');
$t->assertTrue('Dashboard accessible after valid login', $code === 200 && str_contains($dashBody, 'Welcome'));

// ------------------------------------------------------------------
// Test 2: Invalid credentials rejected, generic message
// ------------------------------------------------------------------
$t->resetSession();
[, $loginPage] = $t->get('/login.php');
$csrf = $t->extractCsrf($loginPage);
[$code, $body] = $t->post('/login.php', [
    'csrf_token' => $csrf,
    'email'      => $testEmail,
    'password'   => 'WrongPassword123!',
]);
$t->assertTrue('Wrong password shows generic "Invalid email or password" error', str_contains($body, 'Invalid email or password'));

$t->resetSession();
[, $loginPage] = $t->get('/login.php');
$csrf = $t->extractCsrf($loginPage);
[$code, $body] = $t->post('/login.php', [
    'csrf_token' => $csrf,
    'email'      => 'no-such-account@example.local',
    'password'   => 'whatever123',
]);
$t->assertTrue('Non-existent account shows the SAME generic error (no user enumeration)', str_contains($body, 'Invalid email or password'));

// ------------------------------------------------------------------
// Test 3: SQL-injection-style input does not bypass login
// ------------------------------------------------------------------
$t->resetSession();
[, $loginPage] = $t->get('/login.php');
$csrf = $t->extractCsrf($loginPage);
[$code, $body] = $t->post('/login.php', [
    'csrf_token' => $csrf,
    'email'      => "' OR '1'='1",
    'password'   => "' OR '1'='1",
]);
$t->assertTrue('Classic SQLi payload does not bypass login', $code !== 302 && str_contains($body, 'Invalid email or password'));

[$code2, $dashBody2] = $t->get('/dashboard.php');
$t->assertTrue('No session was created by the SQLi attempt (dashboard redirects away)', $code2 === 302);

// ------------------------------------------------------------------
// Test 4: Password is not stored in plaintext
// ------------------------------------------------------------------
$pdo = get_db();
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :e');
$stmt->execute([':e' => $testEmail]);
$row = $stmt->fetch();
$hash = $row['password_hash'] ?? '';
$t->assertTrue(
    'Stored password is an Argon2id hash, not plaintext',
    str_starts_with($hash, '$argon2id$') && $hash !== $testPass
);

// ------------------------------------------------------------------
// Test 5: CSRF protection on a state-changing endpoint (course registration)
// ------------------------------------------------------------------
$t->resetSession();
[, $loginPage] = $t->get('/login.php');
$csrf = $t->extractCsrf($loginPage);
$t->post('/login.php', ['csrf_token' => $csrf, 'email' => $testEmail, 'password' => $testPass]);

// Attempt course registration WITHOUT a CSRF token
[$code, $body] = $t->post('/courses.php', ['course_id' => 1]);
$t->assertTrue('Course registration without CSRF token is rejected (403)', $code === 403);

// ------------------------------------------------------------------
// Test 6: Access control - student cannot reach admin panel
// ------------------------------------------------------------------
[$code, $body] = $t->get('/admin/index.php');
$t->assertTrue('Student session gets 403 on admin panel', $code === 403);

// ------------------------------------------------------------------
// Test 7: Lockout after repeated failed logins
// ------------------------------------------------------------------
$t->resetSession();
for ($i = 1; $i <= 5; $i++) {
    [, $loginPage] = $t->get('/login.php');
    $csrf = $t->extractCsrf($loginPage);
    $t->post('/login.php', ['csrf_token' => $csrf, 'email' => $testEmail, 'password' => 'wrong-' . $i]);
}
[, $loginPage] = $t->get('/login.php');
$csrf = $t->extractCsrf($loginPage);
[$code, $body] = $t->post('/login.php', [
    'csrf_token' => $csrf,
    'email'      => $testEmail,
    'password'   => $testPass, // even the CORRECT password should now be blocked
]);
$t->assertTrue('Account is locked after 5 failed attempts (correct password still rejected)', str_contains($body, 'temporarily locked'));

// ------------------------------------------------------------------
$t->summary();
