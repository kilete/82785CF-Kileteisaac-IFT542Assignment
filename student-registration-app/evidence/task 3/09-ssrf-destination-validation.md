# Evidence 09: SSRF Destination Validation

Reviewed file:

- `src/services/urlPreviewService.js`

Destination validation steps:

1. Parse submitted value with the standard `URL` parser.
2. Reject malformed URLs.
3. Require `http:` protocol.
4. Reject URLs containing credentials.
5. Require exact origin match against configured allowlist.
6. Resolve hostname through DNS.
7. Reject unsafe resolved addresses.
8. Fetch with server-controlled `GET`, no redirects, timeout, and response-size limit.

Rejected destination categories:

- IPv4 loopback.
- IPv6 loopback.
- Private IPv4 ranges.
- Link-local IPv4.
- Link-local IPv6.
- Multicast/reserved ranges.
- Unspecified addresses.
- IPv4-mapped private/loopback IPv6 addresses.
- Metadata-address values.
- Non-allowlisted hostnames.

DNS rebinding note:

- The service validates DNS resolution before fetch. The exact-origin allowlist is the primary boundary. Production deployments should use pinned-address connection handling or an equivalent strategy if broader destinations are ever allowed.

No real restricted endpoints were contacted.
