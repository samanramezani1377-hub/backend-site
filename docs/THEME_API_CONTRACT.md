# قرارداد API تم WooGit

> وضعیت: V1 — قرارداد همگام با Backend موجود
>
> این سند قرارداد ارتباط `theme/woogit/` با API عمومی WooGit است. Theme فقط مصرف‌کننده API عمومی است و نباید به کلاس‌ها، سرویس‌ها یا فایل‌های داخلی Backend وابسته شود.
>
> اصل این سند این است که **Theme باید با Backend موجود هماهنگ شود**؛ مستندات Theme نباید برای تغییر قرارداد عملیاتی App یا بازطراحی غیرضروری Backend استفاده شوند.

## ۱. اصل مرجعیت و مرزبندی

Account، Site، احراز هویت، Session، مالکیت، Subscription، Entitlement، Billing و Authorization در Backend تعیین می‌شوند. Theme فقط Presentation و تعامل کاربر را ارائه می‌کند.

Theme نسخه وب App نیست و نباید عملیات Products، Orders، Sync، Conflicts، Inventory، Media یا Store Dashboard عملیاتی را پیاده‌سازی کند.

## ۲. آدرس و نسخه API

```text
/wp-json/woogit/v1/
```

API version از Client version جداست. Theme نباید برای مصرف endpointهای App، هدر یا payload جعلی ارسال کند.

الگوی Client برای Web:

```text
X-WooGit-Client: web
X-WooGit-Client-Version: 1.0.0
```

`X-WooGit-App-Version` مخصوص قرارداد App است و Theme نباید آن را جعل کند.

## ۳. نشست‌ها

```text
Android App → X-WooGit-Session
Theme       → X-WooGit-Web-Session
```

این Sessionها قابل جایگزینی نیستند. Web Session منقضی‌شده قابل revive محلی نیست؛ Login مجدد باید Session جدید بسازد و Backend دوباره Account + Site Ownership + Entitlement لازم را بررسی کند.

## ۴. قراردادهای Web

مسیرهای Web که Backend فعلاً برای Theme منتشر کرده است:

```text
GET  /account/requirements
POST /account/setup-web-credentials       # App-session based bootstrap/helper
POST /account/web-bootstrap               # Web-first registration
POST /web/login
POST /web/logout
GET  /web/me
POST /web/account/contact-email
POST /web/account/password
POST /web/password-recovery/start
POST /web/password-recovery/reset
GET  /web/billing/history
```

### Web-first Bootstrap — پیاده‌سازی V1

ثبت‌نام مستقیم وب از مسیر زیر انجام می‌شود:

```text
POST /account/web-bootstrap
```

ورودی:

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

`Idempotency-Key` برای این mutation الزامی است.

جریان واقعی Backend:

```text
Rate Limit
  ↓
Validate Idempotency-Key / replay state
  ↓
Validate input
  ↓
Verify real WooCommerce site with supplied credentials
  ↓
Resolve/Create Account + Site
  ↓
Reject already-configured Web credentials
  ↓
Create Web Credential
  ↓
Grant initial trial entitlement
  ↓
Issue Web Session
  ↓
Persist successful operation/idempotent result
```

Credentialهای WordPress/WooCommerce فقط request-scoped هستند و نباید در DB، options، session پایدار، cookie، browser storage، log، telemetry، audit، cache یا HTML/JS نگهداری شوند.

Backend از اطلاعات `account_id` و `site_id` ارسالی کاربر برای Authorization استفاده نمی‌کند؛ Account/Site از نتیجه verification و داده Backend resolve می‌شوند.

`/sites/verify` همچنان مسیر App/bootstrap است. Theme نباید با تغییر header یا payload آن را به‌عنوان Web API مصرف کند.

### رفتار Idempotency و Operation در Web Bootstrap

برای replay همان `Idempotency-Key`:

- اگر عملیات قبلاً موفق شده باشد، همان پاسخ ذخیره‌شده برگردانده می‌شود؛
- اگر عملیات در حال انجام باشد، Backend وضعیت pending را برمی‌گرداند؛
- اگر کلید با fingerprint متفاوت استفاده شود، `idempotency_conflict` برمی‌گردد؛
- اگر نتیجه عملیات unknown شده باشد، Backend وضعیت unknown و نیاز به reconciliation را اعلام می‌کند.

کدهای واقعی این مسیر شامل موارد زیر هستند:

```text
invalid_idempotency_key
idempotency_conflict
operation_in_progress
operation_status_unknown
operation_unavailable
operation_persistence_failed
missing_customer_credentials
invalid_web_password
password_confirmation_mismatch
invalid_site_url
site_verification_failed
site_unavailable
account_unavailable
web_credentials_already_configured
account_creation_failed
site_creation_failed
web_password_unavailable
entitlement_unavailable
web_session_creation_failed
```

Theme باید بر اساس HTTP status + `code` رفتار کند و نباید فرض کند همه این خطاها عضو یک enum محدود و از قبل ثابت‌شده هستند. کدهای جدید Web باید در همین قرارداد ثبت شوند، بدون تغییر دادن کدهای App که معنای مستقلی دارند.

### Password Recovery / Reset

