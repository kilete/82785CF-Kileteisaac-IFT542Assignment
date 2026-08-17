<?php
declare(strict_types=1);

$appDebug = getenv('APP_DEBUG');
if ($appDebug === '1') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0'); // Task 3: never leak stack traces in "production" demo
    error_reporting(E_ALL);
}

require_once __DIR__ . '/security.php';

start_secure_session();
send_security_headers();
