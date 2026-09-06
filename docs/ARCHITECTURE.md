# WooGit System Architecture

## 1. High-level topology

```text
                         Internet
                            |
             +--------------+--------------+
             |                             |
        WooGit Website                WooGit App
        WordPress CMS                     |
             |                            |
       Control Plane UI            HTTPS / short token
             |                            |
             +-------------+--------------+
                           |
                    WooGit API/Gateway
                           |
       +-------------------+-------------------+
       |          |          |         |        |
      Auth     Entitle    Sites/Vault  Chat    AI Gateway
       |          |          |         |        |
       +----------+----------+---------+--------+
                           |
                      Redis / Queue
                           |
                      PostgreSQL
                           |
                           |
                    outbound HTTPS
                           |
                  Customer WordPress
                           |
                    WooGit Bridge
```

## 2. Control plane vs data plane

### Control plane

WordPress is allowed to manage:

- WooGit accounts;
- plan definitions;
- subscriptions;
- entitlements;
- customer/site records;
- operational configuration;
- support/admin views;
- public website content.

### Data plane

The Gateway handles:

- authenticated mobile requests;
- entitlement checks;
- site credential resolution;
- outbound calls to customer WordPress;
- response normalization;
- rate limits;
- idempotency;
- chat traffic;
- realtime traffic;
- webhook/event ingestion.

This separation prevents the WordPress admin UI from becoming a required hop for every API request.

## 3. Why WordPress is still central

The commercial site can be WordPress because WordPress already provides users, roles/capabilities, content management, REST APIs and a mature plugin ecosystem. It can also serve as the operator control plane through a WooGit management plugin.

The decision is not “WordPress vs backend”. It is:

```text
WordPress = CMS + Control Plane
Gateway    = Execution Plane
```

## 4. Connection flow

```text
1. App collects domain + WordPress application credential.
2. App sends it once to WooGit API over TLS.
3. Backend canonicalizes the URL and creates a site connection operation.
4. Backend validates authentication and required capabilities against WordPress.
5. Backend stores the credential encrypted.
6. Backend returns connection success and non-sensitive site metadata.
7. App receives a WooGit session/access token, not the site credential.
```

The backend should never log the credential, Authorization header, raw request body containing the credential, or a full customer response that might contain secrets.

## 5. Normal request flow

```text
App
 -> access token
 -> Gateway
 -> authenticate account/session
 -> check subscription
 -> check site entitlement
 -> check capability
 -> resolve encrypted credential
 -> call customer WordPress
 -> sanitize response
 -> return response to App
```

If any authorization step fails, the customer WordPress endpoint is not contacted.

## 6. Expired account flow

```text
App -> Gateway
          |
          +-- account active? NO
          |
          +-- return 402/403-style business error
          |
          X no outbound request
```

The exact HTTP status contract should be finalized during implementation, but the invariant is that the remote site is never contacted after the server has determined that the request is not entitled.

## 7. Bridge discovery

After installation/activation the Bridge should expose a small discovery endpoint returning:

```json
{
  "bridge": "woogit",
  "protocol_version": 1,
  "plugin_version": "x.y.z",
  "site_id": "opaque-id",
  "capabilities": ["chat", "analytics", "commerce"],
  "status": "ready"
}
```

No secret should be returned in discovery.

## 8. Chat architecture

```text
Browser
  -> Bridge-injected widget
  -> Gateway Chat API
  -> conversation store
  -> AI router OR human inbox
  -> response stream
  -> widget
```

For order-aware AI, the AI tool layer calls WooGit's authorized site service rather than allowing the model to invent order state.

## 9. Analytics architecture

```text
Browser / WP hooks
  -> lightweight event collector
  -> Gateway ingestion
  -> queue
  -> analytics worker
  -> PostgreSQL/analytics storage
```

The customer's WordPress database should not become the primary event warehouse.

## 10. Scaling path

### MVP

One VPS can host:

- WordPress control plane;
- API service;
- PostgreSQL;
- Redis.

### Growth

Split API instances behind a load balancer and move PostgreSQL/Redis to managed services where useful.

```text
Load Balancer
  -> API 1
  -> API 2
  -> API N
       |
   PostgreSQL
   Redis
   Workers
```

### High volume

Separate ingestion, chat/realtime, gateway and workers. Introduce dedicated analytics storage only when measured load justifies it.

## 11. Failure boundaries

Customer site unavailable:

- do not mark the WooGit account invalid;
- record site health separately;
- retry only idempotent operations or operations with safe reconciliation;
- surface a clear site connectivity error.

WooGit API unavailable:

- app cannot bypass the gateway;
- local UI can show cached non-sensitive state;
- no direct customer-site fallback is allowed for commercial-gateway traffic.

Credential revoked at WordPress:

- mark site credential invalid;
- require reconnection/rotation;
- do not repeatedly hammer the site.
