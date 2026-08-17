# Phase 5 Evidence 15: Redacted Security Logs

These are safe illustrative records using fictitious local lab data. They do not contain passwords, tokens, cookies, session IDs, request bodies, or real personal data.

```text
action: authentication_failure
user_id: 1
resource: route:/login;identifier:<hashed-login-identifier>
result: failure
ip_address: <local-test-ip>
user_agent: <local-test-user-agent>
created_at: <database timestamp>
```

```text
action: authentication_success
user_id: 1
resource: route:/login
result: success
ip_address: <local-test-ip>
user_agent: <local-test-user-agent>
created_at: <database timestamp>
```

```text
action: authorization_denied
user_id: 1
resource: route:/admin/dashboard;reason:insufficient_role;role:student
result: denied
ip_address: <local-test-ip>
user_agent: <local-test-user-agent>
created_at: <database timestamp>
```

```text
action: validation_rejected
user_id: 1
resource: route:/student/profile;field:email,firstName;reason:invalid_profile_input
result: rejected
ip_address: <local-test-ip>
user_agent: <local-test-user-agent>
created_at: <database timestamp>
```

```text
action: csrf_validation_failed
user_id: 1
resource: route:/student/profile;reason:token_mismatch
result: failure
ip_address: <local-test-ip>
user_agent: <local-test-user-agent>
created_at: <database timestamp>
```

```text
action: ssrf_destination_rejected
user_id: 1
resource: route:/tools/url-preview;reason:url_credentials_present
result: rejected
ip_address: <local-test-ip>
user_agent: <local-test-user-agent>
created_at: <database timestamp>
```

Redaction check:

- Login identifiers are hashed before storage.
- Raw validation input is not stored.
- CSRF token values are not stored.
- Submitted SSRF URLs and URL credentials are not stored.
