# Phase 4D Evidence 11: Security Headers

Header configuration is implemented in `src/app.js` through Helmet and one custom middleware.

Verified headers:

- `Content-Security-Policy`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: no-referrer`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`

CSP directives verified by automated test:

- `default-src 'self'`
- `script-src 'self'`
- `object-src 'none'`
- `base-uri 'self'`
- `frame-ancestors 'none'`

Additional configuration:

- `X-Powered-By` is disabled.
- HSTS is configured only for `NODE_ENV=production` so HTTP localhost testing continues to work.
- CSP does not include wildcard script sources or `unsafe-inline`.

Automated evidence:

- `tests/integration/security-misconfiguration.test.js`

Result: PASS.
