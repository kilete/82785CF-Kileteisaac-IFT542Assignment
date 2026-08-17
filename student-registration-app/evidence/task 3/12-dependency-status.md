# Phase 4D Evidence 12: Dependency Status

Dependency review used `package.json` and `pnpm-lock.yaml`.

| Dependency        | Manifest Specifier | Locked Version | Notes                                                          |
| ----------------- | ------------------ | -------------- | -------------------------------------------------------------- |
| `bcrypt`          | `^5.1.1`           | `5.1.1`        | Password hashing dependency.                                   |
| `dotenv`          | `^16.4.7`          | `16.6.1`       | Local environment loading.                                     |
| `ejs`             | `^3.1.10`          | `3.1.10`       | Server-side templates; escaped output required.                |
| `express`         | `^4.21.2`          | `4.22.2`       | Web framework.                                                 |
| `express-session` | `^1.18.1`          | `1.19.0`       | Session middleware; production session store remains required. |
| `helmet`          | `^8.0.0`           | `8.3.0`        | Security headers.                                              |
| `morgan`          | `^1.10.0`          | `1.10.1`       | Local request logging.                                         |
| `multer`          | `^1.4.5-lts.1`     | `1.4.5-lts.2`  | Lockfile marks 1.x deprecated and recommends 2.x.              |
| `mysql2`          | `^3.12.0`          | `3.23.2`       | MySQL driver with parameterized queries.                       |
| `zod`             | `^3.24.1`          | `3.25.76`      | Validation.                                                    |

No dependency upgrades were performed in Phase 4D because the brief requested an audit and cautioned against blind upgrades. Multer should be upgraded to 2.x in a separate tested change.

External vulnerability audit was not performed because network access is restricted in this workspace.
