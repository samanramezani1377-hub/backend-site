# Backend Session Map

## Account/Site foundation

`site identity -> Account + Site resolution -> entitlement -> session scope`

Email is not the identity key for the App/Web site model. Site identity and the backend Account/Site ownership contract determine context.

## SessionService

Source: `plugin/woogit-backend/src/SessionService.php`.

- `SCOPE_BILLING = billing`
- `SCOPE_OPERATIONAL = operational`
- Billing sessions have their own TTL and are not counted against the operational concurrent-session cap.
- Operational sessions are issued with the remaining entitlement lifetime.
- `authenticate()` rejects missing, revoked or expired sessions.
- `issueBilling(accountId, siteId)` issues billing access.
- `issueOperational(accountId, siteId, entitlementExpiresAt)` issues operational access only when the current entitlement's configured session limit has capacity.
- `activateOperationalFromBilling(...)` applies the same entitlement-based operational session limit while holding the account/site activation lock.
- Session limits are not hard-coded globally. `EntitlementService::getSessionLimit()` resolves the effective plan's `_woogit_max_sessions` setting from the purchased WooGit plan product.

## Verify lifecycle

`/sites/verify -> Account/Site -> trial/entitlement check -> Billing Session always available -> Operational Session when entitlement allows -> response`

The dedicated Billing Session is intentionally separate from the Operational Session so expired commerce access does not create a circular dependency where payment requires the expired session.

## Session accumulation prevention

`App persisted session -> no verify on normal process restart -> existing operational session continues`

A new operational session is created only when the App has no usable persisted session and performs real backend verification. The backend then evaluates the current entitlement session limit under a per-account/site/scope lock before inserting a new operational row.

If a plan has a configured positive `_woogit_max_sessions` value and the limit is already reached, the backend refuses the additional operational session rather than silently revoking another device's session.

## Plan configuration

Subscription products expose `WooGit Max Sessions` in the WooCommerce product editor. A positive value is the maximum concurrent operational sessions for that plan. Leaving it empty means that plan currently has no configured cap; this is deliberate and avoids inventing a global limit.

## Operational lifecycle

`Operational Session -> WooCommerce operations -> entitlement expiry/session expiry -> operational access stops`

Do not weaken this boundary merely to make billing work.

## Billing lifecycle

`Billing Session -> status/checkout -> payment -> activate-session -> new Operational Session`

## Web session

Web authentication/session handling is separate from App Operational Session. Endpoint controllers resolve account context according to their explicit authentication contract.

## Revocation

Logout/disconnect/session lifecycle operations must revoke/clear the correct session scope without deleting Account/Site ownership.

## Change rule

Changing TTLs, scope names, revocation rules, session issuance, authentication checks or exchange behavior requires updating this map and all affected API/security/cross-repo maps in the same code change.
