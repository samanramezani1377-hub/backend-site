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

The source of truth remains Theme PHP/JS/CSS and backend contracts. The map is an agent navigation index.
