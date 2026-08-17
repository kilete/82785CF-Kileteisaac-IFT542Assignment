# Setup & Evidence Capture Guide
### Walk through this top to bottom. Check each box as you go.

---

## Part A — Get the app running

- [ ] **1. Place the project.** Copy the whole `student-registration-app`
  folder into `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/`
  (Mac), so the path is `.../htdocs/student-registration-app/`.

- [ ] **2. Start services.** Open the XAMPP Control Panel, click **Start**
  next to **Apache** and **MySQL**. Both rows should turn green.

- [ ] **3. Create the database.** Go to `http://localhost/phpmyadmin`.
  Click **Import** → choose file → select `db/schema.sql` → **Go**.
  Repeat Import for `db/seed.sql`.

- [ ] **4. Generate real password hashes.** Open a terminal
  (Command Prompt/PowerShell/Terminal), navigate into the project:
  ```
  cd C:\xampp\htdocs\student-registration-app\db
  C:\xampp\php\php.exe generate_hash.php "Passw0rd!123"
  ```
  Copy the printed hash. In phpMyAdmin, run this SQL (Query tab) for each
  of the 3 seeded accounts, pasting the hash each time:
  ```sql
  UPDATE users SET password_hash = 'PASTE_HASH_HERE' WHERE email = 'amina.test@example.local';
  UPDATE users SET password_hash = 'PASTE_HASH_HERE' WHERE email = 'chinedu.test@example.local';
  UPDATE users SET password_hash = 'PASTE_HASH_HERE' WHERE email = 'admin.test@example.local';
  ```

- [ ] **5. Configure secrets.** In the project root:
  ```
  copy .env.example .env        (Windows)
  cp .env.example .env          (Mac/Linux)
  ```
  Open `.env` in a text editor, set `DB_PASS=` to your MySQL root password
  (blank by default on XAMPP), and set `APP_SECRET` to a random string:
  ```
  C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32));"
  ```

- [ ] **6. Visit the app.**
  `http://localhost/student-registration-app/public/login.php`
  Log in with `amina.test@example.local` / `Passw0rd!123` to confirm it works.

---

## Part B — Run the automated tests (Task 2 evidence)

- [ ] Open a terminal in the project root and run:
  ```
  C:\xampp\php\php.exe tests\run_tests.php
  ```
- [ ] You should see a list of `[PASS]` lines ending in a summary
  (`Total: 15  Passed: 15  Failed: 0` or similar).
- [ ] Save the output for your evidence folder and report:
  ```
  C:\xampp\php\php.exe tests\run_tests.php > evidence\test-results.txt
  ```
- [ ] Screenshot the terminal output too (screenshots are required
  alongside raw output per the submission checklist).
- [ ] **Paste this into report section 2.4** where the report says
  `[ INSERT test-results.txt OUTPUT / SCREENSHOT HERE ]`.

---

## Part C — Render the Data-Flow Diagram (Task 1 evidence)

- [ ] Open `docs/task1-threat-model.md`, copy everything between the
  \`\`\`mermaid and \`\`\` fences (the flowchart code).
- [ ] Go to **https://mermaid.live**, paste it into the editor panel.
- [ ] The diagram renders on the right. Click the **Export** / download
  icon to save it as a PNG.
- [ ] Insert that PNG into the report at section 1.1, replacing
  `[ INSERT RENDERED DATA-FLOW DIAGRAM SCREENSHOT HERE ]`.
- [ ] Save a copy into `evidence/` too, named `dfd.png`.

---

## Part D — Manually demonstrate each control (screenshots)

Log in as a **student** (`amina.test@example.local`) unless noted.

- [ ] **SQL injection blocked**: on the login page, enter email
  `' OR '1'='1` and any password. Screenshot the generic error message.
  Save as `evidence/sqli-blocked.png`.
- [ ] **CSRF rejected**: this is easiest to show via the automated test
  output from Part B (manually crafting a no-CSRF request needs a tool
  like Postman/curl — optional bonus: use curl to POST to `/courses.php`
  without a `csrf_token` field and screenshot the 403 response).
- [ ] **XSS-safe field**: on the course registration page, note that any
  status message (e.g. after registering) is displayed as plain text even
  though it passes through the same code path as everywhere else.
  Screenshot `courses.php` after a registration.
- [ ] **SSRF blocked vs allowed**: go to `/preview.php`.
  - Paste `http://localhost/student-registration-app/public/local-target/resource.php`
    → should succeed and show the reading-list text. Screenshot as
    `evidence/ssrf-allowed.png`.
  - Paste `http://127.0.0.1/` → should show "not on the approved resource
    list" or similar. Screenshot as `evidence/ssrf-blocked.png`.
  - Paste `http://169.254.169.254/` → should also be blocked. Screenshot
    as `evidence/ssrf-metadata-blocked.png`.
