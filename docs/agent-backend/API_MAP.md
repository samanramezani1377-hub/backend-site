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
| API-007 | POST | `/wp-json/woogit/v1/billing/bazaar/verify` | `BillingController` + `BazaarService` | OPERATIONAL/BILLING App Session | enabled Bazaar plan + server-side CafeBazaar subscription verification | App (Bazaar build) |

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

## API-007 — billing/bazaar/verify

Bazaar subscription purchase verification endpoint. The app submits the CafeBazaar subscription SKU, purchase token and optional package name. The backend authenticates the App session, rate-limits by IP and Account/Site, validates the package name, resolves the SKU to an enabled WooGit subscription plan, verifies the subscription server-side through `BazaarService`, and activates or refreshes the WooGit entitlement. Purchase tokens are stored only as hashes in the Bazaar purchase audit table for replay/cross-account protection.

Request JSON:

`product_id` (string, required), `purchase_token` (string, required), `package_name` (string, optional).

Success response: `status`, `plan_key`, `expires_at`, `account_id`, `site_id`.

Representative errors: `invalid_request`, `missing_purchase_data`, `invalid_package_name`, `bazaar_plan_not_configured`, `bazaar_not_configured`, `bazaar_auth_failed`, `bazaar_unreachable`, `bazaar_verify_failed`, `bazaar_purchase_not_found`, `bazaar_purchase_not_active`, `bazaar_product_mismatch`, `bazaar_subscription_expired`, `purchase_already_claimed`.

Source: `plugin/woogit-backend/src/BillingController.php:161-191` and `plugin/woogit-backend/src/BazaarService.php:1-202`.

## Rate limiting / idempotency

Billing and proxy mutation endpoints are protected by the backend rate-limit/idempotency implementation. Exact limits and keys are source-controlled by `BillingController`, `BillingService`, `IdempotencyService` and the billing documentation.

## Bazaar billing integration

`BillingService.php:15,50,64-65,72-87,102,179-234,541` owns Bazaar SKU mapping, plan resolution and entitlement activation/renewal. `BazaarService.php:1-202` owns CafeBazaar Developer API authentication, access-token caching and server-side subscription verification. Server credentials are configuration-only and must never be committed.

## Endpoint change rule

Changing method/path, permission callback, required headers, session scope, request body, response body, status code, error code, side effect, rate limit or idempotency behavior requires updating this map and the corresponding JSON/security maps in the same code change.
