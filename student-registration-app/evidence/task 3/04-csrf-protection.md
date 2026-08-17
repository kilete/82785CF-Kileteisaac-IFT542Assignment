# Evidence 04: CSRF Protection

Reviewed and changed files:

- `src/middleware/csrf.js`
- `src/app.js`
- `src/routes/authRoutes.js`
- `src/routes/studentRoutes.js`
- `src/routes/adminRoutes.js`
- `src/views/layouts/header.ejs`
- `src/views/student/profile.ejs`
- `src/views/student/courses.ejs`
- `src/views/student/documents.ejs`
- `src/views/admin/courses.ejs`

Evidence summary:

- CSRF tokens are generated with Node.js `crypto.randomBytes()`.
- Tokens are stored in the Express session and exposed to EJS forms through `res.locals`.
- Server-side middleware verifies the submitted token belongs to the current session.
- Missing, invalid, and cross-session tokens are rejected with HTTP 403.
- CSRF validation failures are logged as minimal security events without recording the token.

Protected state-changing routes:

- `POST /logout`
- `POST /student/profile`
- `POST /student/courses/register`
- `POST /student/documents`
- `POST /admin/courses`
- `POST /admin/courses/:id`
- `POST /admin/courses/:id/delete`

No CSRF token values are recorded in this evidence file.
