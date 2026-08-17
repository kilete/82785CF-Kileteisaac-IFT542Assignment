<?php
/**
 * Minimal test harness - no external dependencies (no Composer/PHPUnit
 * needed, since network access for `composer install` may not be available
 * in every lab environment). Uses cURL to talk to your running XAMPP app
 * exactly like a browser would, including cookies for session/CSRF flow.
 */

class TestHarness
{
    private string $baseUrl;
    private string $cookieJar;
    private int $passed = 0;
    private int $failed = 0;
    /** @var array<int,array{name:string,ok:bool,note:string}> */
    private array $results = [];

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'ift542_cookies_');
    }

    public function resetSession(): void
    {
        if (file_exists($this->cookieJar)) {
            unlink($this->cookieJar);
        }
        touch($this->cookieJar);
    }

    /** Perform a GET and return [httpCode, body] */
    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /** Perform a POST with form fields and return [httpCode, body] */
    public function post(string $path, array $fields): array
    {
        return $this->request('POST', $path, $fields);
    }

    private function request(string $method, string $path, array $fields = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR      => $this->cookieJar,
            CURLOPT_COOKIEFILE     => $this->cookieJar,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 5,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, (string)$body];
    }

    /** Extract the CSRF token hidden input from an HTML page body */
    public function extractCsrf(string $html): ?string
    {
        if (preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $html, $m)) {
            return $m[1];
        }
        return null;
    }

    public function assertTrue(string $name, bool $condition, string $note = ''): void
    {
        if ($condition) {
            $this->passed++;
            $this->results[] = ['name' => $name, 'ok' => true, 'note' => $note];
        } else {
            $this->failed++;
            $this->results[] = ['name' => $name, 'ok' => false, 'note' => $note];
        }
    }

    public function summary(): void
    {
        echo "\n============================================================\n";
        echo " TEST RESULTS\n";
        echo "============================================================\n";
        foreach ($this->results as $r) {
            $status = $r['ok'] ? 'PASS' : 'FAIL';
            echo sprintf("[%s] %s%s\n", $status, $r['name'], $r['note'] ? ' - ' . $r['note'] : '');
        }
        echo "------------------------------------------------------------\n";
        echo "Total: " . ($this->passed + $this->failed) . "  Passed: {$this->passed}  Failed: {$this->failed}\n";
        echo "============================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }
}
