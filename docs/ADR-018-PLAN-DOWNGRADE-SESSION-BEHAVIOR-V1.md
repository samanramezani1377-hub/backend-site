# ADR-018 — Plan Changes and Existing Sessions (V1)

**Status:** Accepted / Locked for V1

## Decision

A session is a supporting control mechanism for the customer's commercial plan. It is **not** the primary source of truth for entitlement or plan validity.

The session lifetime is aligned with the customer's current plan/entitlement period:

- A session's effective expiration must not extend beyond the customer's current plan expiration.
- When the customer's plan is downgraded, the new plan's concurrent-session limit applies immediately to creation of new sessions.
- Existing sessions are not silently auto-revoked merely because the concurrent-session limit decreased.
- Existing sessions must nevertheless follow the new plan's expiration boundary; they cannot remain valid beyond the active plan period.
- The entitlement system remains the authoritative source for plan status, validity, and the allowed concurrent-session count.
- Session state is therefore a secondary enforcement/cache-like mechanism and must never be treated as the canonical billing or entitlement record.

This allows an active client to remain usable during a plan change without silently disconnecting sessions solely because the numeric session limit changed, while still ensuring sessions cannot outlive the plan that authorizes them.
