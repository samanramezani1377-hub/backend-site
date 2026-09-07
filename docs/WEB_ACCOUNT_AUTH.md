# WooGit Account Authentication

## Identity model

A WooGit Account is permanently associated with exactly one connected Site. The Account identity is backed by a WordPress User on the central WooGit Backend WordPress installation; when WooCommerce is available that user is created as a WooCommerce Customer. `wp_user_id` is the identity link. Site and Entitlement remain WooGit-owned domain entities.

Email is contact metadata, not the Account lookup key. Web login still starts with Site URL, which resolves Site -> Account -> linked WordPress User.

## Two independent authentication paths

### App/API path

The app connects using the supplied customer-site credentials: Site URL, WordPress username, WordPress Application Password, Consumer Key, and Consumer Secret. These authenticate against the customer's WordPress/WooCommerce site during `/sites/verify` and `/forward`. They are not the central WooGit WordPress User password and are never treated as the web login credential.

### WooGit web-site path

The separate WooGit Backend website authenticates with Site URL + the linked central WordPress User password. The resulting credential is a dedicated WooGit Web Session and is independent from `X-WooGit-Session`.

Web password storage therefore uses the central WordPress user password storage (`wp_set_password` / `wp_check_password`); WooGit no longer uses `web_password_hash` as the active authentication source. The legacy column remains only for migration compatibility and must not be used for new authentication.

## Account creation and identity provisioning

When `/sites/verify` creates a new WooGit Account and the request includes a valid contact email, the Backend also provisions a central WordPress/WooCommerce Customer identity and links its `wp_user_id` to the Account. The new identity receives a random high-entropy temporary WordPress password that is never returned to the App. It is marked as provisioned-but-not-configured until the customer chooses the actual web password.

If the email already belongs to a central WordPress user, WooGit does not silently take over that identity. The Account is created without linking that existing user; first-time web credential setup must also provide the current WordPress password and prove that the identity is a permitted non-privileged customer/subscriber identity.

If no email is supplied during `/sites/verify`, the Account and Site can still be created, but the central web identity remains unconfigured until the customer completes first-time web credential setup with an email.

If identity provisioning fails for a newly created Account, the Account creation is failed closed rather than leaving a partially created WooGit Account.

## Linking an existing WordPress Customer

First-time setup is performed from a valid App/API session. If a central identity was provisioned by WooGit, the customer chooses the web password and the Backend replaces the temporary password using WordPress password storage. No temporary password is exposed to the App.

If the supplied contact email already belongs to a central WordPress user, the setup request must provide the current WordPress password before the Account can be linked. Administrator, editor, author, and shop-manager identities are never auto-linked.

This prevents a customer-site credential holder from claiming an unrelated privileged WordPress identity merely by knowing its email address.

## Generic account requirements contract

`GET /wp-json/woogit/v1/account/requirements` returns a generic list of account requirements. The contract is deliberately not tied to a particular UI.

Each requirement has an `id`, a numeric `type`, and a `required` flag. The numeric type is an opaque wire value owned by the app.

Current stable types:

| Type | ID | Meaning in Backend |
|---:|---|---|
| `1` | `web_account_password` | Linked WordPress/WooCommerce identity exists but its web password is not configured |
| `2` | `contact_email` | Optional contact email metadata |

## First-time web credential setup

After `/sites/verify` returns a valid API session, the app can call the requirements endpoint. If type `1` is required, it calls `POST /wp-json/woogit/v1/account/setup-web-credentials` with password and confirmation. `email` may be supplied when the Account has no contact email. When an existing central WordPress user owns that email and is not already linked, `current_wordpress_password` is also required to prove control before linking.

## Web login

`POST /wp-json/woogit/v1/web/login` accepts Site URL + password. The server normalizes the URL, resolves the unique Site, resolves its single Account, resolves the linked WordPress User, and verifies the WordPress password. Successful login returns a separate Web Session token.

## Security boundary

- One Account has exactly one Site.
- One central WordPress User can back at most one WooGit Account.
- Site and Entitlement remain WooGit domain entities.
- Email is contact metadata and is never used alone to resolve Account identity.
- Customer-site API credentials are never copied into the central WordPress User password.
- App/API and Web authentication remain separate paths and separate sessions.
- Existing privileged WordPress users cannot be auto-linked by email.
- Newly provisioned identities use a temporary random password that is never exposed and must be replaced during setup.
- The legacy `web_password_hash` column is not an active authentication source.
