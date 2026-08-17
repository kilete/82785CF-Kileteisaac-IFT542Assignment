# Evidence 03: XSS Defensive Tests

Automated test file:

- `tests/integration/xss-defence.test.js`

Tests covered:

- Profile page renders successfully for an authenticated student.
- User-controlled profile text is HTML-escaped in attribute context.
- Raw markup from the stored profile value is not present in the response.
- Normal profile text still renders correctly.
- CSP header is present.
- CSP includes restrictive directives.
- Existing Helmet security headers remain enabled.

Latest recorded result during Phase 4A implementation:

```text
Test Suites: 8 passed, 8 total
Tests: 38 passed, 38 total
```

No fake screenshots were generated.
