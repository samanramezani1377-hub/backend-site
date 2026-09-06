# محدوده محصول و مدل تجاری WooGit Backend

> وضعیت: **V1 — Locked**

## ۰. مرز قطعی

این مخزن Backend اپ Android موجود WooGit است. اپ مستقل است و نباید در این repository بازسازی یا از نظر UI/UX بازطراحی شود.

```text
WooGit Android — Existing Client
          ↓
WooGit Backend / Main Plugin
          ↓
Customer WordPress / WooCommerce
```

`WooGit Main Plugin` روی WordPress اصلی WooGit قرار دارد و مغز Backend این پروژه است.

`WooGit Gateway Plugin` یک Plugin مستقل روی سایت مشتری است و فعلاً کاملاً خارج از Scope توسعه این پروژه است.

## ۱. محصول V1

هدف V1 این است که Backend با کمترین تغییر ممکن به Client موجود، مسیر محافظت‌شده‌ای بین اپ و سایت مشتری ایجاد کند.

Backend مسئول:

- WooGit Session؛
- Account lifecycle؛
- Site Identity و ownership؛
- Trial / Subscription؛
- Entitlement؛
- Version Gate؛
- Security و Rate Limit؛
- Controlled Forwarding؛
- Idempotency؛
- Timeout-after-success؛
- Audit و Operations.

Backend نباید برای عملیات عادی یک Mirror از WooCommerce بسازد.

## ۲. Client موجود و Connection

اپ موجود در صفحه اتصال این اطلاعات را دریافت/نگهداری می‌کند:

1. HTTP/HTTPS؛
2. Store URL؛
3. WooCommerce Consumer Key؛
4. WooCommerce Consumer Secret؛
5. WordPress Username؛
6. WordPress Application Password.

Backend Contract باید همین چهار Customer Credential را به‌عنوان ورودی مقصد در نظر بگیرد و برای V1 نیاز به تغییر بنیادی فرم اتصال نداشته باشد.

## ۳. مالکیت تنظیمات Customer

هر Customer Site تنظیمات WooCommerce خودش را روی همان WordPress نگه می‌دارد. این تنظیمات با تنظیمات WooGit Backend یکی نیستند.

```text
WooGit Backend / Main Plugin
├── Account
├── Site Identity
├── WooGit Session
├── Subscription
└── Entitlement

Customer WordPress / WooCommerce
├── WooCommerce Settings
├── Products
├── Orders
├── Customers
├── Categories / Variations
├── Media
└── سایر Store Data
```

Backend منبع حقیقت تنظیمات داخلی WooCommerce مشتری نیست؛ Customer WordPress/WooCommerce منبع اصلی داده و تنظیمات فروشگاه است.

Backend فقط از طریق Controlled Forwarding و Credentialهای همان Site به WooCommerce دسترسی می‌گیرد.

## ۴. دو نوع Credential

```text
WooGit Session
    → احراز و مجوز Client در Backend

WP Username
+ WP Application Password
+ WC Consumer Key
+ WC Consumer Secret
    → احراز Backend در Customer Site
```

Access Token + Refresh Token جزو معماری V1 نیست.

Credential Vault برای هر Request اجباری نیست. در Proxy عادی، Client می‌تواند Customer Credentials را همراه Request ارسال کند.

## ۵. Onboarding / Verification

در اولین اتصال ممکن است Session هنوز وجود نداشته باشد؛ بنابراین Onboarding یک Flow مستقل است:

```text
Customer Credentials
      ↓
WordPress reachability/authentication
      ↓
WooCommerce verification
      ↓
Site Identity
      ↓
Existing Account OR New Account
      ↓
Trial eligibility
      ↓
WooGit Session
      ↓
Normal requests
```

Verification باید read-only باشد و هیچ Product/Order/Media mutation صرفاً برای تست انجام نشود.

## ۶. Site موجود

برای Site Identity موجود:

