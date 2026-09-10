# WooGit Theme UX — Flows

> قرارداد جریان‌های اصلی کاربر در Theme. Source of Truth هر داده طبق `docs/THEME_DATA_OWNERSHIP.md` تعیین می‌شود.

## Payment and Billing boundary

Billing یک صفحه ترکیبی است. وضعیت Subscription و Entitlement از WooGit Backend می‌آید؛ اما Payment Method، Payment History و Order/payment details مربوط به خریدهای WooGit از WooCommerce خود `woogit.ir` می‌آیند.

```text
Theme Portal
  ├─ Backend API → Account / Web Session / Ownership / Subscription / Entitlement
  │
  └─ WooCommerce adapter → WooCommerce خود woogit.ir
                            → Orders / Payments / Payment Method
```

WooCommerce مشتری فقط برای operational store integration است و Theme نباید مستقیماً به آن وصل شود.

## Login

Login → Site URL + Web Password → POST /web/login → Web Session → Portal

## Register / Web Bootstrap

```text
Register
  → validation of Site URL + WordPress + WooCommerce credentials
  → POST /account/web-bootstrap
  → site verification
  → Account/Site resolution
  → Web Session
  → if web_password_configured=false: Account Security / Set Web Password
  → otherwise: Portal
```

Web Password بخشی از Register و Web Bootstrap نیست. رمز فقط در endpoint مستقل `POST /account/setup-web-credentials` برای اولین تنظیم ایجاد می‌شود؛ این endpoint در صورت وجود رمز قبلی نباید آن را overwrite کند.

## Subscription

Backend authoritative status را نمایش می‌دهد. Theme نباید Premium/Active یا Entitlement را محلی جعل کند.

## Checkout

```text
Pricing / Subscription
  → Backend eligibility/orchestration
  → POST /billing/checkout + Idempotency-Key
  → WooCommerce خود woogit.ir / Gateway
  → Payment processing
  → Backend entitlement update
```

Timeout-after-success شکست قطعی نیست؛ همان logical operation باید با همان Idempotency-Key پیگیری شود.

## Payment Return

Query مانند `success=1` proof نیست. Theme باید payment/order state و Subscription/Entitlement state را از منابع authoritative بررسی کند.

## Payments / History

```text
Payments
  → WooCommerce adapter
  → WooCommerce خود woogit.ir
  → Order/payment records
  → list / Empty / Error
```

Backend در صورت نیاز می‌تواند projection امن ارائه کند، اما منبع حقیقت همچنان WooCommerce خود `woogit.ir` است.

## Payment Method

```text
Billing / Payment Method
  → WooCommerce adapter
  → relevant WooGit Order/payment state
  → non-sensitive presentation
```

`payment_method` و `payment_method_title` داده‌های Order/gateway هستند. `expires_at` مربوط به Entitlement/Subscription نباید expiration کارت فرض شود.

## Connected Site

اطلاعات Account/Site از Backend می‌آید. اتصال مستقیم به WooCommerce مشتری ممنوع است؛ این ممنوعیت شامل WooCommerce خود `woogit.ir` نمی‌شود.

## Password Change / Logout

پس از تغییر موفق رمز، Backend Web Sessionهای قبلی را revoke می‌کند و Theme باید state موقت را پاک و Login مجدد را درخواست کند. Logout با `POST /web/logout` انجام می‌شود.
