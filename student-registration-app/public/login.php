<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // --- Input validation (Task 2: validate type/length/format) ---
    $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 150;
    $validPass  = strlen($password) >= 1 && strlen($password) <= 200;

    if (!$validEmail || !$validPass) {
        log_event('VALIDATION_REJECTED', $email, 'Login form failed input validation');
        $error = 'Invalid email or password.'; // generic - never say "bad format"
    } else {
        // --- Parameterized lookup (Task 2: fixes SQL injection) ---
        // Compare with vulnerable-examples/login_vulnerable.php for the "before" state.
        $stmt = get_db()->prepare(
            'SELECT id, full_name, email, password_hash, role, failed_attempts, locked_until
             FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Same generic error whether the account exists or not
            log_event('LOGIN_FAIL', $email, 'No matching account');
            $error = 'Invalid email or password.';
        } elseif (is_locked_out($user)) {
            log_event('LOGIN_FAIL', $email, 'Account temporarily locked');
            $error = 'This account is temporarily locked. Please try again later.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            register_failed_attempt((int)$user['id'], (int)$user['failed_attempts']);
            log_event('LOGIN_FAIL', $email, 'Bad password');
            $error = 'Invalid email or password.';
        } else {
            // Success
            reset_failed_attempts((int)$user['id']);

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_role']  = $user['role'];

            log_event('LOGIN_SUCCESS', $email, null);

            header('Location: ' . ($user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Student Registration</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main class="auth-box">
    <h1>Student Login</h1>

    <?php if ($error): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/login.php" novalidate>
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" maxlength="150" required
               value="<?= e($_POST['email'] ?? '') ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" maxlength="200" required>

        <button type="submit">Log in</button>
    </form>
    <p><a href="/register.php">Create an account</a></p>
</main>
</body>
</html>
