# ADR-015 — Customer Credential Failure Semantics (V1)

**Status:** Accepted / Locked for V1

## Decision

When a request reaches the customer WordPress/WooCommerce site using credentials supplied in that same request, and the customer site's authentication fails (for example HTTP 401), WooGit Backend V1 will:

- Fail only the current request with a clear typed authentication/credential error.
- Leave the user's WooGit session unchanged.
- Leave Site Identity / site connection state unchanged.
- Not force reconnect or re-onboarding.
- Not mark the credentials or site connection as invalid based solely on that request failure.

## Future compatibility

V1 must keep the request/response and error boundaries structured so that a future version can add a limited, controlled retry policy (option C) without requiring credential persistence or changing the fundamental authentication model.

Any future retry must be explicitly bounded and must not blindly retry non-idempotent customer-site operations.
