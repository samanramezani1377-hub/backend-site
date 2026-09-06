# مدل داده WooGit

طرح زیر منطقی است. نوع دقیق SQL و جزئیات پیاده‌سازی بعداً تعیین می‌شوند.

اصل مهم V1: Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است. Backend برای درخواست‌های عادی Customer Credentials را از Client دریافت می‌کند و برای Forward همان Request مصرف می‌کند؛ بنابراین `site_credentials` برای Proxy عادی منبع اجباری Credential نیست.

## ۱. accounts

- id (UUID)
- email
- display_name
- status: active / suspended / deleted
- created_at
- updated_at

## ۲. account_sessions

نماینده WooGit Session است.

- id
- account_id
- session_reference / token_hash (بسته به مدل Session نهایی)
- expires_at
- revoked_at
- last_seen_at
- created_at

جزئیات نوع Token/Session در این سند هنوز به‌عنوان تصمیم مستقل قفل نشده است؛ مهم این است که Session برای احراز مصرف‌کننده در Backend استفاده شود.

## ۳. sites

Site Identity مستقل Backend:

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

محدودیت یکتا باید از ایجاد Site Identity تکراری برای یک Account جلوگیری کند.

## ۴. site_credentials — اختیاری/سناریویی

در Proxy عادی Credentialها از Client در همان Request می‌آیند و این جدول برای هر Request خوانده نمی‌شود.

اگر قابلیت‌هایی مانند background jobs، webhooks یا عملیات بدون حضور Client نیاز به Credential پایدار داشته باشند، می‌توان در این جدول Credential را به‌صورت رمزنگاری‌شده نگهداری کرد:

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

این جدول **جزء مسیر اجباری Lightweight Proxy عادی نیست**.

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

این داده مرجع تصمیم‌گیری درباره دسترسی Backend است.

## ۸. site_entitlements

برای Overrideهای اختصاصی Site:

- id
- account_id
- site_id
- capability
- state
- limit_value
- expires_at

## ۹. idempotency_operations

برای mutationهای نیازمند Idempotency:

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

محدودیت یکتا باید حداقل Account + Site + Idempotency Key را پوشش دهد.

## ۱۰. bridge_registrations

در صورت فعال بودن Bridge:

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

## ۱۱. chat / analytics / AI

مدل‌های Chat، Analytics و AI در صورت فعال بودن این قابلیت‌ها می‌توانند در جدول‌های مستقل نگهداری شوند؛ این قابلیت‌ها نباید برای مسیر اصلی Lightweight Proxy وابستگی اجباری ایجاد کنند.

## ۱۲. audit_events

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

هیچ Customer Credential خامی در metadata ذخیره نشود.

## ۱۳. نمای روابط

```text
Account
  |
  +-- Sessions
  +-- Sites
  |     +-- optional Credential Storage
  |     +-- Bridge Registration
  |     +-- Site Entitlements
  |
  +-- Subscriptions -> Plans -> Entitlements
  +-- Idempotency Operations
  +-- Audit Events
  +-- optional Chat / Analytics / AI
```
