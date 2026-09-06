# WooGit Operations and Deployment

## 1. Initial deployment

A cost-conscious MVP can run on one VPS:

```text
VPS
├── Reverse proxy / TLS
├── WooGit WordPress control plane
├── WooGit API service
├── PostgreSQL
└── Redis
```

Do not run a large AI model on this server for the initial commercial architecture. Use external providers or a separately managed inference service.

## 2. Production separation

As traffic grows:

```text
Internet
  -> Load Balancer
      -> API instances
      -> WebSocket/chat instances
      -> Worker instances

Managed PostgreSQL
Managed Redis
Object Storage
Secret/KMS service
```

## 3. Configuration

Environment variables should hold non-secret configuration and references to secret storage.

Never commit:

- database passwords;
- application encryption keys;
- WordPress credentials;
- AI provider keys;
- payment provider secrets;
- JWT signing keys.

## 4. Backups

Minimum policy:

- automated PostgreSQL backups;
- encrypted backup storage;
- tested restore procedure;
- credential encryption key backup/recovery procedure kept separately;
- defined recovery point objective (RPO);
- defined recovery time objective (RTO).

A backup that cannot be restored is not a tested backup.

## 5. Observability

Every request should have:

- request ID;
- account ID where safe;
- site ID where safe;
- operation ID where applicable;
- latency;
- result category;
- upstream latency/status without secret headers.

Metrics:

- API request rate;
- error rate;
- p50/p95/p99 latency;
- outbound WordPress latency;
- WordPress failure rate;
- queue depth;
- worker failures;
- AI spend/usage;
- active subscriptions;
- bridge health.

## 6. Circuit breakers

If a customer site is failing repeatedly, stop aggressive retries and mark it degraded. Prevent one broken site from consuming all gateway resources.

## 7. Rate limiting

At minimum:

- account-level;
- site-level;
- endpoint-level;
- IP-level for public endpoints;
- chat-level;
- AI spend/requests.

## 8. Deployment strategy

Use:

```text
commit
 -> automated tests
 -> security/static checks
 -> staging
 -> smoke tests
 -> production
```

Database migrations must be backward-compatible with the currently deployed application during rolling deploys.

## 9. Incident handling

For credential exposure:

1. revoke affected credential;
2. invalidate bridge/session tokens if needed;
3. inspect audit logs;
4. rotate encryption/provider credentials when required;
5. notify affected customers according to policy;
6. document root cause.

For a subscription enforcement bug:

1. disable the affected capability at the gateway;
2. preserve evidence;
3. fix authorization logic;
4. run regression tests;
5. redeploy;
6. verify with a test account and an expired account.

## 10. Cost control

Do not overbuild early.

The first architecture should be able to run cheaply while preserving these boundaries:

- PostgreSQL is the durable source of application state;
- Redis is disposable acceleration/queue state;
- API instances are stateless;
- workers are horizontally scalable;
- AI is externalized;
- WordPress control plane can be scaled separately.
