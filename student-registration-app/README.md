# Student Registration Web Application (IFT542 Practical)

A hardened prototype student registration system built for the IFT542
security assessment assignment. All data is fictitious; do not enter real
personal information.

## Stack
- PHP 8+ (uses `str_starts_with`, `declare(strict_types=1)`)
- MySQL / MariaDB (via XAMPP)
- No external PHP dependencies (Composer not required for this baseline)

## Setup (XAMPP, Windows/macOS)

1. Copy this whole `student-registration-app` folder into your XAMPP
   `htdocs` directory, e.g. `C:\xampp\htdocs\student-registration-app`.
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (http://localhost/phpmyadmin) and import, in order:
   - `db/schema.sql`
   - `db/seed.sql`
4. Generate real password hashes for the seed accounts (the ones in
   `seed.sql` are placeholders):
   ```
   cd student-registration-app/db
   php generate_hash.php "Passw0rd!123"
   ```
   Copy the output hash and `UPDATE users SET password_hash = '...' WHERE email = '...';`
   for each seeded account (or re-edit seed.sql and re-import).
5. Copy `.env.example` to `.env` and fill in your local DB credentials and
   a random `APP_SECRET`:
   ```
   cp .env.example .env
   php -r "echo bin2hex(random_bytes(32));"   # paste result as APP_SECRET
   ```
6. Visit `http://localhost/student-registration-app/public/login.php`

## Test Accounts (fictitious data only)

| Role    | Email                       | Password       |
|---------|------------------------------|----------------|
| Student | amina.test@example.local    | Passw0rd!123   |
| Student | chinedu.test@example.local  | Passw0rd!123   |
| Admin   | admin.test@example.local    | Passw0rd!123   |

Change these hashes before any demonstration/recording; do not reuse this
password anywhere real.

## Project Structure
```
config/     -> DB connection (PDO, prepared statements) + .env loader
includes/   -> security.php (CSRF, sessions, headers, logging, lockout)
public/     -> web root - all pages served from here
  admin/    -> role-gated admin panel (overview, manage courses, view students)
  upload.php-> secure document upload (content-sniffed MIME, random filenames)
uploads/    -> where uploaded files land; .htaccess blocks script execution here
db/         -> schema.sql, seed.sql, hash generator
vulnerable-examples/ -> isolated "before" code for Task 2 report evidence
             (NOT linked into the live app - do not deploy)
logs/       -> local log output (gitignored)
evidence/   -> screenshots/test output for report submission
```

## STEP 8 additions (this build)
- **`docs/setup-and-evidence-guide.md`**: the click-by-click walkthrough
  from a blank XAMPP install through to a submission-ready ZIP - starting
  services, importing the DB, generating real password hashes, running the
  test suite, rendering the DFD, capturing all 9 required screenshots with
  suggested filenames, filling in the incident record, and finalising/
  renaming the report per the submission checklist. Follow it top to
  bottom with the checkboxes.

## STEP 7 additions (this build)
- **`report/IFT542_Technical_Report.docx`**: the actual submission document -
  a Word report following the three task headings exactly as the
  assignment specifies (Task 1 / Task 2 / Task 3), built from everything
  in Steps 1-6. Currently 7 pages before you insert screenshots; will land
  in the 8-12 page range once the DFD image, test-result screenshot, log
  export and runbook screenshot are dropped into the marked placeholder
  spots (search the doc for "INSERT").
- **Before submitting**, you still need to:
  1. Rename the file to `MATRICNO_IFT542.docx` (replace `/` with `-`),
     then export to PDF (the assignment requires a PDF).
  2. Fill in your name/matric number on the title page.
  3. Render the Mermaid diagram from `docs/task1-threat-model.md`
     (mermaid.live or VS Code's Mermaid preview) and paste the screenshot
     into section 1.1.
  4. Run `tests/run_tests.php`, screenshot or paste the output into 2.4.
  5. Screenshot the `/admin/index.php` audit log table (redact anything
     sensitive) into 3.5.
  6. Fill in and screenshot a completed incident record from
     `docs/incident-response-runbook.md` into 3.6.
  7. Sign and date the ethics statement at the end.

## STEP 6 additions (this build)
- **`docs/task1-threat-model.md`**: the full Task 1 deliverable -
  - A Mermaid data-flow diagram with 3 labelled trust boundaries, matching
    this app's actual processes/data stores (paste into mermaid.live or a
    Mermaid-aware editor and screenshot for your PDF report).
  - A STRIDE worksheet with 9 application-specific threats (exceeds the
    6 required, all 6 categories covered).
  - A risk register scoring each threat before and after the controls
    already built in Steps 1-5, with a rank column.
  - Written justification for the top 3 priority risks, including why
    residual risk is accepted rather than driven to zero.

## STEP 5 additions (this build)
- **`docs/incident-response-runbook.md`**: the one-page runbook required by
  Task 3, covering all six IR stages (Preparation, Identification,
  Containment, Eradication, Recovery, Lessons Learned) - written against
  this app's actual `audit_log` table and event types, not generic
  boilerplate. Includes a fillable incident record template at the bottom.
  Convert to PDF or paste into your report as-is.

## STEP 4 additions (this build)
- **`tests/TestHarness.php`** and **`tests/run_tests.php`**: a dependency-free
  PHP test suite (no Composer/PHPUnit needed) that drives the running app
  over HTTP with cURL, exactly like a browser - cookies, CSRF tokens and
  all. Registers one throwaway fictitious account per run, so it's safe to
  re-run and never touches your seeded demo data.
- Covers exactly the "Evidence to Submit" line items from Task 2 (valid
  login, invalid login rejected, SQLi doesn't change query meaning,
  passwords not plaintext) plus Task 3 access-control/CSRF checks.
- Run it, redirect the output into `evidence/test-results.txt`, and
  reference that file from your report.

## STEP 3 additions (this build)
- **SSRF-hardened URL preview** (`public/preview.php`): a "course resource
  link preview" feature - the classic SSRF-prone pattern. Defended with:
  - Scheme allowlist (http/https only)
  - Hostname allowlist (`ALLOWED_HOSTS` - edit this list for your own demo)
  - Self-resolved DNS + IP-range check rejecting loopback/private/link-local/
    metadata addresses (blocks `127.0.0.1`, `169.254.169.254`, `10.x`, etc.)
  - Redirects disabled (`CURLOPT_FOLLOWLOCATION = false`) so a 302 can't
    bypass the checks above
  - Timeout + response size cap
  - Fetched content is stripped/escaped before display, not rendered as raw HTML
- **`public/local-target/resource.php`**: a harmless local page the preview
  feature is allowed to fetch, so you can demo the *allow* path safely -
  try `http://localhost/student-registration-app/public/local-target/resource.php`
- **`vulnerable-examples/preview_vulnerable.php`**: isolated "before" SSRF
  example (naive `curl_init($url)` with redirects on, no allowlist) for
  your Task 3 report evidence. Not linked into the live app.

## STEP 2 additions (this build)
- **Admin panel** (`public/admin/`): `index.php` (overview + recent audit log),
  `courses.php` (add/delete courses), `students.php` (read-only roster +
  enrolments). Every page starts with `require_role('admin')` - this is your
  Elevation-of-Privilege control for Task 1/3: a student session gets a 403,
  logged as `ACCESS_DENIED`.
- **Document upload** (`public/upload.php`):
  - Real content-type checked via `finfo` (magic bytes), not the filename
    extension or the browser-supplied `Content-Type`.
  - 2MB size cap enforced server-side.
  - Files stored under a random 32-hex-char name - the original filename is
    kept only as a DB label, never used as a filesystem path (blocks path
    traversal / overwrite attacks).
  - `uploads/.htaccess` disables the PHP engine and denies any `.php*`
    request in that folder as defense in depth, in case a bad file ever
    got past validation.

## Security controls implemented (maps to assignment tasks)
- **Task 2 (SQLi/Auth):** PDO prepared statements everywhere (`ATTR_EMULATE_PREPARES`
  disabled), Argon2id password hashing, generic login errors, 5-attempt
  lockout for 15 minutes, session ID regeneration on login.
- **Task 3 (Defences):**
  - XSS: all dynamic output passed through `e()` (htmlspecialchars) + CSP header.
  - CSRF: `csrf_field()` / `require_csrf()` on every state-changing POST, SameSite=Lax cookies.
  - Security headers: CSP, X-Content-Type-Options, X-Frame-Options, Referrer-Policy,
    Permissions-Policy, `X-Powered-By` removed.
  - Debug mode gated by `APP_DEBUG` env var; errors never echoed to the browser.
  - Audit logging via `log_event()` -> `audit_log` table (event/who/when, no secrets).

## Running tests
Automated test script at `tests/run_tests.php` - registers a throwaway
fictitious account and exercises the app exactly like a browser would
(via cURL with cookie/session handling), then verifies the results.

```
cd student-registration-app
php tests/run_tests.php
```

Save output for your evidence folder:
```
php tests/run_tests.php > evidence/test-results.txt
```

It proves: valid login works, invalid login gets a generic error (no
account enumeration), a classic `' OR '1'='1` SQLi payload does not bypass
login, passwords are stored as Argon2id hashes not plaintext, course
registration without a CSRF token is rejected (403), a student session
cannot reach `/admin/`, and 5 failed logins trigger a temporary lockout.

Manual checklist (for anything not automated, e.g. SSRF/upload):
- [ ] Valid login succeeds and redirects by role
- [ ] Invalid password shows generic error, increments failed_attempts
- [ ] 5 failed attempts locks the account for 15 minutes
- [ ] Submitting `' OR '1'='1` as email does NOT bypass login
- [ ] Course registration without a valid CSRF token is rejected (403)
- [ ] Registering twice for the same course does not duplicate (unique key)
- [ ] A logged-in student visiting `/admin/index.php` gets 403 (ACCESS_DENIED logged)
- [ ] Uploading a renamed `.php` file disguised as `.jpg` is rejected (real MIME check)
- [ ] Uploading a file over 2MB is rejected
- [ ] Requesting an uploaded file's stored name directly does not execute it
- [ ] Preview of `http://localhost/student-registration-app/public/local-target/resource.php` succeeds
- [ ] Preview of `http://127.0.0.1/` (not on allowlist) is blocked
- [ ] Preview of `http://169.254.169.254/` is blocked
- [ ] Preview of `file:///etc/passwd` is blocked (scheme rejected)

## Note on the vulnerable examples folder
`vulnerable-examples/login_vulnerable.php` intentionally contains an
SQL-injectable query for report/demonstration purposes only. It is never
referenced by any live page and should only ever be run against a
throwaway local database with fictitious data.
