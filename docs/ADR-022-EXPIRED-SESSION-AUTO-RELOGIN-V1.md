# ADR-022 — Expired Session and Automatic Re-Login (V1)

## Status
Accepted

## Context
A user's Session is intentionally not resurrected after expiration. However, requiring the user to manually enter credentials again would create unnecessary friction because the app can already provide the authentication/connection information required to establish a new Session.

## Decision
When a Session expires:

- The expired Session remains expired and is never resurrected.
- Requests that require an active Session are rejected with typed authentication/entitlement status such as `SESSION_EXPIRED`, `PLAN_EXPIRED`, or `PAYMENT_REQUIRED`, as applicable.
- Account-level access remains available so the user can inspect their account and purchase/renew a plan from inside the app.
- After a successful plan purchase/renewal, the app automatically performs the normal login/connect flow using the information it already has available; the user is not required to manually enter credentials again.
- The Backend creates a **new Session** after re-validating the account, Site Identity/ownership, entitlement, and required connection/authentication information.
- The new Session follows the current session-expiration and concurrent-session rules.
- An expired Session must never be treated as valid authorization during this process.

## Client / Android Required Changes
The Android app must support a seamless automatic re-login flow:

1. Detect typed expired-session/plan-required responses.
2. Keep the account/session context needed to show the account and plan UI even while operational requests are blocked.
3. Allow plan purchase/renewal from inside the app without requiring a manual login first.
4. After successful purchase/renewal, automatically invoke the existing login/connect contract and send all required authentication/connection data again.
5. Persist/use the newly issued Session only after the Backend successfully creates it.
6. Retry the originally blocked operation only after a new valid Session has been established, and only where retry is safe under the operation's idempotency contract.
7. Never attempt to reuse an expired Session as authorization.

## Security Invariants
- Session expiration is authoritative; the client cannot extend or revive a Session locally.
- Automatic re-login is a new authentication/session-creation flow, not Session resurrection.
- Backend authorization remains server-side and must be re-evaluated for the new Session.
- Customer credentials must not be exposed in responses, logs, telemetry, analytics, traces, or audit records.

## Consequences
This preserves the strict Session lifecycle while giving the user a seamless experience: the account remains accessible, plan purchase can happen in-app, and the app can reconnect automatically without asking the user to type credentials again.
