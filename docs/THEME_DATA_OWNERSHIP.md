# WooGit Theme — Data Ownership Contract

> V1 — مرجع تشخیص Source of Truth برای داده‌های Theme.

## سه سیستم متفاوت

```text
A) WooCommerce خود woogit.ir
   فروشگاه رسمی WooGit

B) WooGit Backend / Main Plugin
   SaaS authority

C) WooCommerce فروشگاه مشتری
   سایت متصل‌شده مشتری
```

این سه سیستم نباید در مستندات یا implementation به‌جای یکدیگر استفاده شوند.

## Source of Truth

| Domain | Source of Truth |
|---|---|
| WooGit products/plans sold on woogit.ir | WooCommerce خود woogit.ir |
| WooGit orders | WooCommerce خود woogit.ir |
| WooGit payment gateway/method | WooCommerce خود woogit.ir |
| WooGit payment status/date/transaction data | WooCommerce خود woogit.ir |
| WooGit payment history | WooCommerce خود woogit.ir |
| WooGit checkout/order state | WooCommerce خود woogit.ir |
| Account | WooGit Backend |
| Web authentication / Web Session | WooGit Backend |
| Site ownership | WooGit Backend |
| Entitlement | WooGit Backend |
| SaaS subscription state | WooGit Backend |
| Authorization/capabilities | WooGit Backend |
| Customer-store Products/Orders/Sync/Inventory operations | Android App + customer-store integration, not Theme |

## Theme access

Theme روی `woogit.ir` می‌تواند داده‌های WooCommerce **همان سایت** را برای صفحات رسمی و Portal مصرف کند.

این به معنی اتصال Theme به WooCommerce مشتری نیست.

```text
Allowed:
Theme → WooCommerce adapter → WooCommerce خود woogit.ir

Forbidden:
Theme → Customer WooCommerce
Theme → Backend Database
Theme → Plugin internal service/class
```

Template و Template Part نباید مستقیماً query بزنند؛ داده باید از adapter/orchestrator مناسب Theme به View Data تبدیل شود.

## Payment Method

Payment Method در Portal باید از Order/payment state معتبر WooCommerce خود `woogit.ir` استخراج شود. اگر چند Order وجود دارد، انتخاب Order مرجع باید با business rule صریح و deterministic انجام شود؛ «آخرین Order ایجادشده» بدون تعریف rule کافی نیست.

`payment_method` و `payment_method_title` داده‌های gateway/order هستند و نباید با Entitlement یا Subscription expiry یکی فرض شوند. اطلاعات حساس مانند شماره کامل کارت، CVV، gateway secrets و credentialها هرگز نباید به View Data منتقل شوند.

## Payment History

Payment History خریدهای WooGit باید بر اساس Order/payment records موجود در WooCommerce خود `woogit.ir` نمایش داده شود.

Backend می‌تواند در صورت نیاز projection امن ارائه کند، اما projection نباید یک Source of Truth دوم بسازد؛ منبع همچنان WooCommerce خود `woogit.ir` است.

## Billing

Billing صفحه‌ای ترکیبی است:

```text
Current Plan / Entitlement / SaaS state
    → Backend

Payment Method / Payment History / WooGit Order payment details
    → WooCommerce خود woogit.ir
```

Checkout ممکن است از Backend orchestration استفاده کند، اما Order و Payment state نهایی در WooCommerce خود `woogit.ir` باقی می‌ماند و Entitlement پس از payment توسط Backend مدیریت می‌شود.

## Terminology

- `WooCommerce خود woogit.ir` = فروشگاه رسمی WooGit
- `Customer WooCommerce` = فروشگاه متصل‌شده مشتری
- `WooGit Backend` = SaaS authority
- `WooGit Theme` = presentation/web portal

هرجا «WooCommerce» بدون تعیین مالکیت آمده و ممکن است دو معنا داشته باشد، متن باید اصلاح شود.
