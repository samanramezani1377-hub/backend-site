# قرارداد API تم WooGit

> وضعیت: V1 — قرارداد یکپارچه پیاده‌سازی
>
> این سند قرارداد ارتباط `theme/woogit/` با API عمومی WooGit است. Theme فقط مصرف‌کننده API عمومی است و نباید به کلاس‌ها، سرویس‌ها یا فایل‌های داخلی Backend وابسته شود.

## ۱. اصل مرجعیت و مرزبندی

Account، Site، احراز هویت، Session، مالکیت، Subscription، Entitlement، Billing و Authorization در Backend تعیین می‌شوند. Theme فقط Presentation و تعامل کاربر را ارائه می‌کند.

Theme نسخه وب App نیست و نباید عملیات Products، Orders، Sync، Conflicts، Inventory، Media یا Store Dashboard عملیاتی را پیاده‌سازی کند.

## ۲. آدرس و نسخه API

```text
/wp-json/woogit/v1/
```

API version از Client version جداست. Theme نباید برای مصرف endpointهای App، هدر یا payload جعلی ارسال کند.

برای تشخیص Client در قرارداد نهایی، الگوی پیشنهادی:

```text
X-WooGit-Client: web
X-WooGit-Client-Version: 1.0.0
```

`X-WooGit-App-Version` مخصوص قرارداد App است و Theme نباید آن را جعل کند. Backend می‌تواند حداقل نسخه پشتیبانی‌شده App و Web را مستقل مدیریت کند.

## ۳. نشست‌ها

```text
Android App → X-WooGit-Session
Theme       → X-WooGit-Web-Session
```

این Sessionها قابل جایگزینی نیستند. Web Session منقضی‌شده قابل revive محلی نیست؛ Login مجدد باید Session جدید بسازد و Backend دوباره Account + Site Ownership + Entitlement لازم را بررسی کند.

## ۴. قراردادهای Web

مسیرهای فعلی وب:

```text
GET  /account/requirements
POST /account/setup-web-credentials
POST /account/web-bootstrap
POST /web/login
POST /web/logout
GET  /web/me
POST /web/account/contact-email
POST /web/account/password
POST /web/password-recovery/start
POST /web/password-recovery/reset
GET  /web/billing/history
```

### Web-first Bootstrap — مورد لازم V1

برای ثبت‌نام مستقیم از Theme، قرارداد مستقل زیر باید در Backend اضافه شود:

```text
POST /account/web-bootstrap
```

ورودی مفهومی:

```json
{
  "site_url": "https://example.com",
  "wp_username": "...",
  "wp_application_password": "...",
  "consumer_key": "...",
  "consumer_secret": "...",
  "web_password": "...",
  "web_password_confirmation": "..."
}
```

جریان authoritative:

```text
Validate input
  ↓
Rate Limit
  ↓
Verify real WooCommerce site
  ↓
Resolve/Create Account
  ↓
Resolve/Create Site
  ↓
Verify Site ↔ Account ownership
  ↓
Create Web Credential
  ↓
Issue Web Session
```

این endpoint باید mutation محسوب شود و `Idempotency-Key` داشته باشد. Credentialهای WooCommerce/WordPress فقط request-scoped هستند و نباید در DB، options، session پایدار، cookie، browser storage، log، telemetry، audit، cache یا HTML/JS نگهداری شوند.

`/sites/verify` همچنان می‌تواند قرارداد App/bootstrap باشد؛ Theme نباید بدون قرارداد صریح، آن endpoint را با تغییر header/payload به‌عنوان Web API مصرف کند.

### Password Recovery / Reset — مورد لازم V1

Forgot Password از مسیر **Site URL + اثبات مجدد کنترل فروشگاه** انجام می‌شود و به Contact Email وابسته نیست. Contact Email فقط برای ارتباط با مشتری است.

```text
POST /web/password-recovery/start
```

ورودی مفهومی:

```json
{
  "site_url": "https://example.com",
  "wp_username": "...",
  "wp_application_password": "...",
  "consumer_key": "...",
  "consumer_secret": "..."
}
```

جریان authoritative:

```text
Validate input
  ↓
Rate Limit
  ↓
Verify real WooCommerce site using supplied credentials
  ↓
Resolve Account + Site
  ↓
Verify Site ↔ Account ownership
  ↓
Issue short-lived, single-use reset authorization
```

