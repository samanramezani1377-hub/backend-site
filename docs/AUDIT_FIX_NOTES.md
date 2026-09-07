# Backend audit fix notes

## Scope

This note records fixes that are internal to the backend and do not intentionally change the mobile-app/backend API contract.

## Fixed

- Normalize announcement rate limiting to the RateLimitService `check()` contract instead of calling a non-existent `allow()` method.
- Make operation/idempotency persistence distinguish SQL failure from a zero-row update. State transitions must not be reported as persisted when the expected row was not changed.
- Keep billing checkout persistence failure-closed: a created WooCommerce order must not be reported as a successful completed checkout if operation/idempotency persistence fails.

## Intentionally unchanged

The following are API/app-path contracts and are not changed by this internal cleanup:

- Session scope and expiry semantics.
- Account/Site ownership authorization.
- `/forward` entitlement enforcement.
- Customer credential request-scoped handling.
- Idempotency-key requirement and unknown-state semantics exposed to the app.
- Billing anti-abuse limits and HTTP 429 contract.
- App version gate behavior.

## Follow-up integration coverage

Static/in-memory tests should be complemented with runtime integration tests for WordPress REST execution, DB state transitions, billing checkout, concurrency, timeout-after-success, DNS/SSRF behavior, and session/account/site authorization.