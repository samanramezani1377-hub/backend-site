# مرز قطعی پروژه WooGit Backend

## ۱. هدف

این مخزن برای **ساخت Backend اپ موجود WooGit** است.

اپ Android WooGit یک پروژه مستقل و از قبل ساخته‌شده است. در وضعیت فعلی مستقیماً با Customer WordPress/WooCommerce کار می‌کند؛ پس از آماده شدن Backend، مسیر تجاری آن به Backend منتقل می‌شود.

```text
Android App موجود
      ↓
WooGit Backend / WooGit Main Plugin
      ↓
Customer WordPress / WooCommerce
```

این مخزن نباید Android App، UI/UX یا معماری داخلی آن را بازسازی کند.

## ۲. تفکیک قطعی کامپوننت‌ها

### WooGit Main Plugin

پلاگین روی **WordPress اصلی WooGit** است و Backend اصلی این repository را اجرا می‌کند.

### WooGit Gateway Plugin

پلاگین مستقل روی **WordPress/WooCommerce سایت مشتری** است.

```text
Customer WordPress/WooCommerce
        └── WooGit Gateway Plugin
```

این کامپوننت در فاز فعلی **خارج از scope توسعه** است و نباید در این repository پیاده‌سازی، refactor یا migrate شود.

وجود `Gateway` در نام یک API یا internal integration به معنی این Plugin مشتری نیست.

## ۳. مسئولیت Backend V1

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

Credential Vault یک قابلیت **اختیاری** است و برای Proxy عادی اجباری نیست.

## ۴. زیرساخت V1

```text
WordPress
  + WooGit Main Plugin
  + WordPress Database
```

PostgreSQL، Redis، Queue مستقل یا Backend distributed جداگانه برای V1 الزامی نیستند و فقط با نیاز اثبات‌شده و تصمیم معماری جدید اضافه می‌شوند.

## ۵. قرارداد با Android Client موجود

Client فعلی این Customer Credentials را دارد:

1. Store URL / protocol؛
2. WooCommerce Consumer Key؛
3. WooCommerce Consumer Secret؛
4. WordPress Username؛
5. WordPress Application Password.

Client فعلی این Credentialها را به‌صورت امن محلی نگهداری می‌کند و برای اتصال مستقیم به Customer Site استفاده می‌کند. Backend باید Migration را طوری طراحی کند که همین Credential surface تا حد ممکن حفظ شود. fileciteturn229file1L23-L29 fileciteturn229file2L42-L55

در معماری هدف:

```text
WooGit Session
  → Backend authentication/authorization

4 Customer Credentials
  → Customer Site authentication
```

Access/Refresh Token مدل Backend نیست.

## ۶. Onboarding

اولین اتصال ممکن است بدون Session کامل باشد:

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

Verification باید قبل از هر عملیات تجاری انجام شود و read-only باشد.

## ۷. Normal Request

```text
WooGit Session
+ site_id
+ Customer Credentials
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

## ۸. Controlled Proxy و SSRF

URL مقصد دلخواه ممنوع است. مقصد از Site Identity ثبت‌شده resolve می‌شود و فقط operation/pathهای مجاز قابل Forward هستند.

```text
/proxy?url=https://anything.com  ❌
```

## ۹. Idempotency و Timeout-after-success

CREATE mutationهای موردنیاز باید idempotent باشند و Backend باید سناریوی زیر را پوشش دهد:

```text
Customer SUCCESS
      ↓
response lost / timeout
      ↓
Client retry same operation identity
      ↓
previous result / reconciliation
```

## ۱۰. Currency و داده فروشگاه

Customer WordPress/WooCommerce منبع حقیقت Products، Orders، Customers، Categories، Variations و Media است.

Backend نباید داده را بی‌دلیل Mirror کند و نباید Currency را hard-code یا بی‌دلیل تبدیل کند.

## ۱۱. خارج از Scope

- ساخت/بازطراحی Android App؛
- UI/UX اپ؛
- Compose/Navigation/State اپ؛
- APK و CI مخصوص Android؛
- پیاده‌سازی `WooGit Gateway Plugin` روی سایت مشتری؛
- الزام PostgreSQL/Redis/Queue مستقل؛
- Mirror دائمی WooCommerce.

## ۱۲. اصل نهایی

**این repository Backend اپ موجود است؛ WooGit Main Plugin مغز Backend روی سایت اصلی WooGit است؛ WooGit Gateway Plugin یک کامپوننت مستقل روی سایت مشتری و خارج از فاز فعلی است.**

تمام تصمیم‌های API، Data Model، Security و Operations باید با این مرزها و با Client واقعی موجود سازگار باشند.
