# Backend Agent — Exhaustive Index

## Authority
Executable Backend source is authoritative. This is a locator/contract index, not a source-code copy.

## Runtime surfaces
- `plugin/` — WordPress plugin runtime, REST routes/controllers, authentication, Account/Site/Entitlement, sessions, billing and WooCommerce integration.
- `tests/plugin/` — Backend validation only.

## Contract discovery matrix
| ID | Search target | Primary source anchor | Map |
|---|---|---|---|
| BE-ROUTE | `register_rest_route`, route namespace/path | `plugin/` | `API_MAP.md` |
| BE-CONTROLLER | `RestController`, controller methods | `plugin/` | `ARCHITECTURE.md`, `API_MAP.md` |
| BE-AUTH | `auth`, `permission_callback`, context verification | `plugin/` | `SESSION_MAP.md`, `ERROR_MAP.md` |
| BE-ACCOUNT | `Account`, site ownership, identity | `plugin/` | `DATA_FLOW.md` |
| BE-ENTITLEMENT | `Entitlement`, plan/status/access | `plugin/` | `SESSION_MAP.md`, `DATA_FLOW.md` |
| BE-SESSION | `SessionService`, billing/operational scopes | `plugin/` | `SESSION_MAP.md` |
| BE-BILLING | `BillingGateway`, plans/status/checkout/activate | `plugin/` | `API_MAP.md`, `JSON_CONTRACTS.md` |
| BE-WC | WooCommerce/WordPress remote calls | `plugin/` | `ERROR_MAP.md`, `DATA_FLOW.md` |
| BE-JSON | `wp_send_json`, response/error arrays, encoding | `plugin/` | `JSON_CONTRACTS.md` |

## Exhaustive HTTP/JSON search keys
Search all source for: `register_rest_route`, `WP_REST_Request`, `WP_REST_Response`, `WP_Error`, `permission_callback`, `get_json_params`, `get_param`, `get_header`, `wp_send_json`, `rest_`, `code`, `message`, `data`, `status`, `session`, `billing_session`, `X-WooGit-Session`, `Authorization`, `Idempotency-Key`, `wp_remote_`, `wc_`.

## Required line-addressable record
`ID | file:line-line | class/function | route | request params/body | headers | response JSON | error JSON | auth/session scope | downstream consumer`

## Completeness gate
Every new executable file under `plugin/` must be represented in `SOURCE_INDEX.md`. Every changed REST, JSON, authentication, session, billing, entitlement, or external-WooCommerce contract must have a corresponding map update.

## Exclusions
Do not treat ZIPs, build artifacts, caches, logs, generated reports, or vendor/build output as runtime source. Tests are validation surfaces only.
