# Theme Session Map

## Web session boundary

Theme/web session is distinct from:

- App Operational Session.
- App Billing Session.

Theme must never read or reuse App `X-WooGit-Session`.

## Authentication flow

`login/register/site verification -> web authentication/context -> web session -> portal pages -> logout/session expiry`

Exact implementation is source-controlled by the Theme auth/API files and backend web controllers.

## Billing

Theme billing pages operate through web/account context. They must not require App Operational Session activation.

## Change rule

Changes to cookies, session IDs, lifetime, login/bootstrap, logout, authorization or account/site resolution require updating this map and the relevant API/JSON/security maps.
