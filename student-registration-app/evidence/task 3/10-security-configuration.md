# Phase 4D Evidence 10: Security Configuration

Scope: localhost-only security misconfiguration review for the IFT 542 Student Registration Web Application.

Reviewed configuration areas:

- `.env.example`
- `.gitignore`
- `src/config/env.js`
- `src/config/database.js`
- `src/app.js`
- `src/server.js`
- `src/middleware/errorHandler.js`
- `src/middleware/upload.js`
- `src/services/urlPreviewService.js`
- `database/seed.js`
- `package.json`
- `pnpm-lock.yaml`

Findings and actions:

- `.env` is excluded from Git and was not printed or copied.
- `.env.example` contains placeholders only.
- Database settings are environment-driven through `src/config/env.js`.
- Production validation now rejects empty database passwords.
- Session secrets are required and production rejects known lab placeholders.
- `NODE_ENV` is constrained to `development`, `test`, or `production`.
- The URL preview feature remains exact-origin allowlist based and disabled by default when no allowed origins are configured.
- Uploads use memory staging, a 2 MB limit, sanitized stored filenames, and local storage under `uploads/`.
- Seed accounts are fictitious and documented as local-only.

Result: PASS for Phase 4D local-lab requirements, with production session store, upload type allowlisting, and Multer 2.x upgrade retained as residual items.
