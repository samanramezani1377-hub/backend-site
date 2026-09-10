# Backend REST API Map

## Security legend

- `PUBLIC` — no Account/Site session required.
- `BILLING` — valid Billing Session required.
- `OPERATIONAL` — valid Operational Session required.
- `WEB` — valid Web Session/account context.

## Known endpoint contract matrix

| ID | Method | Path | Controller/source | Auth/session | Entitlement | Main consumers |
|---|---|---|---|---|---|---|
| API-001 | POST | `/wp-json/woogit/v1/sites/verify` | `RestController` | credential verification | checks commerce access | App |
| API-002 | VARIES | `/wp-json/woogit/v1/forward` | `RestController` / `WooCommerceProxy` | OPERATIONAL | required | App |
| API-003 | GET | `/wp-json/woogit/v1/billing/plans` | `BillingController` | PUBLIC | no | App, Theme |
| API-004 | GET | `/wp-json/woogit/v1/billing/status` | `BillingController` | BILLING/account context | billing state | App, Theme |
| API-005 | POST | `/wp-json/woogit/v1/billing/checkout` | `BillingController` | BILLING/account context | billing state | App, Theme |
| API-006 | POST | `/wp-json/woogit/v1/billing/activate-session` | `BillingController` | BILLING | entitlement/payment must allow activation | App |

## API-001 — sites/verify

`RestController` verifies WordPress/WooCommerce credentials, resolves Account/Site by site identity, grants trial where applicable, checks commerce entitlement and returns either operational access or billing access.

Current active-session contract:

- access enabled: primary `session` is Operational and a dedicated `billing_session` is also issued.
- access disabled: primary `session` is Billing; billing access remains available for purchase flow.

Source: `plugin/woogit-backend/src/RestController.php`, `finishVerifiedContext()`.

## API-002 — forward

Operational commerce proxy. The request must resolve a valid Operational Session before WooCommerce credentials/requests are forwarded.

Source: `plugin/woogit-backend/src/RestController.php`, `WooCommerceProxy.php`, `ProxyPolicy.php`.

## API-003 — billing/plans

Public plan discovery. This endpoint intentionally remains available when commerce entitlement is expired so the client can render purchase options.

Source: `plugin/woogit-backend/src/BillingController.php`.

## API-004 — billing/status

Requires valid account context using a valid session. The current architecture expects Billing Session access so status remains readable after operational entitlement expiry.

Source: `BillingController.php::status`, `authenticateAccountContext()`.

## API-005 — billing/checkout

Requires valid account/site context and idempotency. The checkout path must remain available after operational entitlement expiry through Billing Session.

Source: `BillingController.php::checkout`, `BillingService::createCheckout`.

## API-006 — billing/activate-session

Consumes Billing Session, validates its `billing` scope and entitlement/payment state, then creates an Operational Session.

Source: `BillingController.php::activateSession`, `SessionService::activateOperationalFromBilling()`.

## Rate limiting / idempotency

Billing and proxy mutation endpoints are protected by the backend rate-limit/idempotency implementation. Exact limits and keys are source-controlled by `BillingController`, `BillingService`, `IdempotencyService` and the billing documentation.

## Endpoint change rule

Changing method/path, permission callback, required headers, session scope, request body, response body, status code, error code, side effect, rate limit or idempotency behavior requires updating this map and the corresponding JSON/security maps in the same code change.
