# WooGit Data Model

The schema below is logical. Exact SQL types, indexes and partitioning are implementation details.

## 1. accounts

Represents the WooGit customer account.

Fields:

- id (UUID)
- email
- display_name
- status: active / suspended / deleted
- created_at
- updated_at

## 2. account_sessions

- id
- account_id
- device_id
- refresh_token_hash
- expires_at
- revoked_at
- last_seen_at
- created_at

Never store raw refresh tokens.

## 3. sites

Represents one customer WordPress site.

- id (UUID)
- account_id
- canonical_url
- display_name
- wordpress_version (optional)
- woocommerce_version (optional)
- bridge_version (optional)
- bridge_status
- connection_status
- last_health_check_at
- created_at
- updated_at

Unique constraint: `(account_id, canonical_url)`.

## 4. site_credentials

- id
- site_id
- credential_type
- encrypted_secret
- key_version
- last_validated_at
- last_used_at
- revoked_at
- created_at
- updated_at

The encrypted secret is never returned through the API.

## 5. plans

- id
- code
- name
- duration_days
- price_minor
- currency
- site_limit
- status
- created_at
- updated_at

## 6. plan_entitlements

- plan_id
- capability
- limit_value (nullable)
- configuration_json (nullable)

Examples:

```text
chat.enabled = true
analytics.retention_days = 30
sites.max = 3
ai.credits = 1000000
```

## 7. subscriptions

- id
- account_id
- plan_id
- status: trial / active / expired / cancelled / suspended
- starts_at
- expires_at
- trial_ends_at
- auto_renew (optional)
- provider_reference (optional)
- created_at
- updated_at

The backend uses this table as the authority for entitlement decisions.

## 8. site_entitlements

Optional per-site overrides:

- id
- account_id
- site_id
- capability
- state
- limit_value
- expires_at

Useful when one account has multiple sites with different capabilities.

## 9. idempotency_operations

- id
- account_id
- site_id
- idempotency_key
- operation_type
- request_hash
- state
- remote_reference
- result_json (sanitized)
- created_at
- updated_at

Unique constraint should cover the scope required by the API contract, normally account/site + idempotency key.

## 10. bridge_registrations

- id
- site_id
- bridge_instance_id
- protocol_version
- plugin_version
- token_hash
- status
- last_seen_at
- created_at
- rotated_at

Store only a hash of a bearer token when practical; the plaintext token is shown/handled only during provisioning.

## 11. chat_conversations

- id
- account_id
- site_id
- visitor_reference (privacy-minimized)
- customer_reference (nullable)
- state: open / waiting / human / ai / closed
- assigned_operator_id (nullable)
- created_at
- updated_at

## 12. chat_messages

- id
- conversation_id
- sender_type
- content
- model/provider metadata (nullable)
- created_at

Retention must be configurable.

## 13. analytics_events

- id
- account_id
- site_id
- event_type
- anonymous_visitor_id (nullable)
- customer_reference (nullable)
- properties_json
- occurred_at
- received_at

High-volume installations may require partitioning or a dedicated analytics store later.

## 14. ai_accounts / ai_credits

Suggested separation:

`ai_accounts`

- id
- account_id
- mode: woogit / byok
- provider (nullable)
- encrypted_provider_key (nullable)
- key_version
- status

`ai_credit_ledger`

- id
- account_id
- transaction_type
- amount
- balance_after
- provider
- model
- request_reference
- created_at

Use a ledger rather than mutating only a single balance field so consumption can be audited and reconciled.

## 15. audit_events

- id
- account_id
- site_id (nullable)
- actor_type
- actor_id (nullable)
- action
- target_type
- target_id
- metadata_json
- created_at

Never put raw credentials into audit metadata.

## 16. Relationship overview

```text
Account
  |
  +-- Sessions
  +-- Sites
  |     +-- Credentials
  |     +-- Bridge Registration
  |     +-- Site Entitlements
  |     +-- Conversations
  |     +-- Analytics Events
  |
  +-- Subscriptions -> Plan -> Plan Entitlements
  +-- AI Account -> AI Credit Ledger
  +-- Audit Events
```
