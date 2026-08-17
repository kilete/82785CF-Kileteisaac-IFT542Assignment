# Evidence 06: SameSite Cookie

Reviewed file:

- `src/app.js`

Session cookie configuration:

- Cookie name: `ift542.sid`
- `httpOnly: true`
- `sameSite: 'lax'`
- `secure: true` only when `NODE_ENV === 'production'`
- Session secret loaded from environment variables

Security relevance:

- `HttpOnly` helps keep the session cookie unavailable to browser JavaScript.
- `SameSite=Lax` helps reduce cross-site request risk while preserving local HTTP usability for this assignment.
- `SameSite=None` is not used.
- Secure cookies are not forced for localhost HTTP development, but production mode enables them.

Verification:

- `tests/integration/csrf-defence.test.js` checks `SameSite=Lax` and `HttpOnly` on the session cookie.

No session cookie values are recorded in this evidence file.
