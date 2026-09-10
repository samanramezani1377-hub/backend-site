# WooGit Backend Agent Map

This directory is the backend/plugin contract map. It is documentation/navigation only.

## Mandatory change direction

**CODE -> MAP is mandatory. MAP -> CODE is not mandatory.**

Any code change that changes an endpoint, request/response JSON, authentication, session scope, entitlement rule, error contract, database effect, webhook/cron payload or cross-surface behavior MUST update the affected map in the same change.

Changing a map to clarify or index existing behavior MUST NOT force a code change.

## Files

- `ARCHITECTURE.md` — backend layers and source symbols.
- `API_MAP.md` — every REST endpoint and its security/contract metadata.
- `JSON_CONTRACTS.md` — request/response/error JSON.
- `SESSION_MAP.md` — Account/Site/Entitlement/session lifecycle.
- `ERROR_MAP.md` — backend error codes/statuses.
- `DATA_FLOW.md` — end-to-end request/data flows.
- `CROSS_REPO_MAP.md` — App ↔ Backend ↔ Theme ownership.
- `api-index.json` — machine-readable endpoint index.
- `CHANGE_POLICY.md` — mandatory code-to-map rule.

## Agent lookup

Search this directory for an endpoint path, JSON key, error code, Session scope, class, function or database entity. Then inspect the referenced source code. The source implementation remains authoritative.
