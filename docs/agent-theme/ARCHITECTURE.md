# Theme Architecture Map

## Runtime/page layers

`WordPress request -> Theme template/controller -> Theme auth/session -> Theme API client/data -> Backend web/API contract -> rendered page`

## Theme surface

The current Theme root is `theme/woogit/`.

Important areas include:

- `functions.php`, `header.php`, `footer.php`, `index.php`, `front-page.php`
- `templates/public/` — public pages and payment result.
- `templates/auth/` — login/register.
- `templates/portal/` — overview, subscription, billing, payments, connected site and account security.
- `inc/api/` — web/backend client.
- `inc/auth/` — web session/authentication.
- `inc/portal/` — portal data.
- `inc/commerce/` — Theme commerce adapter.
- `inc/admin/theme-management/` — Theme admin configuration.
- `assets/js`, `assets/css` — browser behavior/presentation.

## Boundary

Theme templates/template-parts must not contain backend infrastructure calls. Theme executable code must not consume App `X-WooGit-Session` or call `/billing/activate-session`.

## Billing flow

`pricing/subscription -> web billing context -> plans/status/checkout web contract -> payment result -> web account/session state`

Exact web endpoints and payloads are mapped in `API_MAP.md` and must be verified against the current source before edits.
