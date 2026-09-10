# Backend Data Flow Map

## Site verification

`App credentials -> /sites/verify -> WP/WC verification -> Account/Site lookup/create -> trial grant -> entitlement check -> Billing Session -> optional Operational Session -> JSON response`

## Operational request

`App Operational Session -> /forward -> session authentication -> Account/Site -> entitlement/policy -> WooCommerceProxy -> WooCommerce -> safe response -> App`

## Billing purchase

`App Billing Session -> /billing/status or /billing/checkout -> Account/Site context -> BillingService -> payment gateway -> checkout/payment response -> App`

## Payment activation

`payment confirmed -> /billing/activate-session -> Billing Session authentication -> entitlement/payment validation -> transactional operational-session creation -> response -> App replaces session state`

## Theme portal

`Browser -> Theme page/controller -> Web session/account context -> Backend web contract -> server-rendered/JSON response -> Theme UI`

Theme does not borrow App session state.

## Change propagation

Any changed node or edge in these flows must update this file plus the relevant API/JSON/session/cross-repo map.
