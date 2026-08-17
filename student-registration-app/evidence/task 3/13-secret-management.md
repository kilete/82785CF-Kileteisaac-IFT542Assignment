# Phase 4D Evidence 13: Secret Management

Secret handling controls:

- Runtime secrets are loaded from environment variables through `src/config/env.js`.
- `.env` is ignored by Git.
- `.env.example` is retained as a placeholder template only.
- The application does not hardcode an active session secret in `src/app.js`.
- Database credentials are read from environment variables in `src/config/database.js`.
- Tests verify active source/config files do not contain hardcoded secret defaults.
- Tests verify missing `SESSION_SECRET` fails environment validation.
- Production validation rejects known lab placeholder session secrets.
- Production validation rejects empty database passwords.

Files intentionally not printed:

- `.env`

Safe placeholder file:

- `.env.example`

Result: PASS for Phase 4D secret-management hardening.
