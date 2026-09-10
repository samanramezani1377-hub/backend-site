# Backend Session Map

## Account/Site foundation

`site identity -> Account + Site resolution -> entitlement -> session scope`

Email is not the identity key for the App/Web site model. Site identity and the backend Account/Site ownership contract determine context.

## SessionService

Source: `plugin/woogit-backend/src/SessionService.php`.

- `SCOPE_BILLING = billing`
- `SCOPE_OPERATIONAL = operational`
- Billing sessions have their own TTL.
- Operational sessions are issued with the remaining entitlement lifetime.
- `authenticate()` rejects missing, revoked or expired sessions.
- `issueBilling(accountId, siteId)` issues billing access.
- `issueOperational(accountId, siteId, entitlementExpiresAt)` issues operational access.
- `activateOperationalFromBilling(...)` consumes valid billing access and creates operational access under the backend's lock/transaction rules.

## Verify lifecycle

`/sites/verify -> Account/Site -> trial/entitlement check -> Billing Session always available -> Operational Session when entitlement allows -> response`

The dedicated Billing Session is intentionally separate from the Operational Session so expired commerce access does not create a circular dependency where payment requires the expired session.

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
