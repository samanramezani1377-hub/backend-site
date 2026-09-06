# ADR-017 — Concurrent Sessions and Plan Limits (V1)

**Status:** Accepted / Locked for V1

## Decision

WooGit Backend V1 allows multiple concurrent sessions for the same account.

The maximum number of simultaneously active sessions is not a hard-coded global constant. It is determined by the customer's currently effective commercial plan/entitlement.

- Entitlement is the server-side authority for the session limit.
- The client cannot increase or override the limit.
- Plan changes must be reflected when session-limit policy is evaluated.
- The session model must support different limits for different commercial plans.
