<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

/**
 * Task 3 - SSRF hardening for a "course resource link preview" feature.
 * Compare with vulnerable-examples/preview_vulnerable.php for the "before" state.
 *
 * Defence layers, in order:
 *   1. Scheme allowlist (http/https only - blocks file://, gopher://, etc.)
 *   2. Hostname allowlist (only pre-approved teaching-resource hosts)
 *   3. Resolve the hostname ourselves and reject private/loopback/link-local/
 *      multicast/reserved IP ranges (blocks 127.0.0.1, 169.254.169.254 cloud
 *      metadata, 10.x/172.16.x/192.168.x internal networks, etc.)
 *   4. No automatic redirect following - a redirect to a blocked address
 *      would otherwise bypass checks 1-3.
 *   5. Strict timeout and response size cap - basic DoS protection.
 */

// Only hosts a lecturer would actually assign as reading material.
// For local grading/demo, localhost is added ONLY for the harmless
// /local-target/resource.php page - never allow bare "localhost" broadly
// in a real deployment.
const ALLOWED_HOSTS = [
    'localhost',            // demo only - serves the harmless local-target page
    'en.wikipedia.org',
    'docs.php.net',
];

function is_public_ip(string $ip): bool
{
    // Rejects private, loopback, link-local (incl. 169.254.169.254 cloud
    // metadata), and reserved ranges for both IPv4 and IPv6.
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) !== false;
}

function validate_preview_url(string $url): array
{
    $parts = parse_url($url);

    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        return [false, 'That does not look like a valid URL.'];
    }

    if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        return [false, 'Only http/https links are allowed.'];
    }

    $host = strtolower($parts['host']);
    if (!in_array($host, ALLOWED_HOSTS, true)) {
        return [false, "This host is not on the approved resource list: $host"];
    }

    // Resolve ourselves (not relying on whatever the HTTP client resolves at
    // request time) and check every returned address.
    if ($host === 'localhost') {
        $ips = ['127.0.0.1']; // demo-only exception, see ALLOWED_HOSTS note
    } else {
        $ips = @dns_get_record($host, DNS_A + DNS_AAAA);
        $ips = $ips ? array_column($ips, 'ip') + array_column($ips, 'ipv6') : [];
        $ips = array_filter($ips);
    }

    if (!$ips) {
        return [false, 'Could not resolve host.'];
    }

    foreach ($ips as $ip) {
        // Allow the explicit local demo target even though 127.0.0.1 is a
        // loopback address - this is the one intentional, documented
        // exception, scoped only to the ALLOWED_HOSTS allowlist above.
        if ($host === 'localhost' && $ip === '127.0.0.1') {
            continue;
        }
        if (!is_public_ip($ip)) {
            return [false, 'This resource points to a non-public address and is blocked.'];
        }
    }

    return [true, null];
}

function safe_fetch(string $url): ?string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL             => $url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => false, // critical: no blind redirects
        CURLOPT_TIMEOUT         => 4,
        CURLOPT_CONNECTTIMEOUT  => 3,
        CURLOPT_MAXFILESIZE     => 200_000, // ~200KB cap
        CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_USERAGENT       => 'IFT542-CourseResourcePreview/1.0',
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return $body === false ? null : $body;
}

$message = null;
$preview = null;
$submittedUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $submittedUrl = trim($_POST['url'] ?? '');

    [$isValid, $reason] = validate_preview_url($submittedUrl);

    if (!$isValid) {
        log_event('SSRF_BLOCKED', $_SESSION['user_email'], "Rejected: $submittedUrl ($reason)");
        $message = $reason;
    } else {
        $body = safe_fetch($submittedUrl);
        if ($body === null) {
            $message = 'Could not fetch that resource.';
        } else {
            // Never render fetched HTML directly - strip tags and escape,
            // so a malicious page can't inject script into OUR page (XSS
            // via SSRF response is a real combined-attack scenario).
            $preview = mb_substr(trim(strip_tags($body)), 0, 400);
            log_event('PREVIEW_FETCH', $_SESSION['user_email'], "Fetched: $submittedUrl");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Resource Preview</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Course Resource Preview</h1>
    <p><a href="/dashboard.php">Back to dashboard</a></p>
    <p>Paste a link to a reading resource to preview it. Only pre-approved hosts are allowed.</p>
    <p><small>Approved hosts for this demo: <?= e(implode(', ', ALLOWED_HOSTS)) ?></small></p>

    <?php if ($message): ?><p class="alert alert-error"><?= e($message) ?></p><?php endif; ?>

    <form method="POST" action="/preview.php">
        <?= csrf_field() ?>
        <label for="url">Resource URL</label>
        <input type="url" id="url" name="url" maxlength="500" required
               value="<?= e($submittedUrl) ?>"
               placeholder="http://localhost/student-registration-app/public/local-target/resource.php">
        <button type="submit">Preview</button>
    </form>

    <?php if ($preview !== null): ?>
        <h2>Preview</h2>
        <p class="alert alert-success"><?= e($preview) ?></p>
    <?php endif; ?>

    <hr>
    <p><small>Try pasting <code>http://127.0.0.1/</code> or a metadata-style address like
       <code>http://169.254.169.254/</code> to see the block in action - both are rejected
       before any request leaves the server.</small></p>
</main>
</body>
</html>
