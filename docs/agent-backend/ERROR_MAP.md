# Backend Error Map

## Error layers

1. HTTP status — transport/security outcome.
2. WP REST `code` — stable machine-readable error identifier.
3. `message` — human-readable server message.
4. `data` — endpoint-specific metadata.

Agents must preserve the machine-readable code when changing messages.

## Security/session failures

Search source for session authentication failures in `SessionService`, controller authentication helpers and proxy policy before modifying behavior.

Important categories:

- missing session
- invalid session
- revoked session
- expired session
- wrong session scope
- account/site context mismatch
- entitlement not allowed

## WooCommerce proxy failures

Proxy failures must preserve safe backend semantics. Customer consumer credentials must not be placed in query parameters. HTTPS and media-path restrictions are enforced by proxy policy/CI invariants.

## Billing failures

Billing errors must distinguish unavailable plan, invalid account/site context, invalid/expired Billing Session, checkout/idempotency failure, payment state and inability to activate Operational Session.

## Rule

Any new/changed error code, HTTP status, message contract or error data shape requires this map plus the API/JSON/cross-repo maps to be updated in the same code change.
