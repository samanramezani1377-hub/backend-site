# WooGit Theme Agent Map

The Theme is a separate architectural surface under `theme/woogit` in the `backend-site` repository.

## Mandatory direction

**CODE -> MAP is mandatory. MAP -> CODE is not mandatory.**

Any Theme code change affecting pages, routes, forms, requests, JSON, web sessions, backend endpoints, rendering or security boundaries MUST update the affected Theme map in the same change.

A map-only correction does not require a code change.

## Files

- `ARCHITECTURE.md`
- `API_MAP.md`
- `JSON_CONTRACTS.md`
- `SESSION_MAP.md`
- `ERROR_MAP.md`
- `CROSS_REPO_MAP.md`
- `api-index.json`
- `CHANGE_POLICY.md`

## Static Theme verification contract

`tests/theme/test_theme_static.sh` is part of the Theme architectural verification surface. It validates the Theme/template boundary, credential-persistence restrictions, public-page rendering, App-only endpoint exclusion, registration/Web Password separation, payment-return entitlement presentation, and selected Theme UI/accessibility contracts.

The payment-return check specifically enforces that the Theme may **read** Backend billing/entitlement state for presentation but must not reconcile or activate entitlement itself. Payment authority remains in the Backend/WooCommerce flow, while App reconciliation occurs after the payment WebView closes.

Changes to this Theme verification contract are therefore considered Theme-surface changes and must be accompanied by an update under `docs/agent-theme/**` describing the affected architectural contract.

The source of truth remains Theme PHP/JS/CSS and backend contracts. The map is an agent navigation index.
