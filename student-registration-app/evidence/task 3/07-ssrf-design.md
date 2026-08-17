# Evidence 07: SSRF Design

Reviewed and changed files:

- `src/routes/toolsRoutes.js`
- `src/controllers/toolsController.js`
- `src/services/urlPreviewService.js`
- `src/views/tools/url-preview.ejs`
- `src/config/env.js`
- `.env.example`

Original behavior:

- `/tools/url-preview` was an authenticated placeholder and did not make outbound requests.

Implemented design:

- Authenticated `GET /tools/url-preview` renders the form.
- Authenticated and CSRF-protected `POST /tools/url-preview` performs a controlled preview.
- Only `http:` URLs are accepted.
- URL credentials are rejected.
- Destination origin must exactly match `URL_PREVIEW_ALLOWED_ORIGINS`.
- DNS is resolved and resolved addresses are checked before fetching.
- Restricted address ranges are rejected.
- Redirects are disabled.
- Timeout and maximum response-size limits are enforced.
- User-supplied headers, cookies, and credentials are not forwarded.

No public services, private networks, FUT Minna systems, or real metadata endpoints were tested.
