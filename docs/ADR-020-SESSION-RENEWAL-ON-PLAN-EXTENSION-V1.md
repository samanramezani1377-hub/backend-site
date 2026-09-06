# ADR-020 — Session Renewal on Plan Extension (V1)

**Status:** Accepted / Locked for V1

## Decision

A WooGit Backend V1 session has its own explicit `expires_at`, but that timestamp is derived from the customer's effective plan/entitlement lifecycle.

When a customer's plan is successfully extended or renewed:

- Existing active sessions are extended to the new effective plan expiration.
- A session must never be extended beyond the currently effective plan expiration.
- Session renewal does not replace entitlement checks; Entitlement remains the authoritative source for access and plan validity.
- Session state is a supporting enforcement/cache layer for plan lifecycle, not the source of truth for billing or authorization.
- A plan change that reduces the allowed concurrent-session count affects creation of new sessions immediately according to the effective entitlement; it does not require silently revoking existing sessions solely because of the plan change.

## Rationale

This keeps active clients signed in across a legitimate plan renewal while preserving the server-side entitlement system as the final authority. It also keeps the session model simple in V1 and leaves room for future migration to a more advanced access/refresh-token lifecycle.

## Invariants

1. `session.expires_at <= effective_plan.expires_at`.
2. Extending a plan may extend eligible active sessions.
3. An expired or revoked entitlement cannot be bypassed by an unexpired session.
4. Session lifecycle must never become the primary billing/entitlement authority.
