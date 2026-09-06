# ADR-021 — Expired Session and Plan State (V1)

- **Status:** Accepted
- **Scope:** WooGit Backend V1
- **Date:** 2026-09-06

## Context

A session is a server-managed, explicitly expiring authorization mechanism. Plan/entitlement state remains the server-side authority. When the session or plan expires, the app must not be forced into a full logout experience: the user should remain identifiable and able to access account and billing surfaces, while protected operational actions remain unavailable.

The client must also be able to distinguish ordinary session expiration from a plan/billing state that requires renewal or purchase. The backend must not redirect API clients to a payment page.

## Decision

WooGit V1 uses a **typed-state rejection model (D)** for expired sessions and plan/billing states, combined with the following account-continuity rule:

1. **The user can remain connected to their account.**
   - Expiration of an operational session does not mean the account is deleted or that the user must lose access to account-level/billing UI.
   - The app may keep a limited authenticated account context suitable for account, entitlement, and billing operations.

2. **Protected store operations are blocked.**
   - Once the operational session is expired, requests that require an active session are rejected.
   - The backend must never treat an expired session as sufficient authorization merely because the account still exists.

3. **Responses use typed machine-readable states.**
   - At minimum V1 supports distinct states such as:
     - `SESSION_EXPIRED` — the operational session has expired.
     - `PLAN_EXPIRED` — the effective commercial plan has ended.
     - `PLAN_RENEWAL_REQUIRED` — the account must renew/extend its plan before protected operations can resume.
     - `PAYMENT_REQUIRED` — a billing action is required before the requested protected operation can proceed.
   - Exact HTTP status mapping and response schema are defined by the API contract, not by UI text.

4. **The app handles renewal/purchase in-app.**
   - The client uses the typed state to keep the user in the account experience and present the appropriate renewal/purchase flow.
   - The user must be able to open the plan/billing area from the app and purchase a new/extended plan without first performing a forced full reconnect to the store.

5. **Backend does not perform HTTP redirects to payment.**
   - API responses remain API responses. The backend returns typed state/error information; the client decides how to present the billing flow.

6. **A newly purchased/extended plan does not resurrect an already expired operational session implicitly.**
   - Plan/entitlement changes can make the account eligible for a new operational session, but an expired session remains expired unless an explicitly defined re-authentication/session-creation flow creates a new one.
   - This prevents an expired authorization artifact from becoming active merely because billing changed.

## Invariants

- Account continuity and operational authorization are separate concerns.
- An expired operational session cannot execute protected customer-site operations.
- A valid account context does not imply a valid operational session.
- Entitlement/plan state is authoritative on the server.
- The client cannot override `SESSION_EXPIRED`, `PLAN_EXPIRED`, `PLAN_RENEWAL_REQUIRED`, or `PAYMENT_REQUIRED`.
- The backend never relies on client-side plan state to authorize operations.
- Payment/renewal is reachable from the app without requiring a forced store reconnect.
- No payment redirect is performed by the backend API.

## Consequences

### Positive

- Users are not unnecessarily logged out of their WooGit account when a store-operation session or plan expires.
- Billing and renewal can remain an in-app recovery path.
- The client receives deterministic states instead of parsing human-readable error messages.
- The design preserves a strong authorization boundary between account access and store operations.
- The model remains compatible with a future Access + Refresh session architecture.

### Negative / Trade-offs

- The client needs a clear distinction between account-level authentication and operational-session authorization.
- API contracts must define typed error/state payloads consistently across protected endpoints.
- Billing and account flows need to work even when store-operation authorization is unavailable.

## Future Migration

This ADR intentionally does not lock the implementation to the current simple session model. A future Access + Refresh design may replace the underlying session mechanics while preserving the externally important behavior: account continuity, explicit operational authorization, typed expiration/plan states, and in-app renewal.
