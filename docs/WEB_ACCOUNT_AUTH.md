# WooGit Account Authentication

## Identity model

A WooGit Account is permanently associated with exactly one connected site. The Site URL identifies the site; the Account is not resolved by email.

## Two independent authentication paths

### App path

The app connects using the supplied WordPress/WooCommerce site credentials: Site URL, WordPress username, WordPress Application Password, Consumer Key, and Consumer Secret. These are site credentials and are not WooGit web-account credentials.

### WooGit web-site path

The separate WooGit Backend website authenticates with Site URL + WooGit web-account password. Email is contact metadata only and is never a login identifier.

The web password is stored only as an Argon2id hash.

## Generic account requirements contract

`GET /wp-json/woogit/v1/account/requirements` returns a generic list of account requirements. The contract is deliberately not tied to password setup or to a particular UI.

Each requirement has an `id`, a numeric `type`, and a `required` flag. The numeric type is an opaque wire value owned by the app: Backend does not define what UI or interaction a type means.

Current stable types:

| Type | ID | Meaning in Backend |
|---:|---|---|
| `1` | `web_account_password` | WooGit web password is not configured |
| `2` | `contact_email` | Optional contact email metadata |

Example:

```json
{
  "requirements": [
    {
      "id": "web_account_password",
      "type": 1,
      "required": true,
      "configured": false
    },
    {
      "id": "contact_email",
      "type": 2,
      "required": false,
      "configured": false
    }
  ]
}
```

Future requirement types can be added without changing the endpoint shape. The app should treat unknown numeric types as unsupported data rather than assuming UI semantics from the number.

The server enforces each security-sensitive requirement independently; the requirement response is not itself an authorization mechanism.

## First-time web credential setup

After `/sites/verify` returns a valid API session, the app can call the requirements endpoint. If type `1` is required, it can submit `POST /wp-json/woogit/v1/account/setup-web-credentials` with the web password and confirmation. A contact email may be supplied, but it never changes Account identity.

## Web login

`POST /wp-json/woogit/v1/web/login` accepts Site URL + password. The server normalizes the URL, resolves the unique Site, resolves its single Account, and verifies the Account password hash. Successful login returns a separate web session token.

## Security boundary

- One Account has exactly one Site.
- Email is contact-only.
- WordPress site credentials are not copied into the Account.
- Web passwords use Argon2id hashes.
- Web sessions are separate from app API sessions.
- Unknown/future requirement types do not change backend authorization behavior.
