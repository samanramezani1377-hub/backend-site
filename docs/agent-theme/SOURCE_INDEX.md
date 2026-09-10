# WooGit Theme — Agent Source Index

## Runtime root
The WordPress Theme implementation is `theme/woogit/`.

## Mandatory lookup order
1. `theme/woogit/style.css` — theme identity and styles.
2. `theme/woogit/functions.php` — theme bootstrap/hooks.
3. `theme/woogit/header.php`, `footer.php`, `front-page.php`, `index.php`, `404.php` — shell/page entry points.
4. `theme/woogit/inc/api/client.php` — Backend HTTP client boundary.
5. `theme/woogit/inc/auth/session.php` — Web session/cookie boundary.
6. `theme/woogit/inc/portal/data.php` — portal data access/normalization.
7. `theme/woogit/inc/commerce/adapter.php` — Theme commerce adapter boundary.
8. `theme/woogit/templates/public/` — public flows.
9. `theme/woogit/templates/auth/` — authentication/registration flows.
10. `theme/woogit/templates/portal/` — authenticated portal/account/billing flows.
11. `theme/woogit/template-parts/` — reusable rendering components.
12. `theme/woogit/assets/js/` and `assets/css/` — browser behavior/presentation.
13. `theme/woogit/inc/admin/` — theme administration/settings.

## Required Theme surfaces
The current CI structure contract explicitly covers home, pricing, payment result, login, register, overview, subscription, billing, payments, connected-site and account-security templates plus API/auth/portal/commerce/admin boundaries.

## Contract rule
Theme must consume the Backend web contract only through its own web/session boundary. It must not consume the Android `X-WooGit-Session` or call the App-only `billing/activate-session` flow.

## Line-addressable rule
For every source-level claim, agents must locate the exact file and line range on the current `main` tree. When Theme runtime code changes, update the affected Theme map and line references in the same change.

## Completeness rule
Every newly added runtime Theme file must be added here in the same change. Generated/vendor/build output is excluded only when explicitly identified as such.

## Authority
`theme source code -> agent map`. The map documents executable truth; changing the map alone never requires a code change.
