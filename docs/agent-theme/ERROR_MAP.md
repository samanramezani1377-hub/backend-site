# Theme Error Map

## Error sources

- server-rendered WordPress/backend errors
- web/API response codes
- validation errors
- authentication/session expiry
- billing/payment failures
- payment-result redirects

Theme should preserve stable machine-readable backend codes when available and render user-facing Persian text separately.

## Rule

Any changed error code, status, form validation, redirect or displayed error contract must update this map and the relevant JSON/API map in the same code change.