Forgot Password از مسیر **Site URL + اثبات مجدد کنترل فروشگاه** انجام می‌شود و به Contact Email وابسته نیست. Contact Email فقط برای ارتباط با مشتری است.

```text
POST /web/password-recovery/start
```

و سپس:

```text
POST /web/password-recovery/reset
```

Reset authorization باید کوتاه‌عمر، single-use، محدود به همان Account/Site و غیرقابل استفاده به‌عنوان Web Session باشد. Reset token نباید در URL قرار گیرد.

پس از تغییر موفق رمز، Web Sessionهای قبلی revoke می‌شوند و Theme باید Login مجدد را درخواست کند.

## ۵. Billing برای Web و App

مسیرهای Billing موجود:

```text
GET  /billing/plans
GET  /billing/status
POST /billing/checkout
POST /billing/activate-session
```

`/billing/activate-session` **App-only** است و Theme نباید آن را مصرف کند.

در مسیرهای Billing موجود، Backend همچنان می‌تواند Context مشترک Account/Site را با توجه به نوع Session resolve کند؛ Theme نباید App Session را جعل یا reuse کند.

### Payment Method در Billing

در بخش `Payment Method`، Theme فقط داده‌های non-sensitive منتشرشده توسط Backend را نمایش می‌دهد:

```text
payment_method_type
payment_method_status
last_payment_at
expires_at
```

Theme نباید وضعیت یا تاریخ‌ها را حدس بزند یا از داده محلی بسازد. اطلاعات حساس پرداختی مانند شماره کامل کارت، CVV یا credentialهای Gateway نباید توسط Theme نمایش، ذخیره یا دریافت شوند.

## ۶. Checkout و Idempotency

Theme برای Checkout از قرارداد Backend استفاده می‌کند و `Idempotency-Key` را برای mutationهای پشتیبانی‌شده ارسال می‌کند. Retry همان عملیات باید همان کلید را حفظ کند.

در timeout-after-success، timeout یا قطع شبکه به معنی شکست قطعی نیست. Theme نباید صرفاً به دلیل timeout یک Checkout جدید با کلید جدید بسازد؛ نتیجه باید طبق operation/idempotency state موجود Backend پیگیری شود.

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
Backend status
```

Query parameter مانند `status=success` نباید trusted باشد. وضعیت نهایی باید از Backend گرفته شود.

## ۸. خطاها — قرارداد مخصوص Theme بدون تغییر قرارداد App

Theme فقط باید Error Codeهایی را که واقعاً در مسیر Web/Theme مصرف می‌کند، در قرارداد Web خود لحاظ کند.

### Web-specific فعلی

```text
invalid_web_credentials
invalid_web_session
invalid_web_password
invalid_current_password
password_confirmation_mismatch
invalid_contact_email
contact_email_unavailable
web_credentials_already_configured
missing_customer_credentials
invalid_web_password
invalid_site_url
site_verification_failed
site_unavailable
account_unavailable
web_password_unavailable
web_session_creation_failed
```

### Shared / Backend-level

برخی خطاها در بیش از یک Client مصرف می‌شوند و نباید صرفاً به خاطر Theme تغییر نام یا semantics داده شوند:

```text
validation_error
account_inactive
site_not_owned
not_entitled
rate_limited
idempotency_conflict
operation_in_progress
operation_status_unknown
operation_pending
operation_unknown
server_error
```

### App-specific

این کدها متعلق به قرارداد App هستند و Theme نباید آن‌ها را تغییر دهد یا برای Web شبیه‌سازی کند:

```text
invalid_session
APP_VERSION_DEPRECATED
insufficient_session_scope
billing_required
```

بنابراین وجود `invalid_session` در Backend به معنی خطای Web نیست؛ این کد برای `X-WooGit-Session` و App Session باقی می‌ماند، در حالی که Web Session از `invalid_web_session` استفاده می‌کند.

برای `429`، Theme باید در صورت وجود `Retry-After`/`retry_after` رفتار کنترل‌شده داشته باشد و retry تهاجمی نکند.

`docs/API_ERROR_CODES.md` مرجع Backend-wide است؛ این سند فقط نحوه مصرف Errorها در Theme را مشخص می‌کند و نباید با تغییر قرارداد App باعث بازطراحی Backend شود.

## ۹. نگهداری Web Session

Token نباید در URL قرار گیرد. گزینه ترجیحی Cookie امن `HttpOnly` + `Secure` + `SameSite` مناسب یا BFF/Bridge امن است. اگر Backend فقط Header را پشتیبانی کند، ذخیره خام Token در `localStorage` بدون تصمیم امنیتی صریح مجاز نیست.

## ۱۰. ممنوعیت‌ها

Theme نباید:

- مستقیماً به WooCommerce مشتری وصل شود؛
- Credential سایت مشتری را دائمی ذخیره کند؛
- App Session را برای Web جعل یا reuse کند؛
- Entitlement را خودش محاسبه کند؛
- موفقیت پرداخت را از URL نتیجه‌گیری کند؛
- Session منقضی‌شده را locally revive کند؛
- به کلاس یا سرویس داخلی Plugin وابسته شود؛
- `account_id`/`site_id` ارسالی کاربر را مرجع Authorization بداند؛
- برای هماهنگ شدن با Theme، قرارداد عملیاتی App را تغییر دهد.
