# Evidence 01: XSS Output Encoding

Reviewed files:

- `src/routes/studentRoutes.js`
- `src/controllers/studentController.js`
- `src/services/userService.js`
- `src/utils/validators.js`
- `src/views/student/profile.ejs`

Profile data flow:

1. Student submits first name, last name, and email to `POST /student/profile`.
2. `profileSchema` validates fields as bounded plain text/email values.
3. `userService.updateProfile()` stores values through parameterized SQL.
4. `GET /student/profile` retrieves the row through `userService.findById()`.
5. `student/profile.ejs` renders profile fields with escaped EJS output.

Primary demonstration field:

- Field: first name.
- Storage: `users.first_name`.
- Output context: HTML attribute value.
- Encoding: EJS `<%= user.first_name %>`.

Test evidence:

- `tests/integration/xss-defence.test.js` verifies encoded output and absence of raw markup from stored profile text.

No reusable XSS payloads were created.
