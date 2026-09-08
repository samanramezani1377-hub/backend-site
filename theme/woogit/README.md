# WooGit Theme V1

Official WooGit website + Customer Portal theme.

## Boundaries

- Backend Public REST API is authoritative for Account, Web Session, Site Ownership, Entitlement and Authorization.
- WooCommerce on `woogit.ir` is authoritative for WooGit commercial order/payment data through approved adapters.
- Customer WooCommerce is never accessed by the Theme.
- Customer WordPress/WooCommerce credentials are request-scoped during bootstrap and are never persisted by the Theme.
- App session (`X-WooGit-Session`) and Web session (`X-WooGit-Web-Session`) are separate.
- `/billing/activate-session` is App-only and is not used here.

## Install

Copy `theme/woogit/` into `wp-content/themes/woogit/`, activate it, and create the documented pages using the slugs in `docs/theme/PAGES.md`.

## Management

Presentation/content controls are under **Appearance → WooGit Theme**. Billing, payment, entitlement and authorization truth are not configurable there.

## Development

Theme implementation follows the frozen V1 documents under `docs/`. UI tuning is allowed only where it does not change frozen architecture, security, page inventory, navigation or API semantics.
