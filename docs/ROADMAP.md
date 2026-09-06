# WooGit Backend Roadmap

## Phase 0 — Foundation

Goal: make the architecture executable without exposing commercial traffic.

- repository documentation;
- environment/config contract;
- database schema/migrations;
- account/auth service;
- structured errors/request IDs;
- CI;
- local Docker development environment.

Acceptance:

- fresh environment boots from documented steps;
- tests run in CI;
- no secrets in repository.

## Phase 1 — Site connection

- site model;
- credential vault;
- WordPress connection validation;
- site health;
- secure disconnect;
- session/token management.

Acceptance:

- valid site connects;
- invalid credential fails cleanly;
- credential never appears in API response/logs;
- revoked WordPress credential is detected.

## Phase 2 — Gateway

- typed site operations;
- authorization pipeline;
- subscription check;
- site ownership check;
- rate limiting;
- outbound timeout/circuit breaker.

Acceptance:

- active account can reach entitled site operation;
- expired account cannot reach customer WordPress;
- another account cannot address the site by guessing an ID;
- modified app cannot bypass entitlement.

## Phase 3 — Idempotency and reliability

- idempotency table;
- operation status API;
- retry policy;
- reconciliation strategy;
- timeout-after-success integration tests.

Acceptance:

- every CREATE mutation is idempotent;
- simulated lost response after remote success creates one resource only;
- retry returns the original operation result.

## Phase 4 — Bridge

- headless plugin;
- discovery;
- scoped Bridge authentication;
- capability negotiation;
- health/version endpoint;
- event ingestion.

Acceptance:

- plugin activates without manual configuration;
- WooGit can configure supported features;
- unsupported protocol versions fail safely.

## Phase 5 — Commercial billing

- 15-day trial;
- plans;
- subscriptions;
- payment provider integration;
- webhook verification;
- renewals/extensions;
- expiration enforcement.

Acceptance:

- trial is server-controlled;
- paid period is server-controlled;
- payment webhooks are idempotent;
- expired accounts are blocked before outbound site calls.

## Phase 6 — Chat

- conversations;
- messages;
- widget session;
- operator inbox;
- realtime transport;
- human handoff.

Acceptance:

- widget works without privileged credentials in browser;
- chat traffic is subscription-gated;
- abusive clients are rate-limited.

## Phase 7 — Analytics

- typed event schema;
- ingestion;
- queue;
- aggregation;
- dashboards;
- retention/deletion controls.

Acceptance:

- event ingestion is bounded and validated;
- duplicate event IDs do not double-count;
- retention policy is enforced.

## Phase 8 — AI Gateway

- provider abstraction;
- provider credentials;
- WooGit credits;
- BYOK;
- tool execution;
- cost/usage ledger;
- chat integration.

Acceptance:

- provider keys never reach customer WordPress/browser;
- AI cannot call arbitrary URLs;
- usage is server-measured;
- credits cannot be forged by the client.

## Phase 9 — Scale and hardening

- managed database/Redis;
- horizontal API scaling;
- dedicated workers;
- advanced monitoring;
- disaster recovery drills;
- penetration/security testing;
- privacy/legal review.

## V1 commercial gate

Do not call the platform commercially ready until these are proven:

1. authentication and site ownership isolation;
2. encrypted credential storage;
3. server-side subscription enforcement;
4. idempotency for all CREATE mutations;
5. timeout-after-success proof;
6. Bridge authentication and versioning;
7. audit logging for privileged actions;
8. backup/restore test;
9. production monitoring;
10. no critical secrets in client or repository.
