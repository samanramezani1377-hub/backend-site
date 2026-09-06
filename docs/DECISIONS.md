# WooGit Architecture Decisions

## ADR-001 — WordPress is the commercial/control plane

**Decision:** Use WordPress for the public WooGit website and internal management UI.

**Reason:** Fast content/admin development, mature user/role system, and a good fit for marketing/docs/control-plane workflows.

**Boundary:** WordPress is not required to process every realtime gateway request.

## ADR-002 — Gateway is separate from the WordPress control plane

**Decision:** A small stateless API/Gateway handles protected traffic and outbound customer-site calls.

**Reason:** Subscription enforcement, rate limiting, credential use and realtime traffic should not depend on the WordPress admin runtime.

## ADR-003 — PostgreSQL is the durable application store

**Decision:** Use PostgreSQL for accounts, sites, subscriptions, operations and audit data.

**Reason:** Relational integrity is valuable for ownership and entitlement boundaries.

## ADR-004 — Redis is acceleration/queue state

**Decision:** Redis is disposable infrastructure for cache, rate limiting, queues and realtime coordination.

**Reason:** Durable business state must remain recoverable from PostgreSQL.

## ADR-005 — Headless Bridge

**Decision:** WooGit Bridge has no required configuration UI in WordPress.

**Reason:** WooGit should provide a single UX and centralized subscription/capability control.

## ADR-006 — No direct app-to-customer-site traffic after commercial onboarding

**Decision:** Protected commercial traffic goes through WooGit Gateway.

**Reason:** This is the enforcement point for subscription, entitlements, audit, abuse prevention and credential isolation.

**Exception:** Direct access may exist only for explicitly non-commercial/local functionality and must not expose a bypass around entitlement enforcement.

## ADR-007 — Typed gateway operations over arbitrary proxying

**Decision:** Prefer typed operations to a generic URL proxy.

**Reason:** Arbitrary proxying increases SSRF, authorization and abuse risk.

## ADR-008 — Application Passwords for initial WordPress API authentication

**Decision:** Prefer WordPress Application Passwords for remote programmatic access.

**Reason:** WordPress documents them as revocable per-application credentials for API use.

## ADR-009 — AI stays behind WooGit

**Decision:** AI provider keys and model routing live in WooGit infrastructure.

**Reason:** Prevent provider secrets from reaching the customer site/browser and allow provider changes without Bridge updates.

## ADR-010 — Client is not an authority

**Decision:** Trial, subscription, entitlements, credits and site ownership are server-authoritative.

**Reason:** APKs can be modified; server-side enforcement is required for a commercial SaaS.

## ADR-011 — Idempotency is a platform primitive

**Decision:** Every CREATE-style mutation has an idempotency contract.

**Reason:** Network timeouts can occur after remote success. The system must prove safe retry behavior before commercial release.

## Non-goals

- Building a custom distributed platform before measured need.
- Storing customer passwords in the mobile app.
- Turning the Bridge into a general-purpose remote execution plugin.
- Allowing an LLM to issue arbitrary HTTP requests.
