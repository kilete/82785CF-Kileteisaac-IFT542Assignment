<?php
/**
 * =============================================================
 * VULNERABLE EXAMPLE - FOR REPORT/DEMONSTRATION PURPOSES ONLY
 * =============================================================
 * This file is NOT wired into the live application and should
 * never be linked from any page. It exists solely so you can:
 *   1. Show this "before" excerpt in Task 2 of your report.
 *   2. Run it against a throwaway/isolated test DB to demonstrate
 *      the flaw with fictitious data.
 *
 * DO NOT deploy this file. DO NOT test it against anything other
 * than your own local isolated database.
 * =============================================================
 */

require_once __DIR__ . '/../config/db.php';

$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// VULNERABLE: raw user input concatenated directly into SQL text.
// An attacker can submit   ' OR '1'='1   as the email to bypass
// the WHERE clause entirely, or terminate the query and inject
// arbitrary SQL, because the input is treated as CODE, not DATA.
$pdo = get_db();
$query = "SELECT * FROM users WHERE email = '$email' AND password_hash = '$password'";
$result = $pdo->query($query);   // no binding, no separation of code and data
$user = $result->fetch();

if ($user) {
    echo "Login successful for: " . $user['full_name']; // also reflects raw output - XSS risk too
} else {
    // Also unsafe: leaks whether the query itself errored, or DB structure,
    // if $email contains a syntax-breaking payload.
    echo "Login failed.";
}

/**
 * WHY THIS IS UNSAFE (for your report):
 * - The attacker-controlled $email/$password are spliced into the SQL string.
 * - MySQL cannot tell the difference between "data" and "query syntax" here.
 * - A crafted email like:  admin.test@example.local' -- 
 *   comments out the password check entirely.
 * - Fix (see public/login.php): use a prepared statement with bound
 *   parameters (:email) so the DB driver sends the query plan and the
 *   data separately - user input can never change the query's meaning.
 */
