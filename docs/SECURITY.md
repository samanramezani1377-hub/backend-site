# WooGit Security Model

## 1. Security objectives

The commercial architecture must make these statements true:

- A modified APK cannot bypass subscription authorization.
- Customer WordPress credentials are not stored in the APK after onboarding.
- Raw site credentials are never written to normal application logs.
- A customer cannot use one site's credential to access another site's data.
- Expired subscriptions cannot continue through an already-issued long-lived token.
- Retry after a timeout cannot duplicate a CREATE mutation.
- The Bridge exposes only WooGit-specific capabilities and does not become a generic unauthenticated remote-code-execution endpoint.

## 2. Credential handling

WordPress Application Passwords are preferred over the customer's primary WordPress password for programmatic access. WordPress documents Application Passwords as revocable per-application credentials intended for API access.

Storage design:

```text
credential plaintext
    -> TLS
    -> backend memory
    -> encrypt using application encryption key / KMS
    -> encrypted database value
```

The decrypted value exists only for the minimum time required to make an outbound request.

Do not store:

- raw Authorization headers;
- plaintext application passwords;
- provider API keys in logs;
- full request dumps containing credentials.

## 3. Mobile authentication

The app receives a WooGit access token/session, not a customer-site credential.

Recommended token model:

- short-lived access token;
- refresh token with rotation;
- server-side session/revocation record for sensitive actions;
- device/session metadata;
- rate limits.

The server is authoritative for account state.

## 4. Subscription enforcement

Every gateway request passes an authorization pipeline:

```text
authenticate
  -> account status
  -> subscription status
  -> site ownership
  -> entitlement
  -> capability
  -> rate limit
  -> idempotency policy
  -> outbound request
```

No client-side flag is trusted.

## 5. Site isolation

Every customer site has a unique opaque `site_id` owned by a WooGit account.

Authorization must verify both:

```text
request.account_id owns site_id
AND
request.account_id has capability X for site_id
```

Never accept a credential reference supplied by the client as authoritative. Resolve credential references from server-side site ownership.

## 6. Bridge authentication

The Bridge should not expose privileged commands anonymously.

A recommended model is a site-specific Bridge credential/token established during provisioning. The token is:

- scoped to one site;
- revocable;
- rotatable;
- stored server-side in encrypted form;
- never exposed to the browser widget.

The browser widget should authenticate to WooGit using a constrained public/site session mechanism, not the Bridge's privileged secret.

## 7. Request signing and replay protection

For high-value Bridge operations, use:

- timestamp;
- request ID;
- nonce/idempotency key;
- short validity window;
- server-side replay detection.

Do not use a static secret embedded in JavaScript as proof of authorization.

## 8. Idempotency

All CREATE-style mutations must support:

```text
Idempotency-Key: <client-generated stable key>
```

Store an operation record containing:

- account_id;
- site_id;
- operation_type;
- idempotency_key;
- request fingerprint;
- state;
- remote resource identifier if known;
- response summary;
- created_at/updated_at.

If the same key is retried, return the existing operation result instead of executing a second CREATE.

## 9. Timeout-after-success

A critical failure mode is:

```text
WooGit -> WordPress: CREATE
WordPress -> executes successfully
WordPress -> response lost / timeout
WooGit -> sees timeout
WooGit -> retries
```

The retry path must first reconcile state. Depending on the operation, reconciliation may use:

- idempotency key supported by the Bridge;
- deterministic external reference stored on the remote object;
- lookup by a unique client-generated identifier;
- operation status endpoint.

The system must have an automated test proving that one logical CREATE produces one remote resource even when the first response is lost after the remote commit.

## 10. Privacy

Default telemetry should avoid collecting direct identifiers unless needed.

For analytics:

- define event schemas;
- minimize fields;
- define retention periods;
- provide deletion/retention controls;
- document consent requirements where applicable;
- do not silently collect sensitive data.

User tracking is a product feature, not permission to collect arbitrary personal data.

## 11. AI security

AI providers must never receive unrestricted WordPress credentials.

The AI tool layer should expose typed tools such as:

```text
get_order(order_id)
get_product(product_id)
search_products(query)
get_customer(customer_id)
```

The model receives only the result needed for the task. Sensitive actions require explicit confirmation.

Never let an LLM directly construct arbitrary HTTP requests against customer sites.

## 12. Abuse controls

Implement:

- per-account rate limits;
- per-site rate limits;
- per-IP limits where appropriate;
- chat message throttling;
- AI spend limits;
- maximum request body size;
- maximum response size;
- outbound timeout and circuit breaker;
- audit events for privileged operations.

## 13. Operational security

Production requirements:

- HTTPS everywhere;
- secure cookies where browser sessions exist;
- CSRF protection for WordPress control-plane mutations;
- strict CORS allowlist;
- secret rotation procedure;
- encrypted backups;
- dependency/security scanning;
- alerting for repeated authentication failures;
- audit trail for credential creation/revocation;
- no production secrets in Git.

## 14. WordPress-specific notes

The official WordPress REST API supports Application Password authentication over HTTPS and exposes capability-controlled endpoints. The plugin endpoint also requires appropriate WordPress capabilities for plugin management. WooGit must preserve those permission boundaries rather than attempting to circumvent them.
