# معماری نهایی Backend ووگیت

> وضعیت: **V1 Architecture — Locked**
>
> این repository فقط Backend اپ Android موجود WooGit را می‌سازد. اپ در repository مستقل قرار دارد و در این سند به‌عنوان Client خارجی در نظر گرفته می‌شود.
>
> **تفکیک مهم:** `WooGit Gateway Plugin` و `WooGit Main Plugin` دو محصول/کامپوننت کاملاً جدا هستند و نباید با یکدیگر قاطی شوند.
>
> - **WooGit Gateway Plugin:** پلاگینی که روی WordPress/WooCommerce سایت مشتری نصب می‌شود و در آینده بخشی از مسیر اتصال Backend به سایت مشتری خواهد بود. توسعه آن در وضعیت فعلی این پروژه انجام نمی‌شود.
> - **WooGit Main Plugin:** پلاگین مربوط به خود سایت اصلی WooGit/WordPress ووگیت است و از Gateway Plugin مشتری مستقل است.

## ۱. تصمیم اصلی

Backend V1 باید سبک باشد و بین اپ موجود و Customer WordPress/WooCommerce قرار بگیرد. هدف، بازسازی WooCommerce یا Mirror دائمی داده‌ها نیست.

در وضعیت فعلی **تمرکز توسعه روی Backend است و روی `WooGit Gateway Plugin` کار نمی‌کنیم.** Gateway فقط به‌عنوان یک کامپوننت مستقل و آینده در قرارداد/معماری شناخته می‌شود تا بعداً طراحی و پیاده‌سازی آن جداگانه انجام شود.

مدل مفهومی مسیر نهایی:

```text
Android App
   ↓
WooGit Backend
   ↓
[در آینده، در صورت نیاز] WooGit Gateway Plugin روی سایت مشتری
   ↓
Customer WordPress / WooCommerce
```

در V1 فعلی، Backend نباید منتظر پیاده‌سازی Gateway Plugin بماند و نباید کد Gateway را در این repository بازسازی کند.

Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است.

## ۲. تفکیک دو WooGit Plugin

### WooGit Gateway Plugin — سایت مشتری

این پلاگین برای نصب روی WordPress/WooCommerce مشتری طراحی می‌شود و با پلاگین اصلی سایت WooGit یکی نیست.

```text
Customer WordPress/WooCommerce
        └── WooGit Gateway Plugin
```

این کامپوننت در وضعیت فعلی **خارج از محدوده اجرای پروژه** است. هیچ پیاده‌سازی، refactor یا migration مربوط به Gateway Plugin در این مرحله انجام نمی‌شود.

### WooGit Main Plugin — سایت اصلی WooGit

این پلاگین مربوط به WordPress سایت اصلی WooGit است و از Gateway Plugin مشتری مستقل است.

```text
WooGit Main Website / WordPress
        └── WooGit Main Plugin
```

هر اشاره به `WooGit Plugin` در اسناد باید با توجه به این تفکیک مشخص کند منظور کدام‌یک است.

## ۳. وضعیت فعلی Client

اپ فعلی مستقیماً با Customer Site کار می‌کند و همین چهار Credential را دارد:

- WordPress Username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

در وضعیت فعلی، Backend Session وجود ندارد؛ اضافه شدن Backend باید با حداقل تغییر در Client انجام شود.

## ۴. توپولوژی هدف

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
│ + (Future) Gateway Plugin  │
└────────────────────────────┘

مستقل از این مسیر:

┌────────────────────────────┐
│ WooGit Main Website        │
│ + WooGit Main Plugin       │
└────────────────────────────┘
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
Backend
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

وجود Gateway Plugin در آینده نباید به معنی ساخت یک Proxy عمومی یا URL دلخواه باشد. مقصد Customer Site باید از Site Identity و قرارداد کنترل‌شده تعیین شود.

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

API بیرونی می‌تواند برای امنیت و قرارداد پایدار مسیرهای شناخته‌شده/Typed داشته باشد؛ اما پیاده‌سازی داخلی Lightweight Proxy است.

برای مثال:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

این مسیرهای API به معنی پیاده‌سازی `WooGit Gateway Plugin` در این مرحله نیستند؛ آن پلاگین یک کامپوننت مستقل است که در فاز جداگانه طراحی خواهد شد.

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
    = Account + Subscription + Entitlement + Site ownership
      + Security + Lightweight Proxy

WooGit Gateway Plugin
    = کامپوننت مستقل روی سایت مشتری؛ فعلاً خارج از scope توسعه

WooGit Main Plugin
    = پلاگین مستقل سایت اصلی WooGit؛ با Gateway Plugin یکی نیست

Customer WordPress/WooCommerce
    = Source of truth for store data
```
