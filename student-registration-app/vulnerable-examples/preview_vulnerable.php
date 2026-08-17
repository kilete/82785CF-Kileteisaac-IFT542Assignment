<?php
/**
 * =============================================================
 * VULNERABLE EXAMPLE - FOR REPORT/DEMONSTRATION PURPOSES ONLY
 * =============================================================
 * Not wired into the live application. Demonstrates the SSRF flaw
 * that public/preview.php fixes. Test only against your own local
 * targets (e.g. public/local-target/resource.php).
 * =============================================================
 */

$url = $_POST['url'] ?? '';

// VULNERABLE: fetches ANY attacker-supplied URL with no validation.
// An attacker on the same host/network could submit:
//   http://127.0.0.1:3306/                (probe internal services)
//   http://169.254.169.254/latest/meta-data/   (cloud metadata theft)
//   file:///etc/passwd                     (if the client allows the file:// scheme)
// and the server will happily fetch it and return the response,
// effectively turning the server into a proxy into its own internal network.
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // also lets a redirect bypass any later check
$body = curl_exec($ch);
curl_close($ch);

echo $body; // also unsafe: raw response rendered without escaping (XSS risk)

/**
 * WHY THIS IS UNSAFE (for your report):
 * - No scheme restriction: file://, gopher://, dict:// etc. may be usable
 *   depending on the cURL build, expanding the attack far past "just SSRF".
 * - No host allowlist: any hostname the attacker names gets resolved and fetched.
 * - No IP-range check: loopback, private, and cloud metadata addresses are
 *   all reachable, which is the classic path to stealing cloud credentials.
 * - CURLOPT_FOLLOWLOCATION is enabled: even if the initial host looked safe,
 *   a 302 redirect can silently retarget the request to an internal address.
 * - Fix (see public/preview.php): allowlist scheme + host, resolve DNS
 *   ourselves and reject private/loopback/reserved ranges, disable redirects,
 *   cap response size/timeout, and never render the fetched body as raw HTML.
 */
