# محدوده محصول و مدل تجاری WooGit Backend

> وضعیت: V1 — Locked

## ۱. محصول V1

هدف V1 ایجاد مسیر محافظت‌شده و سبک بین Android Client موجود و Customer WordPress/WooCommerce است.

Backend مسئول WooGit Session، Account، Site Identity، Trial/Subscription، Entitlement، Version Gate، Security، Controlled Forwarding، Idempotency، Timeout-after-success و Audit/Operations است.

Backend برای عملیات عادی Mirror دائمی WooCommerce ایجاد نمی‌کند.

## ۲. Client و Credential

Client فعلی این ورودی‌ها را دارد:

- Store URL / protocol؛
- WooCommerce Consumer Key؛
- WooCommerce Consumer Secret؛
- WordPress Username؛
- WordPress Application Password.

چهار Credential مقصد در صورت نیاز همراه Request به Backend می‌آیند. Backend آن‌ها را فقط برای همان Request مصرف می‌کند و در V1 ذخیره نمی‌کند.

## ۳. دو نوع اعتبار

```text
WooGit Session
    → احراز و مجوز Client در Backend

Customer Credentials
    → احراز نزد Customer WordPress/WooCommerce
```

Access Token + Refresh Token جزو معماری V1 نیست.

## ۴. Onboarding / Verification

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
```

Verification باید read-only باشد و قبل از هر عملیات تجاری انجام شود.

## ۵. درخواست عادی

```text
Android
  ↓
WooGit Session + site_id
+ Customer Credentials (request-scoped)
+ operation/path/query/body
  ↓
Authorization / Site Isolation / Entitlement
  ↓
Controlled Forwarding
  ↓
Customer WordPress/WooCommerce
```

Account بسته/غیرفعال یا Subscription/Trial منقضی نباید outbound request بفرستد.

## ۶. Controlled Proxy

Backend Proxy عمومی URL دلخواه نیست. مقصد از Site Identity ثبت‌شده resolve می‌شود و فقط operation/pathهای مجاز قابل Forward هستند.

## ۷. Billing

Trial رایگان ۱۵ روزه و Subscription/Entitlement در سمت Backend کنترل می‌شوند. روی WordPress اصلی WooGit، **WooCommerce موتور فروش و سفارش/پرداخت است و Milo Subscriptions موتور Subscription و چرخه Renewal، Trial، Cancellation و وضعیت Subscription است**. Authorization نهایی با WooGit Backend است.

Milo Subscriptions جایگزین WooCommerce Subscriptions در معماری V1 است و Backend باید از lifecycle و hookهای Milo برای همگام‌سازی Subscription/Entitlement استفاده کند.

## ۸. Currency و داده فروشگاه

Customer WordPress/WooCommerce منبع حقیقت Products، Orders، Customers، Categories، Variations و Media است. Backend Currency را hard-code یا بی‌دلیل تبدیل نمی‌کند.

## ۹. Idempotency

CREATE mutationهای موردنیاز باید operation identity پایدار داشته باشند تا response-loss و retry باعث ایجاد resource تکراری نشود.

## ۱۰. خارج از V1

- بازسازی Android App؛
- تغییر UI/UX اپ برای Backend؛
- پیاده‌سازی Customer `WooGit Gateway Plugin`؛
- الزام PostgreSQL/Redis/Queue مستقل؛
- Mirror دائمی WooCommerce؛
- Customer Credential Vault یا هر persistent credential storage.
