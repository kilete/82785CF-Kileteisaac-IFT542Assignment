<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $matric   = trim($_POST['matric_no'] ?? '');
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // --- Validation ---
    $errors = [];
    if (!preg_match('/^\d{4}\/\d\/\d{5}[A-Z]{2}$/', $matric)) {
        $errors[] = 'Matric number format is invalid (e.g. 2020/1/00001CS).';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Full name must be between 2 and 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'A valid email address is required.';
    }
    if (strlen($password) < 10) {
        $errors[] = 'Password must be at least 10 characters.';
    }

    if ($errors) {
        log_event('VALIDATION_REJECTED', $email, 'Registration form failed validation');
        $error = implode(' ', $errors);
    } else {
        $pdo = get_db();

        // Check for existing email/matric using a parameterized query
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email OR matric_no = :matric');
        $check->execute([':email' => $email, ':matric' => $matric]);

        if ($check->fetch()) {
            // Generic message - don't reveal which field collided (info disclosure)
            $error = 'Registration could not be completed with the details provided.';
        } else {
            // Argon2id: approved slow salted hashing function (Task 2)
            $hash = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 1,
            ]);

            $insert = $pdo->prepare(
                'INSERT INTO users (matric_no, full_name, email, password_hash, role)
                 VALUES (:matric, :name, :email, :hash, "student")'
            );
            $insert->execute([
                ':matric' => $matric,
                ':name'   => $name,
                ':email'  => $email,
                ':hash'   => $hash,
            ]);

            log_event('REGISTER_SUCCESS', $email, null);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Student Registration</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main class="auth-box">
    <h1>Create Account</h1>

    <?php if ($success): ?>
        <p class="alert alert-success">Account created. You can now <a href="/login.php">log in</a>.</p>
    <?php else: ?>
        <?php if ($error): ?>
            <p class="alert alert-error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="/register.php" novalidate>
            <?= csrf_field() ?>
            <label for="matric_no">Matric Number</label>
            <input type="text" id="matric_no" name="matric_no" maxlength="20" required
                   placeholder="2020/1/00001CS" value="<?= e($_POST['matric_no'] ?? '') ?>">

            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" maxlength="100" required
                   value="<?= e($_POST['full_name'] ?? '') ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150" required
                   value="<?= e($_POST['email'] ?? '') ?>">

            <label for="password">Password (min 10 characters)</label>
            <input type="password" id="password" name="password" minlength="10" maxlength="200" required>

            <button type="submit">Register</button>
        </form>
    <?php endif; ?>
    <p><a href="/login.php">Already have an account? Log in</a></p>
</main>
</body>
</html>
