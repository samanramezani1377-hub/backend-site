# ADR-018 — Plan Downgrade and Existing Sessions (V1)

**Status:** Accepted / Locked for V1

## Decision

When a customer's active commercial plan is downgraded and the new plan permits fewer concurrent sessions than are currently active:

- Existing sessions remain valid until their normal expiration or explicit revocation.
- The newly reduced session limit applies immediately to creation of new sessions.
- The backend does not automatically revoke existing sessions solely because of a plan downgrade.
- The entitlement service remains the server-side authority for the effective session limit.

This policy keeps active clients from being unexpectedly disconnected while ensuring the new plan limit is enforced for subsequent logins.
