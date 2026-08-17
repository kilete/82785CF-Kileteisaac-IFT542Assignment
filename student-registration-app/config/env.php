<?php
/**
 * Minimal .env loader - avoids committing secrets to source control.
 * (Task 3: "protect secrets")
 */
function load_env(string $path): void
{
    if (!file_exists($path)) {
        // Fail loudly but without leaking a stack trace to the browser
        http_response_code(500);
        error_log("Missing .env file at $path");
        die('Server configuration error. Please contact the administrator.');
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = array_map('trim', explode('=', $line, 2));
	$key = $parts[0];
	$value = $parts[1] ?? '';
        if (!array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

load_env(__DIR__ . '/../.env');
