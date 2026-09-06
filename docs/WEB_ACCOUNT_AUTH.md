# WooGit Account Authentication

## Identity model

A WooGit Account is permanently associated with exactly one connected site. The Site URL identifies the site; the Account is not resolved by email.

```text
Account
├── contact_email (optional, contact only)
├── web_password_hash
└── exactly one Site
    └── canonical_url / host
```

The database enforces the one-account-one-site relationship.

## Two independent authentication paths

### App path

The app connects to the customer's WordPress/WooCommerce site using the site credentials supplied during `/sites/verify`:

- Site URL
- WordPress username
- WordPress Application Password
- Consumer Key
- Consumer Secret

These credentials are site credentials, not WooGit web-account credentials. They are used to verify and forward requests and are not stored in the WooGit Account record.

### WooGit web-site path

The separate WooGit Backend website authenticates the customer with:

- Site URL
- WooGit web-account password

Email is **not** a login identifier. It is contact metadata only.

The web password is stored only as an Argon2id password hash. Plaintext passwords are never persisted.

## First-time web credential setup

After `/sites/verify` returns a valid API session, the app can call:

`GET /wp-json/woogit/v1/account/requirements`

If the Account has no web password, the response includes:

```json
{
  "id": "web_account_password",
  "type": "account_setup",
  "required": true
}
```

The app can then submit:

`POST /wp-json/woogit/v1/account/setup-web-credentials`

with a web password and confirmation. A contact email may be supplied in the same request, but it never changes Account identity.

The server enforces this state independently of the app UI. Once configured, the setup endpoint refuses to replace the password.

## Web login

`POST /wp-json/woogit/v1/web/login`

Request:

```json
{
  "site_url": "https://example.com",
  "password": "..."
}
```

The server normalizes the Site URL to the site origin, resolves the unique Site, resolves its Account, and verifies the Account's password hash.

Successful login returns a separate web session token. Web sessions are stored in `woogit_web_sessions` and only their SHA-256 token hashes are persisted.

The web session is intentionally separate from the app API session (`X-WooGit-Session`). It must not be used as a proxy credential.

## Web session and account management

All endpoints below except `/web/login` require `X-WooGit-Web-Session`:

- `POST /web/login` — create a web session.
- `GET /web/me` — return the authenticated Account/Site context.
- `POST /web/logout` — revoke the current web session.
- `POST /web/account/contact-email` — update optional contact email.
- `POST /web/account/password` — change the web password; current password is required.
- `GET /web/billing/history?page=1&per_page=20` — return the WooGit orders belonging to this Account/Site, with status, amount, currency, date, and plan key.

The web site can therefore manage the customer's account and display payment history without receiving or reusing the app's WordPress credentials.

Web login is rate limited by both client IP and normalized Site host. Authentication failures use a generic `invalid_web_credentials` response so the API does not disclose whether a Site or Account exists.

## Security boundaries

- Email is never an authentication identifier.
- A single Account cannot be attached to a second Site.
- WordPress site credentials are not copied into Account fields.
- WooGit web passwords are hashed with Argon2id.
- Web sessions and app API sessions are separate credential classes.
- Expired/revoked web sessions are rejected.
- Web session records are cleaned up by the scheduled backend cleanup task.
- Password changes require the existing web password and an authenticated web session.
- Password recovery is intentionally not implemented through email because email is contact metadata, not an authentication identifier. A recovery protocol must be designed explicitly before being added.
