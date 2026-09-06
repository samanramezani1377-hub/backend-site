# WooGit Backend Site

> Architecture and implementation blueprint for the commercial WooGit SaaS platform.

This repository defines the backend/cloud side of WooGit: the commercial website, customer/account management, subscription and entitlement control, secure WordPress connectivity, API gateway, Bridge integration, chat, analytics, AI gateway, and operational architecture.

## Product vision

WooGit is a SaaS-controlled WordPress/WooCommerce management platform. The Android WooGit app does not need direct credentials to customer sites after onboarding. Instead, the intended production path is:

```text
WooGit App
    |
    | HTTPS + short-lived access token
    v
WooGit Gateway / API
    |
    +--> Authentication / Accounts
    +--> Subscription / Entitlements
    +--> Credential Vault
    +--> Chat / Realtime
    +--> Analytics / Events
    +--> AI Gateway / Credits
    |
    | authenticated outbound request
    v
WooGit Bridge Plugin
    |
    v
Customer WordPress / WooCommerce
```

The commercial website is intentionally separated from the request-processing plane. WordPress can serve as the public website and the internal control plane/admin UI, while a small API/Gateway service handles latency-sensitive and security-sensitive traffic.

## Core principles

1. **Server-side subscription enforcement** — the app is never the authority for trial or subscription validity.
2. **No customer-site secret in the APK** — WordPress credentials are stored server-side in an encrypted credential vault and are never returned to the mobile client after onboarding.
3. **WordPress Bridge stays headless** — no required wp-admin settings page for WooGit features; configuration is controlled from WooGit.
4. **Least privilege** — every site credential and capability is scoped to the smallest practical permission set.
5. **Privacy by default** — collect only the data required for the enabled feature; avoid storing raw secrets or unnecessary personal data.
6. **Idempotent mutations** — every CREATE/activation/provisioning mutation has an idempotency key and a durable operation record.
7. **Timeout-after-success safety** — retries must reconcile remote state before creating another resource or repeating an irreversible action.
8. **Provider abstraction** — AI providers are behind a WooGit AI Gateway so the Bridge and app do not depend on a single provider.
9. **Cloud-heavy / WordPress-light** — heavy processing, queues, analytics and AI execution stay on WooGit infrastructure.
10. **No false security from APK obfuscation** — a modified client must still be blocked by the server when entitlement is invalid.

## Documents

- [`docs/PRODUCT.md`](docs/PRODUCT.md) — product scope, commercial model, users and capabilities.
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — system architecture and request flows.
- [`docs/SECURITY.md`](docs/SECURITY.md) — credential, privacy, authentication and abuse model.
- [`docs/DATA-MODEL.md`](docs/DATA-MODEL.md) — core entities and relationships.
- [`docs/API-CONTRACT.md`](docs/API-CONTRACT.md) — API boundaries and canonical request patterns.
- [`docs/WORDPRESS-BRIDGE.md`](docs/WORDPRESS-BRIDGE.md) — Bridge plugin responsibilities and protocol.
- [`docs/AI-CHAT-ANALYTICS.md`](docs/AI-CHAT-ANALYTICS.md) — Chat, AI, tracking and analytics architecture.
- [`docs/BILLING.md`](docs/BILLING.md) — 15-day trial, time-based subscriptions, credits and entitlements.
- [`docs/OPERATIONS.md`](docs/OPERATIONS.md) — deployment, observability, backups, scaling and incident handling.
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — implementation phases and acceptance criteria.
- [`docs/DECISIONS.md`](docs/DECISIONS.md) — architecture decisions and non-goals.

## Planned stack

The first production-oriented implementation can use:

- **Public site / control plane:** WordPress + a dedicated WooGit management plugin/theme integration.
- **API/Gateway:** Laravel/PHP service, kept stateless where possible.
- **Database:** PostgreSQL.
- **Queue/cache/rate limiting:** Redis.
- **Realtime:** WebSocket/SSE service backed by Redis where required.
- **Object storage:** S3-compatible storage for non-sensitive artifacts and exports.
- **Secrets:** application-level encryption with a dedicated encryption key; production should prefer a managed KMS/secret manager.
- **Mobile:** existing WooGit Android application.
- **Customer integration:** headless WooGit Bridge WordPress plugin.

This is a blueprint, not a claim that every component must be deployed on day one. The MVP can start on a small VPS and split services only when load or isolation requirements justify it.

## Source-of-truth rules

- Account, subscription, entitlement and gateway authorization state: **WooGit backend**.
- Customer-site WordPress content/configuration: **customer WordPress**.
- Commercial/admin presentation: **WooGit WordPress control plane**.
- AI provider credentials and WooGit-managed AI credits: **WooGit backend**.
- Customer BYOK provider credentials: **encrypted WooGit vault**.
- The Android app is a client, not an authorization authority.

## Security boundary

A customer request should normally follow:

```text
App -> TLS -> WooGit API -> authenticate -> authorize entitlement
   -> resolve site credential -> forward -> Customer WordPress
   -> sanitize/normalize response -> App
```

When a subscription expires, the gateway rejects the request before it reaches the customer site. A cracked or modified APK therefore cannot simply switch a local `premium=true` flag and regain access.

## Important WordPress assumptions

WordPress Application Passwords are designed for programmatic access and are individually revocable. They are used with HTTPS and Basic Authentication for REST requests. The official WordPress REST API also exposes plugin and application-password resources, subject to the user's capabilities. See the official references linked in the project documentation.

## Current status

This repository is currently a **design and implementation blueprint**. It intentionally does not contain production credentials, customer data, or a pretend-complete backend implementation. Implementation should follow the phases in `docs/ROADMAP.md` and each security/availability acceptance criterion should be tested before the corresponding capability is exposed commercially.
