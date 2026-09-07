# معماری احراز هویت و نشست وب تم WooGit

> وضعیت: V1 — قرارداد یکپارچه

## ۱. دامنه

این سند فقط احراز هویت وب‌سایت و Customer Portal را تعریف می‌کند. احراز هویت عملیاتی App مستقل است.

## ۲. دو نوع نشست

```text
Android App → X-WooGit-Session
Web Theme   → X-WooGit-Web-Session
```

این دو نشست قابل جایگزینی نیستند. Theme نباید App Session را جعل یا برای Web reuse کند.

## ۳. Login

ورود V1:

```text
Site URL
Password
```

Backend Site و Account مرتبط را resolve و اعتبار Web Password را بررسی می‌کند و Web Session صادر می‌کند. Theme فقط نتیجه Backend را مصرف می‌کند.

## ۴. Web-first Registration

برای ثبت‌نام مستقیم از Theme، قرارداد مستقل `POST /account/web-bootstrap` باید توسط Backend ارائه شود.

ورودی مفهومی:

```text
Store URL
WordPress Username
WordPress Application Password
WooCommerce Consumer Key
WooCommerce Consumer Secret
Web Password + Confirmation
```

جریان:

```text
Validate
  ↓
Rate Limit
  ↓
Real Site Verification
  ↓
Resolve/Create Account + Site
  ↓
Ownership Check
  ↓
Create Web Password
  ↓
Issue Web Session
```

این عملیات mutation است و باید `Idempotency-Key` داشته باشد.

Credentialهای WordPress/WooCommerce فقط request-scoped هستند و نباید در DB، Cookie، Browser Storage، Session پایدار، Log، Telemetry، Audit، Cache یا HTML/JS نگهداری شوند.

`/sites/verify` قرارداد App/bootstrap باقی می‌ماند مگر اینکه Backend صراحتاً آن را برای Web نیز منتشر کند.

## ۵. شکاف قرارداد فعلی

`POST /account/setup-web-credentials` در قرارداد فعلی به App Session معتبر وابسته است. این endpoint نباید توسط Theme با هدر جعلی مصرف شود. Web-first bootstrap باید این وابستگی را به‌صورت رسمی حل کند.

## ۶. نگهداری Web Session

Token نشست وب نباید در URL قرار گیرد. گزینه ترجیحی Cookie امن `HttpOnly`، `Secure` و `SameSite` مناسب یا BFF/Bridge امن است.

اگر Backend فقط Header `X-WooGit-Web-Session` را پشتیبانی کند، ذخیره خام Token در `localStorage` بدون تصمیم امنیتی صریح مجاز نیست.

## ۷. انقضای Session

```text
401 / expired
      ↓
Clear temporary state
      ↓
Login
      ↓
New Web Session
      ↓
Backend re-checks Account + Site Ownership + Entitlement
```

Session منقضی‌شده هرگز locally revive نمی‌شود.

## ۸. Logout و Password Change

Logout باید revoke سمت Backend را انجام دهد و Theme وضعیت موقت محلی را پاک کند.

پس از تغییر موفق Password، Backend همه Web Sessionهای قبلی را revoke می‌کند؛ Theme باید کاربر را به Login مجدد هدایت کند.

## ۹. Authorization Context

Backend مرجع Authorization است. Web Session باید Account و Site را از session/context معتبر resolve کند و هرگز `account_id` یا `site_id` ارسالی کاربر را مرجع دسترسی قرار ندهد.

## ۱۰. Rate Limit و خطا

Login، Bootstrap و عملیات حساس باید `429` را پشتیبانی کنند. Theme نباید retry تهاجمی انجام دهد.

کد خطا باید طبق `docs/API_ERROR_CODES.md` canonical باشد و Theme بر اساس HTTP status + `code` رفتار کند. پیام Login نباید اطلاعات حساس درباره وجود Account/Site را افشا کند.
