# معماری نهایی Backend ووگیت

> وضعیت: **V1 Architecture — Locked**
>
> این repository فقط Backend اپ Android موجود WooGit را می‌سازد. اپ در repository مستقل قرار دارد و در این سند به‌عنوان Client خارجی در نظر گرفته می‌شود.
>
> **تفکیک قطعی:** `WooGit Main Plugin` و `WooGit Gateway Plugin` دو کامپوننت کاملاً جدا هستند.
>
> - **WooGit Main Plugin:** پلاگین نصب‌شده روی WordPress اصلی خود WooGit و مغز Backend فعلی این پروژه است.
> - **WooGit Gateway Plugin:** پلاگین مستقل نصب‌شده روی WordPress/WooCommerce سایت مشتری. این کامپوننت فعلاً خارج از scope توسعه است و نباید با Main Plugin یا Backend فعلی یکی فرض شود.

## ۱. تصمیم اصلی

Backend V1 سبک است و روی WordPress اصلی WooGit و `WooGit Main Plugin` اجرا می‌شود. Backend بین اپ موجود و Customer WordPress/WooCommerce قرار می‌گیرد. هدف، بازسازی WooCommerce یا Mirror دائمی داده‌ها نیست.

در V1 فعلی **فقط `WooGit Main Plugin` و Backend آن توسعه داده می‌شوند.** `WooGit Gateway Plugin` فعلاً ساخته، refactor یا migrate نمی‌شود و Backend نباید منتظر آن بماند.

مدل فعلی V1:

```text
Android App
   ↓
WooGit Backend
   │
   └── WooGit Main Plugin
          │
          ↓
   Customer WordPress / WooCommerce
```

`WooGit Gateway Plugin` یک کامپوننت آینده و مستقل است و در مسیر اجباری V1 قرار ندارد:

```text
Customer WordPress / WooCommerce
   └── (Future) WooGit Gateway Plugin
```

Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است.

## ۲. تفکیک دو WooGit Plugin

### WooGit Main Plugin — سایت اصلی WooGit

این همان Plugin مورد استفاده در پروژه فعلی است و روی WordPress اصلی WooGit نصب می‌شود.

```text
WooGit Main Website / WordPress
        └── WooGit Main Plugin
```

مسئولیت‌های آن شامل Backend API، Authentication/Session، Account، Trial، Subscription، Entitlement، Site Identity، Version Gate، Security، Idempotency، Admin و Lightweight Proxy/controlled integration است.

**هرجا در اسناد فعلی پروژه منظور Backend Plugin است، منظور `WooGit Main Plugin` است مگر اینکه صراحتاً نام دیگری ذکر شود.**

### WooGit Gateway Plugin — سایت مشتری

این پلاگین کاملاً مستقل است و برای نصب روی WordPress/WooCommerce مشتری در نظر گرفته می‌شود.

```text
Customer WordPress/WooCommerce
        └── WooGit Gateway Plugin
```

این کامپوننت در V1 فعلی **خارج از scope توسعه** است. هیچ پیاده‌سازی، refactor یا migration مربوط به آن در این repository انجام نمی‌شود.

**نکته مهم:** عبارت `Lightweight Proxy` یا `Gateway/Proxy` در Backend به معنی `WooGit Gateway Plugin` نیست؛ منظور integration/proxy logic داخل `WooGit Main Plugin` است.

## ۳. وضعیت فعلی Client

اپ فعلی مستقیماً با Customer Site کار می‌کند و همین چهار Credential را دارد:

- WordPress Username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

در وضعیت فعلی، Backend Session وجود ندارد؛ اضافه شدن Backend باید با حداقل تغییر در Client انجام شود.

## ۴. توپولوژی هدف V1

```text
┌──────────────────────┐
│     WooGit Android   │
│    Existing Client   │
└──────────┬───────────┘
           │ HTTPS
           │ WooGit Session
           │ + Customer Credentials
           ▼
┌────────────────────────────┐
│       WooGit Backend       │
│    WooGit Main Plugin      │
│                            │
│ Authorization / Account    │
│ Subscription / Entitlement │
│ Site Ownership             │
│ Version / Security         │
│ Lightweight Proxy          │
└────────────┬───────────────┘
             │
             │ Customer credentials / controlled integration
             ▼
┌────────────────────────────┐
│ Customer WordPress         │
│ + WooCommerce              │
└────────────────────────────┘
```

مستقل از این مسیر، همان سایت اصلی WooGit محل اجرای Main Plugin است:

```text
WooGit Main Website / WordPress
        └── WooGit Main Plugin
```

و Gateway Plugin مشتری یک کامپوننت جدا و آینده است:

```text
Customer WordPress/WooCommerce
        └── (Future) WooGit Gateway Plugin
```

## ۵. دو نوع Credential

در درخواست عادی دو دسته اعتبار هم‌زمان وجود دارد:

```text
WooGit Session
    → احراز و مجوز مصرف‌کننده در Backend

WP Username
+ WP Application Password
+ WC Consumer Key
+ WC Consumer Secret
    → احراز Backend نزد Customer Site
```

