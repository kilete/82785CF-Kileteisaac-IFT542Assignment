# Evidence 05: CSRF Tests

Automated test file:

- `tests/integration/csrf-defence.test.js`

Tests covered:

- Profile update with valid CSRF token succeeds.
- Profile update without token is rejected.
- Profile update with invalid token is rejected.
- Rejected profile update does not call the update service.
- Course registration with valid CSRF token succeeds.
- Course registration without token is rejected.
- Course registration with invalid token is rejected.
- Rejected course registration does not call the enrollment service.
- Token from another session is rejected.
- Session cookie has `SameSite=Lax`.
- Session cookie remains `HttpOnly`.

Latest recorded result during Phase 4B implementation:

```text
Test Suites: 9 passed, 9 total
Tests: 49 passed, 49 total
```

No external targets were tested.
