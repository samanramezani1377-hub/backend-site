# Backend Cross-Repository Map

## Repositories/surfaces

- App: `samanramezani1377-hub/woogit`, `main`.
- Backend: this repository, plugin surface.
- Theme: this repository, `theme/woogit` surface; implementation work has also used `theme-v1-implementation`, but this map is committed on `main` as the requested central index.

## Shared endpoints

| Contract | App | Backend | Theme |
|---|---|---|---|
| Site verification | `BackendClient.verifySite` | `RestController` | web auth/verification is separate |
| Plans | `BillingClient.plans` | `BillingController` | pricing/portal |
| Billing status | `BillingClient.status` | `BillingController` | portal billing |
| Checkout | `BillingClient.checkout` | `BillingController`/`BillingService` | payment flow |
| Activate operational session | App billing client | `BillingController`/`SessionService` | forbidden for Theme |
| Commerce proxy | `BackendClient.forward` | `RestController`/`WooCommerceProxy` | Theme adapter only where explicitly contracted |

## Boundary rules

- App Operational Session is not a Web Session.
- App Billing Session is not a Web Session.
- Theme must not send `X-WooGit-Session` or call App session activation.
- Backend is the authority for Account/Site/Entitlement/session validation.

## Change rule

A shared contract change requires synchronized updates in every surface that produces or consumes it. A documentation-only correction does not require code modification.
