# مرز قطعی پروژه WooGit Backend

> وضعیت: V1 — Locked

این مخزن برای ساخت Backend اپ موجود WooGit است. اپ Android مستقل است و در وضعیت فعلی مستقیماً با Customer WordPress/WooCommerce کار می‌کند؛ اتصال تجاری آینده از مسیر Backend انجام می‌شود.

## مرز کامپوننت‌ها

- `WooGit Main Plugin`: Backend روی WordPress اصلی WooGit.
- `WooGit Gateway Plugin`: کامپوننت مستقل روی سایت مشتری و خارج از Scope فاز فعلی.
- Android App: Client مستقل در repository جداگانه.

## مسئولیت Backend V1

- WooGit Session؛
- Account lifecycle؛
- Site Identity و ownership؛
- Trial / Subscription؛
- Entitlement؛
- Version Gate؛
- Security و Rate Limit؛
- Connection Verification؛
- Controlled Forwarding؛
- Idempotency؛
- Timeout-after-success؛
- Reconciliation؛
- Audit و Operations؛
- persistence در WordPress DB.

### Customer Credentials — اصل قطعی V1

Backend V1 **هیچ Customer Credentialای را ذخیره نمی‌کند**.

چهار Credential مقصد:

1. WooCommerce Consumer Key؛
2. WooCommerce Consumer Secret؛
3. WordPress Username؛
4. WordPress Application Password.

این Credentialها در صورت نیاز همراه همان Request از Client ارسال می‌شوند و فقط برای همان Request مصرف می‌شوند.

Backend نباید آن‌ها را در DB، Vault، Cache پایدار، Log، Telemetry، Audit یا Response نگهداری/افشا کند. هیچ `site_credentials` table یا Credential Vault برای V1 وجود ندارد.

هر قابلیت آینده‌ای که به Credential پایدار نیاز داشته باشد خارج از Scope V1 است و نیازمند تصمیم معماری مستقل است.

## زیرساخت V1

```text
WordPress
  + WooGit Main Plugin
  + WordPress Database
```

PostgreSQL، Redis، Queue مستقل یا Backend distributed جداگانه برای V1 الزامی نیستند و فقط با تصمیم معماری جدید اضافه می‌شوند.

## Onboarding

```text
Customer Credentials
 ↓
WordPress Verification
 ↓
WooCommerce Verification
 ↓
Site Identity
 ↓
Existing/New Account
 ↓
Trial eligibility
 ↓
WooGit Session
```

Verification قبل از هر عملیات تجاری و read-only است.

## Normal Request

```text
WooGit Session
+ site_id
+ Customer Credentials (request-scoped)
+ controlled operation
       ↓
Session / Account / Subscription / Entitlement
Site Ownership / Version / Security / Rate Limit
       ↓
Controlled Forwarding
       ↓
Customer WordPress/WooCommerce
```

Account بسته/غیرفعال یا دسترسی منقضی نباید به Customer Site outbound request بفرستد.

## Controlled Proxy و SSRF

URL مقصد دلخواه ممنوع است. مقصد از Site Identity ثبت‌شده resolve می‌شود و فقط operation/pathهای مجاز قابل Forward هستند.

## Idempotency و Timeout-after-success

CREATE mutationهای موردنیاز باید idempotent باشند و retry پس از response-loss باید نتیجه قبلی یا وضعیت canonical را بازیابی کند؛ retry نباید resource تکراری بسازد.

## Currency و داده فروشگاه

Customer WordPress/WooCommerce منبع حقیقت Products، Orders، Customers، Categories، Variations و Media است. Backend نباید Currency را hard-code یا بی‌دلیل تبدیل کند و نباید Mirror دائمی WooCommerce بسازد.

## خارج از Scope

- ساخت/بازطراحی Android App؛
- UI/UX اپ؛
- APK و CI مخصوص Android؛
- پیاده‌سازی `WooGit Gateway Plugin` سایت مشتری؛
- الزام PostgreSQL/Redis/Queue مستقل؛
- Mirror دائمی WooCommerce.
