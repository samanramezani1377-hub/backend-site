# Proposed Repository Structure

The repository should evolve toward this structure as implementation begins:

```text
backend-site/
├── app/
│   ├── Domain/
│   │   ├── Accounts/
│   │   ├── Sites/
│   │   ├── Subscriptions/
│   │   ├── Gateway/
│   │   ├── Bridge/
│   │   ├── Chat/
│   │   ├── Analytics/
│   │   └── AI/
│   ├── Application/
│   ├── Infrastructure/
│   └── Http/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   ├── Integration/
│   └── Security/
├── wordpress-control-plane/
│   └── woogit-admin/
├── docker/
├── docs/
└── .github/
    └── workflows/
```

## Domain boundaries

### Accounts

Identity, sessions, account lifecycle and device/session management.

### Sites

Site ownership, connection state, health and credential references.

### Subscriptions

Plans, subscriptions, entitlements and expiration.

### Gateway

Authorization pipeline and typed outbound operations.

### Bridge

Provisioning, capability negotiation and Bridge protocol.

### Chat

Conversations, messages, operator assignment and realtime sessions.

### Analytics

Event validation, ingestion and aggregation.

### AI

Provider abstraction, usage metering, credits and tools.

## Testing layers

### Unit

Pure authorization, entitlement, idempotency and domain logic.

### Feature

API request/response contracts and authentication.

### Integration

Real PostgreSQL/Redis and a controlled WordPress test instance.

### Security

- cross-account access attempts;
- expired subscription bypass;
- forged site IDs;
- replayed Bridge requests;
- oversized payloads;
- arbitrary proxy/SSRF attempts;
- credential leakage checks.

### Reliability

Explicit tests for:

- timeout after remote success;
- duplicated request;
- lost webhook response;
- worker retry;
- customer WordPress downtime;
- Redis restart;
- API process restart.

## Implementation rule

Do not create empty placeholder modules merely to make the tree look complete. A directory should be introduced when its first real implementation or test exists.
