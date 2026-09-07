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

## ۵. Password Recovery / Reset

Forgot Password در V1 یک Flow واقعی است، اما **Contact Email روش احراز هویت یا بازیابی حساب نیست**. Contact Email فقط برای ارتباط با مشتری استفاده می‌شود.

بازیابی بر پایه اثبات مجدد کنترل فروشگاه انجام می‌شود:

```text
Forgot Password
      ↓
Enter Site URL
      ↓
Enter WooCommerce / WordPress verification credentials
      ↓
Backend rate limit + validate input
      ↓
Backend verifies the real Site using supplied credentials
      ↓
Resolve Account + Site + ownership
      ↓
Issue short-lived, single-use Password Reset authorization
      ↓
Reset Password
      ↓
Set new Web Password + confirmation
      ↓
Revoke existing Web Sessions
      ↓
Login with Site URL + new Password
```

### 5.1 Verification Rules

- Site URL باید ورودی اصلی Flow باشد.
- Credentialهای لازم برای اثبات کنترل فروشگاه فقط در همان request ارسال می‌شوند.
- Backend باید واقعاً اتصال و دسترسی فروشگاه را با Credentialهای ارائه‌شده verify کند؛ صرفاً معتبر بودن Site URL کافی نیست.
- Credentialهای WordPress/WooCommerce request-scoped هستند و نباید در DB، Cookie، Browser Storage، Session پایدار، Log، Telemetry، Audit، Cache یا HTML/JS ذخیره شوند.
- Backend باید Account و Site را از نتیجه verification و ownership resolve کند؛ `account_id` یا `site_id` ارسالی کاربر authority نیست.
- Reset authorization باید کوتاه‌عمر، single-use و محدود به همان Account/Site باشد و نباید به‌عنوان Web Session قابل استفاده باشد.
- Password جدید باید همان policy مربوط به Web Password را رعایت کند.
- پس از reset موفق، همه Web Sessionهای قبلی revoke می‌شوند و کاربر باید با Site URL + Password جدید وارد شود.

### 5.2 Security / Abuse Controls

- Endpointهای recovery و reset باید rate-limited باشند.
- پاسخ اولیه نباید اطلاعاتی درباره وجود یا عدم وجود Account/Site افشا کند.
- شکست verification نباید امکان دور زدن ownership را ایجاد کند.
- Reset token/authorization هرگز در URL قابل اعتماد یا قابل استفاده به‌عنوان Session نیست.
- Retry و عملیات تکراری باید با state و idempotency مناسب کنترل شوند تا یک reset operation باعث رفتار ناخواسته یا چندباره نشود.
- Theme هیچ‌یک از verification/business rules را خودش اجرا نمی‌کند؛ Backend مرجع نهایی است.

## ۶. شکاف قرارداد فعلی

`POST /account/setup-web-credentials` در قرارداد فعلی به App Session معتبر وابسته است. این endpoint نباید توسط Theme با هدر جعلی مصرف شود. Web-first bootstrap باید این وابستگی را به‌صورت رسمی حل کند.

Password Recovery/Reset نیز نیازمند API رسمی Backend است و تا قبل از تعریف endpoint، payload، authorization semantics و rate-limit contract نباید implementation واقعی در Theme انجام شود.

## ۷. نگهداری Web Session

Token نشست وب نباید در URL قرار گیرد. گزینه ترجیحی Cookie امن `HttpOnly`، `Secure` و `SameSite` مناسب یا BFF/Bridge امن است.

اگر Backend فقط Header `X-WooGit-Web-Session` را پشتیبانی کند، ذخیره خام Token در `localStorage` بدون تصمیم امنیتی صریح مجاز نیست.

## ۸. انقضای Session

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

## ۹. Logout و Password Change

Logout باید revoke سمت Backend را انجام دهد و Theme وضعیت موقت محلی را پاک کند.

پس از تغییر موفق Password، Backend همه Web Sessionهای قبلی را revoke می‌کند؛ Theme باید کاربر را به Login مجدد هدایت کند.

## ۱۰. Authorization Context

Backend مرجع Authorization است. Web Session باید Account و Site را از session/context معتبر resolve کند و هرگز `account_id` یا `site_id` ارسالی کاربر را مرجع دسترسی قرار ندهد.

## ۱۱. Rate Limit و خطا

Login، Bootstrap، Password Recovery و Reset و عملیات حساس باید `429` را پشتیبانی کنند. Theme نباید retry تهاجمی انجام دهد.

کد خطا باید طبق `docs/API_ERROR_CODES.md` canonical باشد و Theme بر اساس HTTP status + `code` رفتار کند. پیام Login و Recovery نباید اطلاعات حساس درباره وجود Account/Site را افشا کند.
