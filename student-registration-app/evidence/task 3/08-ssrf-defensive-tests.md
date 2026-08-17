# Evidence 08: SSRF Defensive Tests

Automated test files:

- `tests/integration/ssrf-defence.test.js`
- `tests/unit/urlPreviewService.test.js`

Tests covered:

- Approved allowlisted destination succeeds.
- Malformed URL is rejected.
- Unsupported protocol is rejected.
- URL credentials are rejected.
- Loopback, private, link-local, IPv6 loopback, IPv4-mapped restricted, metadata-address, and non-allowlisted destinations are rejected.
- Redirect responses are rejected.
- Request timeout is enforced.
- Oversized response is rejected safely.
- Rejected destinations and internal DNS errors return safe messages.

Latest recorded result during Phase 4C implementation:

```text
Test Suites: 11 passed, 11 total
Tests: 78 passed, 78 total
```

Tests used mocked DNS/fetch behavior and synthetic values only.
