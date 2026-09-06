# V1 behavioral lifecycle coverage

The executable contract tests cover:

- valid operational authorization
- billing scope cannot authorize gateway access
- session expiry and revocation
- account/site ownership enforcement
- entitlement enforcement before outbound access
- privilege elevation through a distinct operational session
- idempotency same-key replay
- idempotency fingerprint conflict
- pending/concurrent retry behavior
- timeout-after-send becoming `unknown`
- unknown mutation is never blindly forwarded again
- successful and failed terminal states
- billing checkout state-machine behavior

The tests deliberately fail closed. They model the required V1 invariants without making network calls or creating real WooCommerce orders.
