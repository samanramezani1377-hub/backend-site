# قرارداد API تم WooGit

> V1 — Web/Portal API contract. منبع حقیقت داده‌ها در `docs/THEME_DATA_OWNERSHIP.md` تعریف شده است.

## ۱. مرجعیت

Backend مرجع Account، Web Authentication، Session، Site Ownership، Entitlement، Authorization و SaaS Subscription state است.

**WooCommerce نصب‌شده روی خود `woogit.ir` مرجع داده‌های فروشگاه رسمی WooGit است**؛ از جمله Products/Plans، Orders، Payment Method، Payment History و Order/payment state.

این موضوع با ممنوعیت اتصال Theme به **Customer WooCommerce** تفاوت دارد.

## ۲. API

```text
/wp-json/woogit/v1/
```

Web client:

```text
X-WooGit-Client: web
X-WooGit-Client-Version: 1.0.0
X-WooGit-Web-Session: <web session>
```

Android App Session و Web Session قابل جایگزینی نیستند.

## ۳. Web routes

```text
GET  /account/requirements
POST /account/web-bootstrap
POST /web/login
POST /web/logout
GET  /web/me
POST /web/account/contact-email
POST /web/account/password
POST /web/password-recovery/start
POST /web/password-recovery/reset
```

`/sites/verify` قرارداد App/bootstrap است و Theme نباید با جعل header/payload آن را Web API کند.

Credentialهای WordPress/WooCommerce مشتری فقط request-scoped هستند و نباید persistent شوند.

## ۴. Billing و Checkout

```text
GET  /billing/plans
GET  /billing/status
POST /billing/checkout
POST /billing/activate-session   # App-only
```

`/billing/activate-session` برای Theme نیست.

`billing/status` مرجع SaaS/Subscription/Entitlement است؛ نباید به‌صورت پیش‌فرض به‌عنوان Source of Truth برای Payment Method یا Payment History WooCommerce خود `woogit.ir` تعریف شود.

Checkout می‌تواند از Backend orchestration و eligibility استفاده کند، اما Order/Payment در WooCommerce خود `woogit.ir` ایجاد و نگهداری می‌شود و Entitlement توسط Backend پس از payment مدیریت می‌شود.

## ۵. Payment Method

Payment Method در Portal از Order/payment state معتبر WooCommerce خود `woogit.ir` می‌آید و Theme باید از adapter مناسب استفاده کند؛ Template مستقیماً query نمی‌زند.

داده‌هایی مانند:

```text
payment_method
payment_method_title
payment status
payment date
transaction identifier (در صورت مجاز بودن برای نمایش)
```

از WooCommerce Order/gateway می‌آیند.

`expires_at` اگر برای Entitlement/Subscription باشد، نباید به معنی expiration کارت یا Payment Method نمایش داده شود.

شماره کامل کارت، CVV، gateway secret و credential درگاه هرگز به View Data منتقل نمی‌شوند.

## ۶. Payment History

Payment History خریدهای WooGit بر اساس Order/payment records موجود در WooCommerce خود `woogit.ir` نمایش داده می‌شود.

اگر Backend projection امنی ارائه کند، projection فقط یک adapter/service boundary برای دسترسی است و Source of Truth را تغییر نمی‌دهد.

## ۷. Payment Return

بازگشت از Gateway proof پرداخت نیست.

```text
WooCommerce / Gateway
        ↓
Order/payment state
        ↓
Backend entitlement processing
        ↓
Theme payment-result UI
```

Theme برای Entitlement/Subscription state از Backend استفاده می‌کند و برای Payment/Order detail از WooCommerce خود `woogit.ir`.

Queryهایی مانند `success=1` trusted نیستند.

## ۸. ممنوعیت‌ها

Theme نباید:

- مستقیماً به WooCommerce **فروشگاه مشتری** وصل شود؛
- Credential فروشگاه مشتری را دائمی ذخیره کند؛
- App Session را برای Web جعل یا reuse کند؛
- Entitlement را خودش محاسبه کند؛
- موفقیت پرداخت را از query string نتیجه‌گیری کند؛
- Session منقضی‌شده را locally revive کند؛
- به کلاس یا سرویس داخلی Backend Plugin وابسته شود؛
- Backend Database را مستقیماً بخواند؛
- WooCommerce خود `woogit.ir` را با یک payment/order source ساختگی Backend جایگزین کند؛
- Template یا Template Part را مسئول مستقیم API/Database/WooCommerce query کند.

> برای جلوگیری از ابهام، عبارت «Customer WooCommerce» همیشه به فروشگاه متصل‌شده مشتری اشاره دارد؛ «WooCommerce خود woogit.ir» فروشگاه رسمی WooGit است و دسترسی Theme به آن طبق معماری مصوب مجاز است.
