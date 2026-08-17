# Evidence 02: Content Security Policy Verification

Reviewed file:

- `src/app.js`

Configured CSP directives:

- `default-src 'self'`
- `script-src 'self'`
- `style-src 'self'`
- `img-src 'self' data:`
- `object-src 'none'`
- `base-uri 'self'`
- `frame-ancestors 'none'`
- `form-action 'self'`

Verification:

- `tests/integration/xss-defence.test.js` checks that the CSP response header is present.
- The test verifies restrictive script, object, frame ancestor, base URI, and form action directives.
- The test confirms broad script source and unsafe inline script execution are not allowed.

Manual evidence to capture later:

- Browser developer tools screenshot of the `/login` response headers.
- Authenticated browser screenshot of `/student/profile` displaying ordinary profile text.