- [ ] **Upload validation**: try uploading a `.txt` file renamed to `.jpg`
  — it should be rejected because the real content-type check (finfo)
  catches it, not just the extension. Screenshot the rejection.
- [ ] **Access control**: while still logged in as a student, try visiting
  `http://localhost/student-registration-app/public/admin/index.php`
  directly. Screenshot the 403 "You do not have permission" page.
- [ ] **Lockout**: enter the wrong password for the same account 5 times
  in a row, then try the correct password on the 6th attempt. Screenshot
  the "temporarily locked" message.
- [ ] **Admin panel + audit log**: log out, log back in as
  `admin.test@example.local`. Go to `/admin/index.php`. Screenshot the
  "Recent Security Events" table — this is your redacted log evidence for
  report section 3.5. Check it shows no passwords or full request bodies
  before saving.
- [ ] **Password hashes in the database**: in phpMyAdmin, browse the
  `users` table. Screenshot the `password_hash` column showing
  `$argon2id$...` values (crop out anything you don't want visible, this
  is fine to include since it's fictitious test data only).

Save every screenshot from this section into the `evidence/` folder with
a clear, matching filename (the submission checklist requires "clearly
named screenshots").

---

## Part E — Complete the incident-response record (Task 3 evidence)

- [ ] Pick any one of the events you triggered in Part D (e.g. the
  lockout, or an SSRF-blocked attempt).
- [ ] Open `docs/incident-response-runbook.md`, scroll to the **Incident
  Record Template** table at the bottom.
- [ ] Fill it in based on that real event (query `audit_log` in
  phpMyAdmin to get exact timestamps/detail for the "detected" and
  "audit_log event types observed" rows).
- [ ] Screenshot the completed table, insert into report section 3.6
  where it says `[ INSERT COMPLETED INCIDENT RECORD / RUNBOOK SCREENSHOT HERE ]`.

---

## Part F — Finalise the report and package for submission

- [ ] Open `report/IFT542_Technical_Report.docx` in Word.
- [ ] Fill in your **name and matric number** on the title page.
- [ ] Insert all screenshots from Parts B-E at their marked spots.
- [ ] Check page count is 8-12 pages (excluding appendices) — it should
  land in range once screenshots are in; trim commentary if you're over,
  or add a short "Limitations / Future Work" paragraph if you're under.
- [ ] **File → Save As → PDF.**
- [ ] Rename both the `.docx` and `.pdf` to the required format:
  `MATRICNO_IFT542.pdf` (replace `/` in your matric number with `-`,
  e.g. `2020-1-00001CS_IFT542.pdf`).
- [ ] Rename the whole project folder the same way before zipping it, or
  when naming your Git repo.
- [ ] Double-check `.env` is NOT included in your submission zip (it's in
  `.gitignore` already, but double-check if you're zipping manually
  instead of using Git).
- [ ] Confirm no real personal data anywhere — only the fictitious test
  accounts from `db/seed.sql`.

---

## Final submission checklist (from the assignment)

- [ ] Technical report PDF, 8-12 pages, named `MATRICNO_IFT542.pdf`
- [ ] Source code (Git repo link or ZIP) with DB migration/seed files
- [ ] Evidence folder with named screenshots, test outputs, logs
- [ ] README with setup instructions, test accounts, dependencies
- [ ] All secrets/keys/passwords removed or placeholder-only
- [ ] Screenshots/logs readable, redacted, referenced from the report
