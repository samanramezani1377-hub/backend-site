# مدل داده WooGit Backend

> وضعیت: V1 — Locked

## اصل مالکیت داده

Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است. WooGit Backend مالک Account، Site Identity، Session، Subscription و Entitlement است؛ اما تنظیمات و داده‌های خود WooCommerce مشتری روی همان Customer WordPress باقی می‌ماند.

## ۱. accounts

- id (UUID)
- email
- display_name
- status: active / suspended / deleted
- created_at
- updated_at

## ۲. account_sessions

نماینده **WooGit Session** است. در V1 یک Session معتبر مکانیزم احراز Client در Backend است؛ Access Token + Refresh Token وجود ندارد.

- id
- account_id
- session_reference / token_hash
- expires_at
- revoked_at
- last_seen_at
- created_at

مقدار خام Session نباید در دیتابیس یا Log ثبت شود.

## ۳. sites

هر Customer Site یک Site Identity مستقل در Backend دارد و تنظیمات اتصال آن با Site دیگر مخلوط نمی‌شود.

- id (UUID) — Backend `site_id`
- account_id
- canonical_url
- display_name
- wordpress_version (اختیاری)
- woocommerce_version (اختیاری)
- connection_status
- last_health_check_at
- created_at
- updated_at

`site_id` داخلی WooGit است و نباید صرفاً از Store ID محلی Android مشتق یا با آن یکی فرض شود.

برای یک Account نباید Site Identity تکراری برای همان Customer Site ایجاد شود.

## ۴. Customer Site Credentials — Request-scoped Only

برای اتصال به Customer Site، Client این چهار Credential مقصد را در صورت نیاز همراه Request ارسال می‌کند:

- WordPress Username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

این چهار مقدار Credential مربوط به **Customer WordPress/WooCommerce** هستند، نه Credential احراز Client در WooGit.

در V1، Backend این Credentialها را **هرگز در DB، Vault، Cache پایدار یا هیچ storage دائمی دیگری ذخیره نمی‌کند**. این Credentialها فقط برای همان Request مصرف می‌شوند.

در نتیجه در مدل داده V1:

- هیچ `site_credentials` table وجود ندارد؛
- هیچ `encrypted_secret` برای Customer Credential وجود ندارد؛
- هیچ Credential Vault برای Customer Credential وجود ندارد؛
- هیچ persistent credential storage برای background job، webhook یا عملیات بدون حضور Client وجود ندارد.

هر قابلیت آینده‌ای که به Credential پایدار نیاز داشته باشد خارج از قرارداد V1 است و نباید با فرض وجود چنین storageای طراحی شود.

## ۵. WooCommerce Settings

تنظیمات خود WooCommerce متعلق به Customer Site است و Backend آن‌ها را به تنظیمات WooGit تبدیل نمی‌کند.

```text
WooGit Backend
├── Account
├── Site Identity
├── Session
├── Subscription
└── Entitlement

Customer WordPress / WooCommerce
├── WooCommerce Settings
├── Products
├── Orders
├── Customers
├── Media
└── سایر Store Data
```

Backend فقط در صورت نیاز API مربوط به Customer WooCommerce را از طریق Controlled Forwarding مصرف می‌کند.

## ۶. plans

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

## ۷. plan_entitlements

- plan_id
- capability
- limit_value (nullable)
- configuration_json (nullable)

## ۸. subscriptions

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

## ۹. site_entitlements

- id
- account_id
- site_id
- capability
- state
- limit_value
- expires_at

## ۱۰. idempotency_operations

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

## ۱۱. audit_events

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

## ۱۲. chat / analytics / AI

در صورت فعال شدن، مدل‌های Chat، Analytics و AI باید مستقل باشند و برای مسیر اصلی Lightweight Proxy وابستگی اجباری ایجاد نکنند.

## ۱۳. نمای روابط

```text
Account
  |
  +-- Sessions (WooGit Session)
  +-- Sites
  |     +-- Connection Metadata (non-sensitive)
  |     +-- Request-scoped Customer Credentials (not persisted)
  |     +-- Site Entitlements
  |
  +-- Subscriptions -> Plans -> Entitlements
  +-- Idempotency Operations
  +-- Audit Events
  +-- optional Chat / Analytics / AI

Site
  |
  +-- Customer WordPress / WooCommerce
        +-- WooCommerce Settings
        +-- Products / Orders / Customers / Media / ...
```
