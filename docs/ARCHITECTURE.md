# معماری نهایی Backend ووگیت

> وضعیت: **V1 Architecture — Locked**
>
> این repository فقط Backend اپ Android موجود WooGit را می‌سازد. اپ در repository مستقل قرار دارد و در این سند به‌عنوان Client خارجی در نظر گرفته می‌شود.

## ۱. تصمیم اصلی

Backend V1 باید سبک باشد و بین اپ موجود و Customer WordPress/WooCommerce قرار بگیرد. هدف، بازسازی WooCommerce یا Mirror دائمی داده‌ها نیست.

مدل انتخاب‌شده:

```text
Android App
   ↓
WordPress REST / WooGit Plugin
   ↓
Authorization + Account Check
   ↓
Lightweight Proxy
   ↓
Customer WordPress/WooCommerce
```

Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است.

## ۲. وضعیت فعلی Client

اپ فعلی مستقیماً با Customer Site کار می‌کند و همین چهار Credential را دارد:

- WordPress Username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

در وضعیت فعلی، Backend Session وجود ندارد؛ اضافه شدن Backend باید با حداقل تغییر در Client انجام شود.

## ۳. توپولوژی هدف

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
│    WordPress + Plugin      │
│                            │
│ Authorization / Account    │
│ Subscription / Entitlement │
│ Site Ownership             │
│ Version / Security         │
│ Lightweight Proxy          │
└────────────┬───────────────┘
             │ HTTPS
             │ Customer Credentials
             ▼
┌────────────────────────────┐
│ Customer WordPress         │
│ + WooCommerce              │
└────────────────────────────┘
```

## ۴. دو نوع Credential

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

## ۵. جریان درخواست عادی

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
Lightweight Proxy
 ↓
Customer WordPress/WooCommerce
 ↓
Same response / minimum transformation
 ↓
App
```

Backend نباید برای هر درخواست داده‌های WooCommerce را Mirror، بازسازی یا بی‌دلیل تبدیل کند.

## ۶. Bootstrap / Verification اولیه

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
Normal Proxy requests
```

در صورت شکست Verification، اتصال موفق یا Account/Trial موفق نباید ثبت/اعلام شود.

## ۷. Site Identity و مقصد Proxy

Client می‌تواند `site_id` را در قرارداد ارسال کند، اما Backend باید مالکیت آن را خودش احراز کند.

Store ID محلی فعلی App که از Domain Hash ساخته می‌شود، نباید مستقیماً Backend `site_id` فرض شود.

مقصد Proxy باید از Site Identity ثبت‌شده resolve شود:

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

## ۸. Subscription و Account Check

هر Request عادی پیش از Forward باید حداقل این موارد را بررسی کند:

1. WooGit Session معتبر باشد.
2. Account بسته، حذف‌شده یا غیرفعال نباشد.
3. Trial/Subscription و زمان دسترسی منقضی نشده باشد.
4. Account برای Site مجوز داشته باشد.
5. Entitlement عملیات را اجازه دهد.
6. Version/Security/Rate Limit برقرار باشد.

در صورت شکست هر مورد، Request نباید به Customer Site ارسال شود.

## ۹. عدم Mirror

Products، Orders، Customers، Categories، Variations و Media همچنان در Customer Site منبع اصلی خود را دارند.

Backend فقط در حد داده عملیاتی موردنیاز خودش state نگه می‌دارد و نباید یک دیتابیس دوم WooCommerce بسازد.

## ۱۰. عملیات و Media

API بیرونی می‌تواند برای امنیت و قرارداد پایدار مسیرهای شناخته‌شده/Typed داشته باشد؛ اما پیاده‌سازی داخلی Lightweight Proxy است.

برای مثال:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

Media نیز تا حد امکان مستقیماً در Customer WordPress نگهداری می‌شود.

## ۱۱. Idempotency و Timeout-after-success

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

## ۱۲. Currency

Backend نباید واحد پول را فرض یا بازنویسی کند. Currency و context مالی موردنیاز Client باید از Customer WooCommerce عبور داده شود.

## ۱۳. اصل کلی V1

```text
WooGit Session
    = Backend authentication/authorization

Customer Credentials
    = Customer Site authentication

Backend
    = Account + Subscription + Entitlement + Site ownership
      + Security + Lightweight Proxy

Customer WordPress/WooCommerce
    = Source of truth for store data
```
