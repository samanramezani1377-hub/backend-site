# Theme Agent — Exhaustive Index

## Authority
Executable Theme source is authoritative. This document is a locator/contract index, not a copy of source.

## Runtime surface
- `theme/woogit/` — WordPress theme templates, PHP controllers/helpers, assets, forms, browser-side calls and web-session boundary.
- `tests/theme/` and `tools/preview/` — validation/preview tooling, not production runtime.

## Contract discovery matrix
| ID | Search target | Primary source anchor | Map |
|---|---|---|---|
| TH-TEMPLATE | PHP templates, template parts, page handlers | `theme/woogit/` | `ARCHITECTURE.md` |
| TH-ROUTE | `wp_ajax_`, REST registration, form actions | `theme/woogit/` | `API_MAP.md` |
| TH-HTTP | `fetch`, `XMLHttpRequest`, `wp_remote_`, cURL | `theme/woogit/` | `API_MAP.md`, `CROSS_REPO_MAP.md` |
| TH-SESSION | cookies, redirects, web password/session handling | `theme/woogit/` | `SESSION_MAP.md` |
| TH-JSON | `json_encode`, JSON parsing, AJAX responses | `theme/woogit/` | `JSON_CONTRACTS.md` |
| TH-FORM | POST fields, nonce, validation, redirects | `theme/woogit/` | `JSON_CONTRACTS.md`, `ERROR_MAP.md` |
| TH-BILLING | plans/status/checkout web flows | `theme/woogit/` | `API_MAP.md`, `CROSS_REPO_MAP.md` |
| TH-ERROR | `WP_Error`, status codes, user-facing errors | `theme/woogit/` | `ERROR_MAP.md` |

## Exhaustive HTTP/JSON search keys
Search all source for: `add_action`, `wp_ajax_`, `register_rest_route`, `wp_remote_`, `fetch(`, `XMLHttpRequest`, `json_encode`, `json_decode`, `wp_send_json`, `WP_Error`, `$_POST`, `$_GET`, `$_COOKIE`, `$_SESSION`, `nonce`, `redirect`, `Location`, `status`, `code`, `message`, `data`, `session`, `billing`.

## Required line-addressable record
`ID | file:line-line | template/function | trigger | request fields | headers/cookies | response/HTML/JSON | error behavior | backend dependency`

## Completeness gate
Every new production file under `theme/woogit/` must be represented in `SOURCE_INDEX.md`. Every changed web/API/JSON/session/form/error contract must have a corresponding map update.

## Boundary rule
Theme must not consume the Android App `X-WooGit-Session` or call the App-only operational-session activation flow. Theme uses its own web boundary and the Backend contract documented here.
