# ADR-019 — Session Expiration, Plan Renewal, and Expired-Plan UX (V1)

**Status:** Accepted / Locked for V1

## Decision

WooGit Backend V1 treats session expiration as a supporting enforcement boundary derived from the customer's commercial plan. The session is not the primary authority for billing or entitlement.

### 1. Session expiration follows plan expiration

For an active session, the effective session expiration is aligned with the current plan/entitlement expiration. A session must never remain valid after the plan period that authorizes it has ended.

### 2. Plan renewal extends active sessions

If the customer's plan is renewed before the session becomes invalid, the session expiration must be extended to the renewed plan expiration, subject to normal session policy. Renewal must not require an unnecessary forced logout/reconnect merely because the original plan period ended.

The implementation should therefore avoid treating the original session `expires_at` as an immutable lifetime. It must be possible to reconcile/extend it from the authoritative entitlement state.

### 3. Plan expiration

When the authoritative plan/entitlement has fully expired and the corresponding session reaches its expiration boundary:

- Protected operational requests are denied.
- The response clearly indicates that the customer's plan/entitlement has expired.
- The client can use this state to present a renewal/payment action to the customer.
- No new operational session may be created while the plan does not authorize it.

This is a controlled transition to a payment/renewal state, not a replacement for the billing system.

### 4. Plan changes and session count

When a plan changes, the effective concurrent-session limit is read from the new entitlement. New sessions may only be created while the active count is below that limit.

A downgrade does not require silent revocation of existing sessions solely because their count is above the new limit; however, those sessions remain bounded by the new plan's expiration and cannot outlive the entitlement.

### 5. Source of truth

The entitlement/billing layer remains the canonical authority for:

- whether the plan is active, expired, canceled, or otherwise restricted;
- the plan expiration timestamp;
- the allowed concurrent-session count.

Session records are a supporting enforcement mechanism. Any session-vs-entitlement inconsistency must resolve in favor of the authoritative entitlement state.

### 6. Renewal-safe architecture

The session model must retain enough information to reconcile its expiration with the current entitlement without forcing a logout. The design must support future entitlement events/webhooks or reconciliation jobs that update active session boundaries after renewal or plan changes.
