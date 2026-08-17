<?php
/**
 * Central database connection (Task 2: parameterized queries / prepared statements)
 *
 * PDO is configured with ATTR_EMULATE_PREPARES = false so that PHP does NOT
 * simulate parameter binding - MySQL itself separates the query plan from the
 * data. This is what actually defeats SQL injection, not just escaping.
 */
require_once __DIR__ . '/env.php';

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $name = $_ENV['DB_NAME'] ?? 'student_registration';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // real server-side prepared statements
        ]);
    } catch (PDOException $e) {
        // Never echo $e->getMessage() to the browser - it can leak schema/credentials
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        die('A server error occurred. Please try again later.');
    }

    return $pdo;
}