Customer Credentialها ممکن است در Client موجود باشند؛ این موضوع با وضعیت فعلی اپ سازگار است و در V1 برای کاهش تغییرات Client پذیرفته شده است.

Backend نباید این Credentialها را Log کند، به Account/Site دیگری افشا کند یا بی‌دلیل در Response برگرداند.

## ۶. جریان درخواست عادی

```text
App
 ↓
WooGit Session + site_id + Customer Credentials
 ↓
WooGit Main Plugin / Backend
 ├─ Session validation
 ├─ Account active / not closed
 ├─ Trial / Subscription valid
 ├─ Entitlement
 ├─ Site ownership
 ├─ Version / Rate Limit / Security
 └─ Idempotency where required
 ↓
Lightweight Proxy / controlled integration
 ↓
Customer WordPress/WooCommerce
 ↓
Same response / minimum transformation
 ↓
App
```

وجود `WooGit Gateway Plugin` در آینده نباید به معنی ساخت یک Proxy عمومی یا URL دلخواه باشد. مقصد Customer Site باید از Site Identity و قرارداد کنترل‌شده تعیین شود.

Backend نباید برای هر درخواست داده‌های WooCommerce را Mirror، بازسازی یا بی‌دلیل تبدیل کند.

## ۷. Bootstrap / Verification اولیه

در اولین اتصال ممکن است هنوز WooGit Session کامل وجود نداشته باشد. بنابراین Verification اولیه یک Flow جداست:

```text
Customer Credentials
 ↓
WordPress reachability + authentication
 ↓
WooCommerce verification
 ↓
Site Identity
 ↓
Account / Trial lifecycle
 ↓
WooGit Session
 ↓
Normal requests
```

در صورت شکست Verification، اتصال موفق یا Account/Trial موفق نباید ثبت/اعلام شود.

## ۸. Site Identity و مقصد Customer

Client می‌تواند `site_id` را در قرارداد ارسال کند، اما Backend باید مالکیت آن را خودش احراز کند.

Store ID محلی فعلی App که از Domain Hash ساخته می‌شود، نباید مستقیماً Backend `site_id` فرض شود.

مقصد Customer باید از Site Identity ثبت‌شده resolve شود:

```text
site_id
 ↓
registered customer origin
 ↓
WordPress / WooCommerce REST
```

URL دلخواه Client نباید به Proxy عمومی تبدیل شود:

```text
/proxy?url=https://anything.com   ❌
```

## ۹. Subscription و Account Check

هر Request عادی پیش از Forward باید حداقل این موارد را بررسی کند:

1. WooGit Session معتبر باشد.
2. Account بسته، حذف‌شده یا غیرفعال نباشد.
3. Trial/Subscription و زمان دسترسی منقضی نشده باشد.
4. Account برای Site مجوز داشته باشد.
5. Entitlement عملیات را اجازه دهد.
6. Version/Security/Rate Limit برقرار باشد.

در صورت شکست هر مورد، Request نباید به Customer Site ارسال شود.

## ۱۰. عدم Mirror

Products، Orders، Customers، Categories، Variations و Media همچنان در Customer Site منبع اصلی خود را دارند.

Backend فقط در حد داده عملیاتی موردنیاز خودش state نگه می‌دارد و نباید یک دیتابیس دوم WooCommerce بسازد.

## ۱۱. عملیات و Media

API بیرونی می‌تواند برای امنیت و قرارداد پایدار مسیرهای شناخته‌شده/Typed داشته باشد؛ اما پیاده‌سازی داخلی در `WooGit Main Plugin` Lightweight Proxy است.

برای مثال:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

وجود segment یا نام `gateway` در مسیر API به معنی `WooGit Gateway Plugin` نیست؛ این فقط نام integration surface در Backend است. `WooGit Gateway Plugin` مشتری یک کامپوننت مستقل و خارج از scope فعلی است.

Media نیز تا حد امکان مستقیماً در Customer WordPress نگهداری می‌شود.

## ۱۲. Idempotency و Timeout-after-success

Proxy سبک بودن، نیاز به Idempotency را حذف نمی‌کند.

```text
App → CREATE
Backend → Customer Site
Customer → SUCCESS
Response lost / timeout
App → retry with same operation identity
Backend → previous result / reconciliation
```

همه CREATE mutationهای موردنیاز باید به‌صورت idempotent مدیریت شوند تا Timeout به ایجاد منبع تکراری منجر نشود.

## ۱۳. Currency

Backend نباید واحد پول را فرض یا بازنویسی کند. Currency و context مالی موردنیاز Client باید از Customer WooCommerce عبور داده شود.

## ۱۴. اصل کلی V1

```text
WooGit Session
    = Backend authentication/authorization

Customer Credentials
    = Customer Site authentication

WooGit Backend
    = WordPress + WooGit Main Plugin
      + Account + Subscription + Entitlement
      + Site ownership + Security
      + Lightweight Proxy/controlled integration

WooGit Main Plugin
    = پلاگین Backend روی سایت اصلی WooGit

WooGit Gateway Plugin
    = پلاگین مستقل روی سایت مشتری؛ فعلاً خارج از scope توسعه

Customer WordPress/WooCommerce
    = Source of truth for store data
```
