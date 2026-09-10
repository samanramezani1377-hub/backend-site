# Backend JSON Contract Map

## JSON-001 — site verification

Produced by `RestController::finishVerifiedContext()`.

```json
{
  "account_id": 0,
  "site_id": 0,
  "session": "token",
  "scope": "operational|billing",
  "billing_session": "token|null",
  "access_enabled": true,
  "billing_required": false
}
```

Types above describe the current contract; exact serialization is source of truth.

## JSON-002 — billing status

Produced by `BillingController` / billing service and consumed by App/Theme.

The contract contains billing state including status, start/expiry timestamps, capabilities and trial state. Search source for the exact nested response before changing fields.

## JSON-003 — checkout

Produced by `BillingController::checkout()` and `BillingService::createCheckout()`. It contains checkout/payment state and the payment URL consumed by clients. Exact keys must be read from source before editing.

## JSON-004 — activate-session

Produced after successful Billing Session exchange. Contains the newly issued Operational Session and its scope.

```json
{
  "session": "token",
  "scope": "operational"
}
```

## JSON-005 — WordPress REST error envelope

Backend errors exposed through WP REST generally use a code/message/data structure. Exact `data` members are endpoint-specific.

```json
{
  "code": "string",
  "message": "string",
  "data": {}
}
```

## Contract field index

Searchable high-value keys:

`account_id`, `site_id`, `session`, `billing_session`, `scope`, `access_enabled`, `billing_required`, `status`, `starts_at`, `expires_at`, `capabilities`, `trial_used`, `code`, `message`, `data`, `Idempotency-Key`, `X-WooGit-Session`.

## Rule

Any type/nullability/name/nesting change requires this file plus the API/security/cross-repo maps to be updated in the same code change.
