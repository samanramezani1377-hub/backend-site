# Backend — Agent Source Index

## Runtime root
The executable backend is the WordPress plugin under `plugin/woogit-backend/`.

## Mandatory lookup order
1. `plugin/woogit-backend/woogit-backend.php` — plugin bootstrap.
2. `plugin/woogit-backend/src/RestController.php` — REST routing/controller boundary and request dispatch.
3. `plugin/woogit-backend/src/BillingController.php` — billing REST boundary.
4. `plugin/woogit-backend/src/BillingService.php` — billing orchestration/persistence boundary.
5. `plugin/woogit-backend/src/SessionService.php` — Billing/Operational session lifecycle.
6. `plugin/woogit-backend/src/AccountService.php` / site/account services — identity and ownership.
7. `plugin/woogit-backend/src/EntitlementService.php` and related entitlement logic — plan access.
8. `plugin/woogit-backend/src/WooCommerceProxy.php` / `ProxyPolicy.php` — customer API forwarding/security boundary.
9. `plugin/woogit-backend/src/IdempotencyService.php` / `OperationService.php` — checkout/operation safety.
10. `plugin/woogit-backend/src/` remaining services/repositories/models — runtime implementation.

## Contract anchors
| Area | Source |
|---|---|
| Verify site | `src/RestController.php` (`/sites/verify` flow) |
| Forward customer API | `src/RestController.php` + `src/WooCommerceProxy.php` |
| Billing plans/status/checkout | `src/BillingController.php` + `src/BillingService.php` |
| Billing → Operational session exchange | `src/BillingController.php` + `src/SessionService.php` |
| Session scope/TTL | `src/SessionService.php` |
| WooCommerce outbound policy | `src/WooCommerceProxy.php` + `src/ProxyPolicy.php` |
| Checkout idempotency | `src/BillingController.php` + `src/BillingService.php` + `src/IdempotencyService.php` |
| Unknown operation handling | `src/OperationService.php` |
| Error envelopes/codes | `src/RestController.php` and error/exception classes under `src/` |

## Tests
Backend contract/security tests live under `tests/plugin/`. They validate behavior and are not runtime implementation.

## Line-addressable rule
For every source-level claim, agents must locate the exact file and line range on the current `main` tree. When a runtime source file changes, update the affected map and its line references in the same change.

## Completeness rule
Every newly added runtime source file must be added here in the same change. Generated/vendor/build output is excluded only when explicitly identified as such.

## Authority
`plugin source code -> agent map`. The map documents executable truth; changing a map alone never authorizes or requires code changes.