Reset authorization باید کوتاه‌عمر، single-use، محدود به همان Account/Site و غیرقابل استفاده به‌عنوان Web Session باشد. پاسخ نباید وجود یا عدم وجود Account/Site را افشا کند.

سپس:

```text
POST /web/password-recovery/reset
```

ورودی مفهومی:

```json
{
  "reset_token": "...",
  "new_web_password": "...",
  "new_web_password_confirmation": "..."
}
```

جریان:

```text
Validate reset authorization
  ↓
Validate new password policy
  ↓
Set new Web Password
  ↓
Revoke all existing Web Sessions
  ↓
Require Login with Site URL + new Web Password
```

Credentialهای WordPress/WooCommerce فقط request-scoped هستند و هرگز نباید در DB، options، session پایدار، cookie، browser storage، log، telemetry، audit، cache یا HTML/JS نگهداری شوند. Reset token نیز نباید در URL قرار گیرد یا به‌عنوان Session پذیرفته شود.

Recovery و Reset هر دو باید rate-limited باشند و retry/duplicate operation طبق state و idempotency contract کنترل شود.

## ۵. Billing برای Web و App

مسیرهای Billing:

```text
GET  /billing/plans
GET  /billing/status
POST /billing/checkout
POST /billing/activate-session
```

`plans` عمومی است. `status` و `checkout` باید در قرارداد نهایی امکان احراز هویت با Web Session و App Session را داشته باشند.

Backend باید App Session و Web Session را به یک مفهوم مشترک authorization context تبدیل کند، برای نمونه:

```text
account_id
site_id
client_type
session_id
```

Business Logic و BillingService مشترک بمانند؛ فقط لایه احراز هویت/Context تفاوت داشته باشد.

`/billing/activate-session` **App-only** است؛ چون وظیفه آن صدور Operational App Session است. Theme نباید این endpoint را مصرف کند.

## ۶. Checkout و Idempotency

Theme برای Checkout از قرارداد Backend استفاده می‌کند و باید `Idempotency-Key` ارسال کند. Retry همان عملیات باید همان کلید را حفظ کند.

Backend باید سناریوی timeout-after-success را پوشش دهد: اگر عملیات در سرور موفق شد ولی پاسخ به Client نرسید، retry با همان کلید باید نتیجه همان عملیات را بازیابی کند و خرید دوم نسازد.

## ۷. Payment Return

بازگشت از Gateway اثبات پرداخت نیست:

```text
Theme → Checkout
      ↓
Gateway
      ↓
Backend callback/webhook
      ↓
Order + BillingService
      ↓
Entitlement
      ↓
Theme /payment/result
      ↓
GET /billing/status با Web Session
      ↓
نمایش وضعیت نهایی
```

Query parameter مانند `status=success` نباید trusted باشد. Enum دقیق وضعیت‌های Billing/Payment باید توسط Backend به‌صورت canonical منتشر شود؛ برای نمونه `pending`, `paid`, `failed`, `expired`, `cancelled`.

## ۸. خطاها

قرارداد canonical خطا در `docs/API_ERROR_CODES.md` است. Theme بر اساس HTTP status + `code` رفتار می‌کند، نه متن پیام.

کدهای کلیدی:

```text
validation_error
invalid_web_credentials
invalid_web_session
invalid_session
account_inactive
site_not_owned
not_entitled
idempotency_conflict
operation_pending
operation_unknown
rate_limited
server_error
```

برای `429` در صورت وجود `Retry-After`/`retry_after`، Theme باید رفتار کنترل‌شده داشته باشد و retry تهاجمی نکند.

## ۹. نگهداری Web Session

Token نباید در URL قرار گیرد. گزینه ترجیحی Cookie امن `HttpOnly` + `Secure` + `SameSite` مناسب یا BFF/Bridge امن است. اگر Backend فقط Header را پشتیبانی کند، قرار دادن خام Token در `localStorage` بدون تصمیم امنیتی صریح مجاز نیست.

## ۱۰. ممنوعیت‌ها

Theme نباید:

- مستقیماً به WooCommerce مشتری وصل شود؛
- Credential سایت مشتری را دائمی ذخیره کند؛
- App Session را برای Web جعل یا reuse کند؛
- Entitlement را خودش محاسبه کند؛
- موفقیت پرداخت را از URL نتیجه‌گیری کند؛
- Session منقضی‌شده را locally revive کند؛
- به کلاس یا سرویس داخلی Plugin وابسته شود؛
- `account_id`/`site_id` ارسالی کاربر را مرجع Authorization بداند.