```text
Verify credentials
   ↓
Resolve Site Identity
   ↓
Resolve owning Account
   ↓
Account/Subscription/Entitlement checks
   ↓
WooGit Session
```

موفقیت اتصال واقعی به همان Site مبنای احراز دسترسی به Site است؛ Login عادی Google/email بخشی از Flow فعلی نیست.

## ۷. Site جدید

برای Site بدون Account قبلی:

```text
Verification موفق
   ↓
New Site Identity
   ↓
Email + First Name + Last Name
   ↓
Account creation
   ↓
Trial eligibility
   ↓
WooGit Session
```

Trial برابر ۱۵ روز است و به Site Identity/دامنه تعلق دارد، نه صرفاً به Email یا Google Account.

## ۸. درخواست عادی

```text
Android
  ↓
WooGit Session + site_id
+ Customer Credentials
+ operation/path/query/body
  ↓
Main Plugin
  ├─ Session
  ├─ Account status
  ├─ Trial/Subscription
  ├─ Entitlement
  ├─ Site ownership
  ├─ Version/Security/Rate Limit
  └─ Idempotency where required
  ↓
Controlled Forwarding
  ↓
Customer WordPress/WooCommerce
  ↓
Minimum necessary transformation
  ↓
Android
```

در صورت Account بسته/غیرفعال یا Subscription/Trial منقضی، Backend نباید Request را به Customer Site ارسال کند.

## ۹. Controlled Proxy

Backend یک Proxy عمومی URL دلخواه نیست.

مقصد از `site_id` و Site Identity ثبت‌شده resolve می‌شود و فقط operation/pathهای مجاز قابل Forward هستند.

```text
/proxy?url=https://anything.com  ❌
```

نام `gateway` در API path، در صورت استفاده، فقط نام integration surface داخل Main Plugin است و به `WooGit Gateway Plugin` مشتری اشاره نمی‌کند.

## ۱۰. Credential Vault

Vault یک قابلیت optional است.

در مسیر عادی:

```text
Client → Session + Customer Credentials → Backend → Customer Site
```

هیچ Vault lookup اجباری برای هر Request وجود ندارد.

Vault فقط در صورت نیاز واقعی به عملیات بدون حضور Client، مانند بعضی Background Job/Webhookها، قابل استفاده است.

Customer Credentials در هیچ Log، Error Response، Audit Metadata یا Telemetry عمومی ثبت نمی‌شوند.

## ۱۱. Account / Subscription / Entitlement

Backend مرجع نهایی این موارد است:

```text
Account
  └── Site Identity
        └── Subscription / Trial
              └── Entitlements
```

Client نمی‌تواند با وضعیت محلی، Flag یا تاریخ محلی این policy را دور بزند.

## ۱۲. Idempotency و Timeout-after-success

تمام CREATE mutationهای موردنیاز باید idempotent باشند.

```text
Client → CREATE
Backend → Customer
Customer → SUCCESS
response lost
Client → retry same operation identity
Backend → previous result / reconciliation
```

Retry نباید CREATE دوم ایجاد کند.

## ۱۳. Currency

Currency از Customer WooCommerce می‌آید. Backend نباید واحد پول را فرض، hard-code یا بی‌دلیل تبدیل کند و باید context مالی موردنیاز Client را حفظ کند.

## ۱۴. عملیات اصلی Client

V1 باید با حوزه‌های واقعی موجود در Client قابل تطبیق باشد:

- Products؛
- Orders؛
- Customers؛
- Categories؛
- Variations؛
- Attributes / Terms؛
- Media؛
- Sync؛
- Conflicts / reconciliation؛
- Product/Order mutations.

## ۱۵. چیزهای خارج از V1 Backend

- بازسازی Android App؛
- تغییر UI/UX اپ برای سازگار شدن با Backend؛
- پیاده‌سازی `WooGit Gateway Plugin` سایت مشتری؛
- الزام PostgreSQL؛
- الزام Redis؛
- الزام Queue service مستقل؛
- Mirror دائمی WooCommerce.
