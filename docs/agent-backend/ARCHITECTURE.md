# Backend Architecture Map

## Runtime chain

`WordPress REST route -> Controller -> authentication/context -> Account/Site -> Entitlement -> SessionService -> Billing/Commerce service -> persistence/external WooCommerce`

## Core source ownership

- `plugin/woogit-backend/src/RestController.php` — App-facing site verification and general REST orchestration.
- `BillingController.php` — billing REST endpoints.
- `BillingService.php` — billing/domain/payment operations.
- `SessionService.php` — session issuance, authentication, TTL and exchange.
- `WooCommerceProxy.php` — controlled WooCommerce forwarding.
- `ProxyPolicy.php` — proxy transport/security restrictions.
- `IdempotencyService.php` — mutation idempotency state.
- `OperationService.php` — operation lifecycle state.
- Entitlement/account/site services — ownership and access decisions.

## Authorization order

`authenticate context -> resolve Account/Site -> check requested scope -> check entitlement where required -> perform operation -> emit contract response`

Never use a client-provided account/site ID as sufficient authorization.

## Billing boundary

Billing access is deliberately distinct from commerce Operational Session access. Payment must remain possible after operational entitlement expires.

## Commerce boundary

`forward` is Operational-only. Customer WooCommerce credentials and remote requests remain inside the backend proxy boundary.

## Web boundary

Theme/web authentication has its own session and page contract. Theme executable code must not consume App `X-WooGit-Session` or activate App sessions directly.

## Persistence / side effects

For every mutation, inspect the controller + service + repository/data layer to identify idempotency, transaction, lock, state transition and external side effects. This map intentionally points agents to source rather than copying all implementation details.
