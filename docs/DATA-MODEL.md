# مدل داده WooGit

طرح زیر منطقی است. نوع دقیق SQL، ایندکس‌ها و Partitioning جزئیات پیاده‌سازی هستند.

## ۱. accounts

حساب مشتری WooGit را نشان می‌دهد.

فیلدها:

- id (UUID)
- email
- display_name
- status: active / suspended / deleted
- created_at
- updated_at

## ۲. account_sessions

- id
- account_id
- device_id
- refresh_token_hash
- expires_at
- revoked_at
- last_seen_at
- created_at

Refresh Token خام هرگز ذخیره نشود.

## ۳. sites

یک سایت WordPress مشتری را نشان می‌دهد.

- id (UUID)
- account_id
- canonical_url
- display_name
- wordpress_version (اختیاری)
- woocommerce_version (اختیاری)
- bridge_version (اختیاری)
- bridge_status
- connection_status
- last_health_check_at
- created_at
- updated_at

محدودیت یکتا: `(account_id, canonical_url)`.

## ۴. site_credentials

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

Secret رمزنگاری‌شده هرگز از طریق API برگردانده نمی‌شود.

## ۵. plans

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

## ۶. plan_entitlements

- plan_id
- capability
- limit_value (nullable)
- configuration_json (nullable)

نمونه:

```text
chat.enabled = true
analytics.retention_days = 30
sites.max = 3
ai.credits = 1000000
```

## ۷. subscriptions

- id
- account_id
- plan_id
- status: trial / active / expired / cancelled / suspended
- starts_at
- expires_at
- trial_ends_at
- auto_renew (اختیاری)
- provider_reference (اختیاری)
- created_at
- updated_at

بک‌اند از این جدول به‌عنوان مرجع اصلی تصمیم‌گیری درباره مجوز استفاده می‌کند.

## ۸. site_entitlements

برای Overrideهای اختصاصی هر سایت:

- id
- account_id
- site_id
- capability
- state
- limit_value
- expires_at

وقتی یک حساب چند سایت با قابلیت‌های متفاوت دارد مفید است.

## ۹. idempotency_operations

- id
- account_id
- site_id
- idempotency_key
- operation_type
- request_hash
- state
- remote_reference
- result_json (پاک‌سازی‌شده)
- created_at
- updated_at

محدودیت یکتا باید دامنه لازم برای قرارداد API را پوشش دهد؛ معمولاً account/site + idempotency key.

## ۱۰. bridge_registrations

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

در صورت امکان فقط Hash توکن Bearer ذخیره شود؛ توکن متنی فقط هنگام Provisioning نمایش/استفاده شود.

## ۱۱. chat_conversations

- id
- account_id
- site_id
- visitor_reference (حداقل‌سازی‌شده از نظر حریم خصوصی)
- customer_reference (nullable)
- state: open / waiting / human / ai / closed
- assigned_operator_id (nullable)
- created_at
- updated_at

## ۱۲. chat_messages

- id
- conversation_id
- sender_type
- content
- model/provider metadata (nullable)
- created_at

مدت نگهداری باید قابل تنظیم باشد.

## ۱۳. analytics_events

- id
- account_id
- site_id
- event_type
- anonymous_visitor_id (nullable)
- customer_reference (nullable)
- properties_json
- occurred_at
- received_at

نصب‌های پرترافیک ممکن است بعداً به Partitioning یا انبار تحلیل اختصاصی نیاز داشته باشند.

## ۱۴. ai_accounts / ai_credits

تفکیک پیشنهادی:

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

به‌جای تغییر فقط یک فیلد موجودی، از Ledger استفاده کنید تا مصرف قابل حسابرسی و تطبیق باشد.

## ۱۵. audit_events

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

هیچ اعتبار خامی را داخل متادیتای حسابرسی قرار ندهید.

## ۱۶. نمای روابط

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
