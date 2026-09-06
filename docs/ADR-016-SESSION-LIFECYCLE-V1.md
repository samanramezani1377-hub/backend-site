# ADR-016 — Session Lifecycle (V1)

**Status:** Accepted / Locked for V1

## Decision

WooGit Backend V1 uses a simple server-managed session with a defined expiration time.

- No refresh-token mechanism is required in V1.
- Session expiration is explicit and enforced server-side.
- The session model and API boundaries must remain migration-friendly for a future Access Token + Refresh Token model.
- Introducing refresh tokens later must not require changing the underlying Site Identity or account ownership model.
