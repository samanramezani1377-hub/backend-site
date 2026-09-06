# WooGit API Contract

This document defines the intended public boundaries. It is deliberately framework-neutral so the contract can be implemented in Laravel without coupling the Android client to internal classes.

## 1. API groups

```text
/api/v1/auth
/api/v1/account
/api/v1/sites
/api/v1/subscription
/api/v1/gateway
/api/v1/bridge
/api/v1/chat
/api/v1/analytics
/api/v1/ai
```

## 2. Authentication

### POST `/api/v1/auth/login`

Authenticates a WooGit account.

### POST `/api/v1/auth/refresh`

Rotates/refreshes a session. Raw refresh tokens are never returned to logs.

### POST `/api/v1/auth/logout`

Revokes the current session.

## 3. Site connection

### POST `/api/v1/sites`

Creates a site connection operation.

Request concept:

```json
{
  "url": "https://example.com",
  "wordpress_username": "admin",
  "wordpress_application_password": "..."
}
```

Requirements:

- HTTPS to WooGit.
- never log the request body.
- canonicalize and validate URL.
- use an idempotency key.
- validate before persisting.
- return only safe metadata.

Response concept:

```json
{
  "site_id": "opaque-site-id",
  "status": "connected",
  "display_name": "Example Store",
  "bridge": {
    "installed": false,
    "required": false
  }
}
```

The credential itself must never appear in the response.

## 4. Site listing

### GET `/api/v1/sites`

Returns sites owned by the authenticated account.

### GET `/api/v1/sites/{site_id}`

Returns safe connection and entitlement metadata.

### DELETE `/api/v1/sites/{site_id}`

Disconnects the site. This should revoke/delete the stored credential and invalidate Bridge credentials according to the disconnect policy.

## 5. Gateway

A generic gateway endpoint should not become an unrestricted proxy. Prefer typed operations.

Bad:

```text
POST /gateway?url=https://customer-site/... 
```

Good:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

Typed operations make authorization, auditing, rate limiting and idempotency explicit.

For an initial migration from the existing WooGit app, a constrained internal proxy may be used, but it must enforce an allowlist of WooCommerce/WordPress paths and HTTP methods. Arbitrary outbound URL forwarding is prohibited.

## 6. Mutation contract

Every CREATE/activation/provisioning mutation accepts:

```http
Idempotency-Key: 9c2c...
```

The response should include:

```json
{
  "operation_id": "opaque-operation-id",
  "status": "completed"
}
```

For asynchronous operations:

```json
{
  "operation_id": "opaque-operation-id",
  "status": "pending"
}
```

Client retries with the same key must return the existing operation state.

## 7. Bridge endpoints

Proposed namespace on the customer site:

```text
/wp-json/woogit/v1/discovery
/wp-json/woogit/v1/health
/wp-json/woogit/v1/command
/wp-json/woogit/v1/events
/wp-json/woogit/v1/chat/config
```

The exact endpoint set should be reduced to the minimum needed by the feature set.

## 8. Chat

### POST `/api/v1/sites/{site_id}/chat/conversations`

Creates a conversation subject to entitlement.

### POST `/api/v1/chat/conversations/{conversation_id}/messages`

Adds a message.

### GET `/api/v1/chat/conversations/{conversation_id}`

Returns authorized conversation data.

For streaming, use SSE/WebSocket with a short-lived chat session token. Do not expose privileged site credentials to the browser.

## 9. Analytics ingestion

### POST `/api/v1/sites/{site_id}/events`

Accepts a bounded batch of typed events.

Requirements:

- strict schema;
- maximum batch size;
- maximum event size;
- rate limit;
- deduplication/event ID;
- privacy filtering;
- asynchronous processing.

## 10. AI

### POST `/api/v1/ai/chat`

Routes an authorized AI request through WooGit's provider abstraction.

The request should identify a logical model, not an arbitrary provider URL.

```json
{
  "model": "balanced",
  "messages": [],
  "site_context": {
    "site_id": "opaque-site-id"
  }
}
```

The backend resolves provider/model configuration server-side.

## 11. Error contract

Use a stable machine-readable shape:

```json
{
  "error": {
    "code": "subscription_expired",
    "message": "WooGit subscription has expired.",
    "request_id": "..."
  }
}
```

Never put credentials, SQL, stack traces or provider secrets in production error responses.

## 12. Request IDs

Every request receives a server-generated request ID. Propagate it to internal logs and, where safe, return it to the client for support diagnostics.

## 13. Idempotency reconciliation endpoint

Recommended internal/public-to-app endpoint:

`GET /api/v1/operations/{operation_id}`

This lets a client recover from a timeout without repeating the underlying mutation.
