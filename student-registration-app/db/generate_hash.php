<?php
/**
 * Run this once from the command line to generate a real Argon2id hash
 * for your seed accounts, then paste the output into seed.sql.
 *
 * Usage:  php generate_hash.php "Passw0rd!123"
 */
if ($argc < 2) {
    echo "Usage: php generate_hash.php <plaintext-password>\n";
    exit(1);
}

$hash = password_hash($argv[1], PASSWORD_ARGON2ID, [
    'memory_cost' => 65536, // 64 MB
    'time_cost'   => 4,
    'threads'     => 1,
]);

echo "Hash: " . $hash . "\n";
