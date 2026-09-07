# WooGit Account Authentication

## Unified identity model

A WooGit Account is the single user account for a verified customer Site. Its identity is based on the normalized Site URL and the unique WooGit Site record, not on email and not on which client path created it.

The same account is used by both the WooGit App/API path and the WooGit Backend website path. The Account is linked to one central WordPress User on the WooGit Backend installation; that same WordPress User is the WooCommerce Customer identity when WooCommerce is available. These are not separate customer accounts. The WooGit Account, Web account, central WordPress User, and WooCommerce Customer represent the same underlying customer identity across their respective layers.

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

When a new Account is created, its single central WordPress identity is created and linked as part of Account creation. If WooCommerce is available, that WordPress User is also the WooCommerce Customer identity. This is the same customer identity used by the Web path; it is not a second account created later.

## App/API path

The App starts with customer-site credentials and calls `/sites/verify`. If the Site URL is new, successful verification creates the single Account/Site identity and its linked central WordPress identity. If it already exists, the existing Account and its existing central identity are returned.

The resulting App/API session is independent from the Web session. Customer-site credentials remain customer-site credentials and are not converted into the central Web password.

## Web path

The Web path uses the same Site verification lifecycle and the same Account/Site identity. It must not create a second Account from Site URL + password.

A user who starts from the Web client first performs Site verification with the customer-site credentials. After verification succeeds, the existing Account is used and the user can configure the Web password if it has not been configured yet.

Therefore a user can start from either client path:

```text
App/API → verify Site → Account/Site + central WP identity → Web credentials (optional)
Web     → verify Site → same Account/Site + same central WP identity → Web credentials
```

Both paths resolve to the exact same Account when they refer to the same verified Site URL. A second Account for the same Site is forbidden by the one-to-one Site/Account constraint.

## Central WordPress/WooCommerce identity

The central WordPress User is the single WordPress representation of the same WooGit Account. When WooCommerce is available, that same WordPress User is the WooCommerce Customer identity. It is not a separate Web account and it is not a separate WooCommerce-only customer account.

For a newly verified Site, Account creation provisions this central identity and stores its `wp_user_id` on the Account. This happens as part of the Account lifecycle, not merely because an email was supplied. Email remains contact metadata and is not used to claim an unrelated existing WordPress user.

The Web password is a credential for the already-linked central identity. First-time Web credential setup therefore configures the password; it does not create a second identity.

During `POST /account/setup-web-credentials`:

- The Account must already exist and have passed Site verification.
- If the Account has no Web password configured, the chosen password is applied to its already-linked central WordPress User.
- If the central identity is missing unexpectedly, the Backend must not silently claim an unrelated WordPress user by email. The identity must be resolved according to the established Account linkage rules.
- The chosen Web password is stored and verified through WordPress password APIs (`wp_set_password` / `wp_check_password`). The legacy `web_password_hash` column is not an authentication source.

An Account with no configured Web password still has the same central identity. It simply cannot authenticate through the Web password flow until the password is configured.

## Web login

`POST /wp-json/woogit/v1/web/login` accepts Site URL + password for a Site that has previously been verified and whose Account has a linked central WordPress identity with a configured Web password.

The server resolves:

```text
Site URL → unique Site → same Account → same linked WP User → WordPress password
```

Successful login returns a separate WooGit Web Session. It does not reuse or convert `X-WooGit-Session`.

## Generic account requirements contract

`GET /wp-json/woogit/v1/account/requirements` returns a generic list of account requirements. The contract is deliberately not tied to a particular UI.

Each requirement has an `id`, a numeric `type`, and a `required` flag. The numeric type is an opaque wire value owned by the app.

Current stable types:

| Type | ID | Meaning in Backend |
|---:|---|---|
| `1` | `web_account_password` | The verified Account has a linked central identity but no configured Web password yet |
| `2` | `contact_email` | Optional contact email metadata |

## First-time Web credential setup

After Site verification has returned a valid Account/API context, the client can call the requirements endpoint. If type `1` is required, it calls `POST /wp-json/woogit/v1/account/setup-web-credentials` with password and confirmation. `email` may be supplied when the Account has no contact email.

This operation configures the Web credential on the central identity already linked to the Account. It does not create a second Account, Web-only identity, WordPress User, or WooCommerce Customer.

## Password changes and contact email

Once the central identity exists, Web password changes use the authenticated Web Session and update the same WordPress user's password with `wp_set_password`. Contact email changes update WooGit contact metadata and, when a central identity exists, the linked WordPress user's email according to the Account's identity rules.

## Security boundary

- One verified Site URL maps to exactly one WooGit Site and one WooGit Account.
- An Account cannot be created before successful Site verification.
- App/API and Web are two access paths to the same Account, not two account systems.
- The Account's linked central WordPress User is the same customer identity used for Web authentication and, when WooCommerce is available, the WooCommerce Customer identity.
- Creating the Account provisions its single central WordPress identity; Web credential setup configures that identity's Web password rather than creating another identity.
- Email is contact metadata and is never used alone to resolve Account identity or silently claim an unrelated WordPress user.
- Customer-site API credentials are never copied into the central WordPress User password.
- App/API and Web authentication remain separate paths and separate sessions.
- Existing privileged WordPress users cannot be auto-linked by email.
- The legacy `web_password_hash` column is not an active authentication source.
