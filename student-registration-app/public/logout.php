<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$email = $_SESSION['user_email'] ?? null;
log_event('LOGOUT', $email, null);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: /login.php');
exit;
