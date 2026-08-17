# Incident Response Runbook
### Student Registration Web Application - IFT542 Practical Assignment

**Scope:** This runbook covers response to a suspected security incident
(account compromise, injection attempt, unauthorised access, or SSRF/CSRF
abuse) affecting the Student Registration Web Application in the
lab/localhost environment. Follow the six stages below in order.

---

## 1. Preparation
*What's already in place, before an incident happens.*

- **Logging is always on**: `includes/security.php` → `log_event()` writes
  every `LOGIN_FAIL`, `LOGIN_SUCCESS`, `LOGOUT`, `ACCESS_DENIED`,
  `CSRF_REJECTED`, `VALIDATION_REJECTED`, `SSRF_BLOCKED`, and
  `ADMIN_COURSE_*` event to the `audit_log` table (who/what/when, no
  secrets or plaintext passwords).
- **Admin visibility**: recent events are visible at `/admin/index.php`
  without needing raw DB access.
- **Backups**: keep a current export of `student_registration` DB
  (`mysqldump`) before any planned testing session, so you can restore a
  known-good state.
- **Contacts**: designate one team member as incident lead for the
  assignment demo; note their contact detail in your submission.
- **Roles/access**: only `role = 'admin'` accounts can reach `/admin/*`
  (enforced by `require_role('admin')`); keep the list of who holds admin
  test accounts short and known.

## 2. Identification
*How you notice something is wrong.*

- Query `audit_log` for anomalies, e.g.:
  ```sql
  SELECT * FROM audit_log
  WHERE event_type IN ('LOGIN_FAIL','ACCESS_DENIED','CSRF_REJECTED','SSRF_BLOCKED')
  ORDER BY created_at DESC LIMIT 50;
  ```
- Indicators to look for: a burst of `LOGIN_FAIL` for one account or from
  one IP (credential stuffing / brute force), repeated `ACCESS_DENIED`
  (privilege-escalation attempt), any `SSRF_BLOCKED` entry (someone probing
  internal addresses), or `VALIDATION_REJECTED` spikes (automated attack
  tooling hitting the form).
- Confirm scope: which account(s), which endpoint(s), what time window.

## 3. Containment
*Stop it from getting worse, without destroying evidence.*

- **Short-term**: the affected account is already auto-locked after 5
  failed attempts (`locked_until`, 15 minutes) - extend manually if needed:
  ```sql
  UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 24 HOUR)
  WHERE email = 'affected.user@example.local';
  ```
- Force logout of a compromised session by rotating `APP_SECRET` in `.env`
  or clearing session storage (invalidates all active sessions).
- If a malicious file reached `uploads/`, remove it and confirm
  `uploads/.htaccess` is still in place (blocks execution regardless).
- Do not delete `audit_log` rows - they are the evidence for step 4/6.

## 4. Eradication
*Remove the root cause.*

- Identify how the attacker got in (e.g. weak/reused password, a bug that
  bypassed validation) using the `audit_log` trail and, if relevant, web
  server access logs.
- Patch the specific flaw (e.g. add a missing `require_csrf()` call, tighten
  a validation regex, add a host to/from the SSRF allowlist correctly).
- Force a password reset for any account confirmed compromised.
- Re-run `tests/run_tests.php` to confirm the fix holds and nothing else broke.

## 5. Recovery
*Bring things back to normal, safely.*

- Restore from the pre-incident backup only if data integrity is in doubt;
  otherwise resume normal operation once eradication is verified.
- Re-enable the affected account (clear `locked_until`) only after
  confirming the root cause is fixed and the password has been reset.
- Monitor `audit_log` closely for 24-48 hours after recovery for recurrence.

## 6. Lessons Learned
*Close the loop.*

- Write a short incident record (see template below) within 48 hours while
  detail is fresh.
- Note whether an additional control would have caught this sooner (e.g.
  IP-based rate limiting in addition to per-account lockout).
- Update this runbook and the STRIDE risk register (Task 1) if a new
  threat class was discovered that wasn't previously modelled.

---

## Incident Record Template

| Field | Detail |
|---|---|
| Date/time detected | |
| Detected by | |
| Affected account(s)/endpoint(s) | |
| `audit_log` event types observed | |
| Containment action taken | |
| Root cause | |
| Fix applied (file/commit) | |
| Verified by (test run) | |
| Lessons learned / follow-up action | |
