# Phase 6 Evidence 17: Redacted Incident Record

## SIMULATED LAB INCIDENT - NOT A REAL SECURITY INCIDENT

This is a redacted submission-ready summary of a controlled localhost laboratory simulation. It does not describe a real incident, real compromise, external system, or real personal data.

| Field            | Redacted Record                                                       |
| ---------------- | --------------------------------------------------------------------- |
| Incident ID      | `IFT542-LAB-001`                                                      |
| Date/Time        | Fictitious laboratory timestamp: `2026-08-12 14:30 UTC`               |
| Detection Source | Local application security audit logs                                 |
| Event            | Repeated failed authentication attempts followed by temporary lockout |
| Initial Severity | Medium                                                                |
| Affected Asset   | Student Registration Web Application authentication service           |
| Affected Account | Fictitious test account represented by safe hashed identifier only    |

## Detection

Repeated `authentication_failure` events were observed in the local lab audit trail for route `/login`. The application generated database timestamps and safe account identifiers. No password, password hash, session id, cookie, CSRF token, authorization token, database credential, or environment secret was recorded.

## Containment

- Temporary account lockout activated.
- Local sessions were reviewed.
- Redacted audit log evidence was preserved.
- Evidence files were not modified until the simulated incident notes were recorded.

## Eradication

Authentication controls were reviewed and confirmed through tests: generic login failure messages, bcrypt password verification, rate limiting, temporary lockout, and security event logging.

## Recovery

- Test account restored after simulated lockout period.
- Valid authentication tested.
- Invalid authentication tested.
- Application health verified through automated tests.
- Authorization boundary tests remained passing.

## Lessons Learned

Rate limiting, temporary lockout, and security logging improve detection and response for repeated authentication failures. A production environment would also need monitoring, alerting, retention rules, access controls, and formal escalation procedures.

## Evidence References

- `docs/incident-record.md`
- `evidence/task-3/15-redacted-security-logs.md`
- `evidence/task-3/16-security-logging-tests.md`
- `tests/integration/security-logging.test.js`
- `tests/integration/security-auth.test.js`
