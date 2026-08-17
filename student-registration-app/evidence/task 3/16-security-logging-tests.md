# Phase 5 Evidence 16: Security Logging Tests

Automated test file:

- `tests/integration/security-logging.test.js`

Test coverage:

| Area             | Verification                                                  | Result |
| ---------------- | ------------------------------------------------------------- | ------ |
| Failed login     | Creates `authentication_failure` event                        | PASS   |
| Failed login     | Event has timestamp support through `audit_logs.created_at`   | PASS   |
| Failed login     | Password and password hash absent from log fields             | PASS   |
| Successful login | Creates `authentication_success` event                        | PASS   |
| Authorization    | Student denied admin endpoint                                 | PASS   |
| Authorization    | Creates `authorization_denied` event with safe user/role data | PASS   |
| Authorization    | Session cookie/session credentials absent                     | PASS   |
| Validation       | Invalid profile input rejected                                | PASS   |
| Validation       | Creates `validation_rejected` event with field/category       | PASS   |
| Validation       | Raw sensitive input and CSRF token absent                     | PASS   |
| CSRF             | Invalid CSRF request rejected                                 | PASS   |
| CSRF             | Creates `csrf_validation_failed` event                        | PASS   |
| CSRF             | Submitted CSRF token absent                                   | PASS   |
| SSRF             | URL credentials rejected                                      | PASS   |
| SSRF             | Creates `ssrf_destination_rejected` event                     | PASS   |
| SSRF             | Full URL and URL credentials absent                           | PASS   |

Local command result:

```text
PASS tests/integration/security-logging.test.js
Tests: 6 passed, 6 total
```

Full-suite result is recorded in the final Phase 5 response.
