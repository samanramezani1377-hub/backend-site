# ADR-024 — Direct ZarinPal Checkout for WooGit V1

## Status
Accepted / V1 implementation

## Decision

WooGit plan selection on the public Pricing page must not route through the WooCommerce Cart, Shop, or Single Product page.

The purchase flow is:

```text
Pricing / Plans
    ↓
if unauthenticated → Web Login → resume selected plan/variation
    ↓
POST /wp-json/woogit/v1/billing/checkout
    ↓
WooCommerce Order on woogit.ir
    ↓
configured WooCommerce ZarinPal gateway
    ↓
real ZarinPal payment URL
    ↓
ZarinPal / bank
    ↓
WooCommerce gateway callback + server-side verification
    ↓
WooCommerce Order paid
    ↓
Milo subscription lifecycle
    ↓
WooGit Entitlement
```

## Gateway ownership

WooGit does not implement a parallel ZarinPal merchant client. The installed/enabled WooCommerce ZarinPal gateway remains responsible for its merchant configuration, request, callback and verification logic.

The WooGit Backend bridge invokes the configured gateway's payment initiation path and captures the gateway's external ZarinPal redirect before it reaches the browser. The returned `payment_url` therefore points to ZarinPal rather than WooCommerce `order-pay`.

## Authentication UX

The Pricing CTA never creates a cart. If the Billing endpoint returns `401`, the selected plan/variation and idempotency key are stored as a short-lived browser checkout intent in `sessionStorage`. The user is sent to the WooGit Web Login page with a checkout continuation marker. After successful login, the same checkout request is resumed automatically and the user is sent directly to the returned ZarinPal URL.

The checkout intent is not trusted business state. Backend validation is repeated after login; the browser-stored intent cannot grant entitlement, alter price, or prove payment.

## Web Session

The Web Session remains an HttpOnly cookie. A backend REST bridge copies that same-origin cookie into the internal `X-WooGit-Web-Session` request header before WooGit REST callbacks run. The raw Web Session is not exposed to JavaScript/localStorage.

## Payment authority

A redirect, callback query parameter, `Authority`, or browser navigation is never treated as payment proof by WooGit App or Theme. Payment authority remains WooCommerce + the configured gateway verification, followed by Milo/Entitlement synchronization.

## Explicitly excluded UX

- WooCommerce Cart
- WooCommerce Shop/Product archive as an intermediate checkout step
- WooCommerce Single Product page
- WooCommerce `order-pay` as the user-facing payment page
- Direct ZarinPal credentials or SDK logic in the Android app
