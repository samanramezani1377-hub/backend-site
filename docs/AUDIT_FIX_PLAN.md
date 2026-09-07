# Backend-only audit fixes

This file is a tracking note only. No mobile-app/backend API contract is intentionally changed by the audit work.

## API-path items intentionally left unchanged

- Session creation/activation semantics
- Account + Site ownership checks
- Entitlement enforcement for `/forward`
- Customer credential request-scoped handling
- `/forward` idempotency and `unknown` response semantics
- Billing anti-abuse limits and `429` contract
- App version gate contract

## Backend-only defects identified for correction

1. AnnouncementController must call RateLimitService::check(), not a non-existent allow() method.
2. OperationService mutation methods must not treat zero affected rows as a successful state transition where one row is expected.
3. IdempotencyService state transitions must enforce expected affected-row semantics.
4. Billing checkout must fail closed if authoritative operation/idempotency persistence cannot be confirmed after the WooCommerce order is created.

These changes are implementation correctness fixes and should not require an app update.
