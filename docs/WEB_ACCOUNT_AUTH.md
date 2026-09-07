# WooGit Account Authentication

## Unified identity model

A WooGit Account is the single user account for a verified customer Site. Its identity is based on the normalized Site URL and the unique WooGit Site record, not on email and not on which client path created it.

The same account is used by both the WooGit App/API path and the WooGit Backend website path. When the account has a web identity, that identity is the linked WordPress User/WooCommerce Customer on the central WooGit Backend installation. These are not separate customer accounts; the WordPress user is the central WordPress representation of the same WooGit user.

Email is contact metadata only. It is never sufficient to create, resolve, or claim an Account.

## Verification is mandatory before Account creation

No WooGit Account or WooGit Site may be created from an unverified Site URL.

The customer must prove control of the Site using the customer-site credentials accepted by `/sites/verify`:

- Site URL
- WordPress username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

`/sites/verify` verifies the real customer site through the Backend proxy before creating or reusing the unique Site/Account relationship.

A successful verification does exactly one of these:

1. Reuses the existing Account/Site for that verified Site URL; or
2. Creates the Account and its Site together for that verified Site URL.

Account creation itself never provisions a separate identity and never creates a second customer record.

## App/API path

The App starts with customer-site credentials and calls `/sites/verify`. If the Site URL is new, successful verification creates the single Account/Site identity. If it already exists, the existing Account is returned.

The resulting App/API session is independent from the web session. Customer-site credentials remain customer-site credentials and are not converted into the central web password.

## Web path

The web signup path uses the same Site verification lifecycle. It must not create an Account from Site URL + password alone.

The web client first performs the same Site verification with the customer-site credentials. After verification succeeds, it receives the Account/API context and can complete first-time web credential setup with the chosen password.

Therefore a user can start from either client path:

```text
App/API → verify Site → Account/Site → web credentials (optional)
Web     → verify Site → Account/Site → web credentials
```

Both paths resolve to the exact same Account when they refer to the same verified Site URL. A second Account for the same Site is forbidden by the one-to-one Site/Account constraint.

## Central WordPress/WooCommerce identity

The central WordPress User/WooCommerce Customer is the web-facing representation of the same WooGit user. It is created or linked only as part of explicit first-time web credential setup after Site verification has already succeeded.

Account creation and `/sites/verify` do **not** provision a central WordPress/WooCommerce customer merely because an email was supplied.

During `POST /account/setup-web-credentials`:

- If the Account has no linked central user and the email is unused, WooGit creates a WooCommerce Customer when WooCommerce is available (or a subscriber WordPress user as fallback) using the chosen password.
- If the email already belongs to a central WordPress user, WooGit never silently claims it. The customer must provide that user's current WordPress password, and privileged roles are rejected.
- A newly created identity is linked to the already verified Account. If linking fails, the newly created WordPress user is deleted so the operation does not leave an orphan identity.
- The chosen web password is stored by WordPress password APIs (`wp_set_password` / `wp_check_password`). The legacy `web_password_hash` column is not an authentication source.

An Account with no linked central user simply has web credentials not configured yet; this does not mean a hidden or temporary customer identity exists.

## Web login

`POST /wp-json/woogit/v1/web/login` accepts Site URL + password only after the Site has previously been verified and the Account has a linked central WordPress identity.

The server resolves:

```text
Site URL → unique Site → same Account → linked WP User → WordPress password
```

Successful login returns a separate WooGit Web Session. It does not reuse or convert `X-WooGit-Session`.

## Generic account requirements contract

`GET /wp-json/woogit/v1/account/requirements` returns a generic list of account requirements. The contract is deliberately not tied to a particular UI.

Each requirement has an `id`, a numeric `type`, and a `required` flag. The numeric type is an opaque wire value owned by the app.

Current stable types:

| Type | ID | Meaning in Backend |
|---:|---|---|
| `1` | `web_account_password` | The verified Account has no linked central WordPress identity yet |
| `2` | `contact_email` | Optional contact email metadata |

## First-time web credential setup

After Site verification has returned a valid Account/API context, the client can call the requirements endpoint. If type `1` is required, it calls `POST /wp-json/woogit/v1/account/setup-web-credentials` with password and confirmation. `email` may be supplied when the Account has no contact email.

When the supplied email already belongs to a central WordPress user and that user is not already linked, `current_wordpress_password` is required to prove control before linking.

## Password changes and contact email

Once a central identity exists, web password changes use the authenticated Web Session and update the same WordPress user's password with `wp_set_password`. Contact email changes update both WooGit contact metadata and the linked WordPress user's email when an identity exists.

## Security boundary

- One verified Site URL maps to exactly one WooGit Site and one WooGit Account.
- An Account cannot be created before successful Site verification.
- App/API and Web are two access paths to the same Account, not two account systems.
- The central WordPress User/WooCommerce Customer represents the same Account's web identity; it is not a separate WooGit account.
- Email is contact metadata and is never used alone to resolve Account identity.
- Customer-site API credentials are never copied into the central WordPress User password.
- App/API and Web authentication remain separate paths and separate sessions.
- Existing privileged WordPress users cannot be auto-linked by email.
- A newly created central identity is created only during explicit web credential setup and is rolled back if Account linking fails.
- The legacy `web_password_hash` column is not an active authentication source.
