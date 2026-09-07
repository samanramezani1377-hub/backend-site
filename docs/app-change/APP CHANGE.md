# APP CHANGE — قرارداد تطبیق اپ با WooGit Backend

> این سند فقط قرارداد تغییرات لازم در Android App برای اتصال به WooGit Backend است. Web Account و Admin UI خارج از این قرارداد هستند.

## 1. معماری V1

App نباید مستقیماً به Customer Site متصل شود. تمام عملیات Customer باید از Backend عبور کند:

```text
App
  ↓ HTTPS + WooGit Session
WooGit Backend
  ↓ Session + Account + Site control/ownership + Entitlement validation
Customer WordPress / WooCommerce
```

حتی GET محصولات، سفارش‌ها و تصاویر نیز نباید مستقیم به Customer Site ارسال شوند.

## 2. Base URL و مسیرها

App باید Backend Base URL را برای APIهای WooGit استفاده کند:

```text
/wp-json/woogit/v1/...
```

Customer Site URL فقط برای شناسایی Site و verify اولیه است و نباید مقصد مستقیم request عملیاتی App باشد.

تمام عملیات Customer از این مسیر عبور می‌کنند:

```http
GET    /wp-json/woogit/v1/forward?path=...
POST   /wp-json/woogit/v1/forward?path=...
PUT    /wp-json/woogit/v1/forward?path=...
PATCH  /wp-json/woogit/v1/forward?path=...
DELETE /wp-json/woogit/v1/forward?path=...
```

`path` یک query parameter مخصوص Backend است. متد HTTP از خود request تعیین می‌شود و App نباید `method` را داخل JSON body برای تعیین متد بفرستد.

Queryهای Customer نیز باید query واقعی HTTP باشند و Backend فقط `path` را حذف کرده و بقیه query را به Customer forward می‌کند.

## 3. اتصال اولیه Site

```http
POST /wp-json/woogit/v1/sites/verify
```

این endpoint Session قبلی نمی‌خواهد و باید با credentialهای Customer فراخوانی شود:

```json
{
  "url": "https://customer-site.example",
  "wordpress_username": "...",
  "wordpress_application_password": "...",
  "consumer_key": "ck_...",
  "consumer_secret": "cs_..."
}
```

`email` اگر ارسال شود فقط contact metadata است و هویت Account نیست.

Response ممکن است `scope=operational` یا `scope=billing` بدهد. Billing Session برای عملیات Customer قابل استفاده نیست.

## 4. Session Lifecycle

برای endpointهای Session-aware:

```http
X-WooGit-Session: <session-token>
```

تفکیک فعلی:

| Endpoint | Session |
|---|---|
| `/sites/verify` | ندارد |
| `/billing/plans` | ندارد |
| `/billing/status` | هر Session معتبر Account |
| `/billing/checkout` | هر Session معتبر Account |
| `/billing/activate-session` | فقط Billing |
| `/operations/{operation_id}` | فقط Operational |
| `/sessions/revoke` | token-based revoke |
| `/forward` | فقط Operational |

Session منقضی یا revoke شده هرگز مجوز معتبر نیست.

پس از expiry، App باید طبق flow احراز هویت موجود دوباره Session بگیرد؛ Backend در ایجاد Session جدید Account + Site control/ownership و برای operational، Entitlement را دوباره بررسی می‌کند.

بعد از خرید موفق، Billing Session به Operational تبدیل نمی‌شود. App باید:

```http
POST /wp-json/woogit/v1/billing/activate-session
```

را با Billing Session اجرا کند و token جدید را دریافت کند.

## 5. مسیرهای Customer مجاز

WooCommerce:

```text
/wp-json/wc/v3/*
```

WordPress Media:

```text
/wp-json/wp/v2/media
/wp-json/wp/v2/media/*
```

هیچ namespace دیگر WordPress را نباید به‌عنوان مسیر عمومی Proxy فرض کرد.

## 6. WooCommerce Operations

Repositoryهای فعلی App باید از abstraction شبکه استفاده کنند، اما مقصد عملیاتی آنها باید Backend `/forward` باشد.

مواردی مانند products، orders، categories، variations، attributes/terms، reports، `system_status` و سایر endpointهای مجاز `wc/v3` باید از `/forward` عبور کنند.

Customer WooCommerce منبع اصلی حقیقت برای وضعیت سفارش است؛ App نباید status سفارش را مستقل و authoritative به‌صورت local نگهداری کند.

## 7. Media

Upload/read/update/delete Media باید از Backend عبور کند:

```text
/wp-json/wp/v2/media
/wp-json/wp/v2/media/{id}
```

برای Media:

```http
X-WooGit-Wordpress-Username: ...
X-WooGit-Wordpress-Application-Password: ...
```

برای WooCommerce:

```http
X-WooGit-Consumer-Key: ...
X-WooGit-Consumer-Secret: ...
```

حتی اگر Customer یک `source_url` برای Media برگرداند، App نباید آن URL را مستقیم fetch کند؛ download/read نیز باید از Backend عبور کند.

## 8. Query و Body

نمونه صحیح:

```http
GET /wp-json/woogit/v1/forward?path=%2Fwp-json%2Fwc%2Fv3%2Fproducts&page=1&per_page=20&search=phone
```

در این request:

```text
Backend path: /wp-json/wc/v3/products
Customer query: page=1&per_page=20&search=phone
```

برای `POST`, `PUT`, `PATCH`، body واقعی Customer API باید بدون envelope ساختگی ارسال شود و `Content-Type` صحیح حفظ شود.

### محدودیت‌های V1

App باید قبل از ارسال request این محدودیت‌ها را رعایت کند:

- request body معمولی Customer: حداکثر حدود 5 MiB
- Media request body: حداکثر حدود 10 MiB
- timeout سمت Proxy برای درخواست Customer: حدود 20 ثانیه

App نباید timeout را به‌تنهایی failure قطعی mutation تلقی کند؛ قواعد بخش Unknown باید اعمال شوند.

JSON زیر wire contract نیست و فقط می‌تواند مدل داخلی App باشد:

```json
{
  "path": "/wp-json/wc/v3/products",
  "method": "GET",
  "query": {"page": 1}
}
```

## 9. Mutation Idempotency — الزامی

تمام mutationهای Customer (`POST`, `PUT`, `PATCH`, `DELETE`) باید با:

```http
Idempotency-Key: <stable-client-operation-key>
```

ارسال شوند.

کلید باید برای یک عملیات منطقی پایدار بماند. Retry همان عملیات باید دقیقاً همان کلید را استفاده کند. استفاده از یک کلید برای دو عملیات متفاوت ممنوع است؛ همان کلید با request متفاوت باید به‌عنوان Conflict مدیریت شود.

## 10. Timeout-after-success / Unknown Operation

Timeout به‌تنهایی به معنی failure قطعی نیست.

اگر request به Customer انجام شده ولی response به App نرسیده، App باید `operation_id` و همان `Idempotency-Key` را حفظ کند.

اگر Backend وضعیت `unknown` داد، App نباید mutation را با کلید جدید تکرار کند. وضعیت باید از:

```http
GET /wp-json/woogit/v1/operations/{operation_id}
```

reconcile شود.

App باید حداقل این stateها را بشناسد:

```text
pending
succeeded
failed
unknown
```

`unknown` نباید خودکار به `failed` تبدیل شود.

## 11. App Version Gate — الزامی برای App جدید

**هر requestی که App به Backend می‌فرستد و مشمول API قرارداد WooGit است باید header زیر را داشته باشد:**

```http
X-WooGit-App-Version: <app-version>
```

ارسال نسخه در implementation اپ **اجباری** است و نباید به‌صورت optional یا nullable در transport تعریف شود.

منبع version باید version واقعی build اپ باشد؛ در V1 مقدار مورد انتظار همان `versionName` release/build است و نباید دستی در چند محل تکرار شود.

Backend در V1 فعلاً ممکن است نبودن این header را برای سازگاری با Appهای قدیمی قبول کند. این رفتار legacy compatibility است و به معنی optional بودن header در App جدید نیست.

در آینده می‌توان Backend را نیز fail-closed کرد تا نبودن `X-WooGit-App-Version` رد شود؛ App نباید برای آن تغییر منتظر بماند و از همین V1 باید همیشه header را ارسال کند.

در صورت deprecated/unsupported بودن نسخه، Backend ممکن است بدهد:

```http
426 Upgrade Required
```

با:

```text
APP_VERSION_DEPRECATED
```

و اطلاعاتی مانند:

```json
{
  "code": "APP_VERSION_DEPRECATED",
  "update_required": true,
  "minimum_supported_version": "...",
  "latest_version": "...",
  "recommended_version": "..."
}
```

App باید این پاسخ را به typed update-required state تبدیل کند، نه خطای عمومی شبکه.

## 12. In-App Announcements

App باید endpoint زیر را پشتیبانی کند:

```http
GET /wp-json/woogit/v1/announcements
```

این قابلیت In-App Announcement است و Push Notification نیست.

Announcement می‌تواند شامل:

```json
{
  "id": "...",
  "type": "critical",
  "title": "...",
  "message": "...",
  "priority": 100,
  "display_type": 1,
  "action": {},
  "dismissible": true,
  "starts_at": "...",
  "expires_at": "..."
}
```

`display_type` یک **opaque numeric contract** است. Backend فقط عدد را می‌فرستد و UI mapping متعلق به App است. مثلاً App می‌تواند `1` را full banner و `2` را expandable notice تعریف کند؛ این mapping جزو قرارداد Backend نیست.

Actionهای ناشناخته باید safely نادیده گرفته شوند و App نباید صرفاً بر اساس داده Backend action/deep-link ناامن اجرا کند.

Announcement مربوط به deprecated App باید قابل دریافت باقی بماند تا App بتواند پیام Update را نمایش دهد.

## 13. Error Contract

App باید Backend error را بر اساس `code` و state، نه فقط HTTP status، طبقه‌بندی کند.

حداقل این موارد باید typed باشند:

```text
APP_VERSION_DEPRECATED
invalid_session
insufficient_session_scope
not_entitled
billing_required
idempotency_conflict
operation_in_progress
operation_status_unknown
operation_pending
operation_unknown
RATE_LIMITED
```

`operation_in_progress` و `operation_pending` از `/forward` و Billing lifecycle می‌توانند معادل مفهومی داشته باشند؛ App باید هر دو را به state داخلی `pending` نگاشت کند.

`operation_status_unknown` و `operation_unknown` نیز باید هر دو به state داخلی `unknown` نگاشت شوند و هرگز به failure قطعی تبدیل نشوند.

برای Rate Limit، retry تهاجمی ممنوع است. در صورت وجود `Retry-After` باید رعایت شود و backoff مناسب اعمال شود.

Session error باید باعث invalidate/reconcile کردن Session state شود و App نباید همان token نامعتبر را بی‌نهایت retry کند.

## 14. Response Headers و Pagination

App باید headerهای pagination برگشتی Customer را در حد نیاز حفظ و مصرف کند، به‌خصوص:

```text
X-WP-Total
X-WP-TotalPages
```

نام headerها باید case-insensitive مصرف شوند.

Pagination نباید از تعداد آیتم‌های صفحه فعلی حدس زده شود.

این مورد برای products، orders، Media، categories، variations و attributes اعمال می‌شود.

## 15. Credential Boundary

Customer credentials با WooGit Session یکی نیستند.

Customer credentials:

```text
Site URL
WordPress username
WordPress Application Password
WooCommerce Consumer Key
WooCommerce Consumer Secret
```

WooGit authorization:

```http
X-WooGit-Session: <session-token>
```

Customer credentialها باید در secure storage موجود App نگهداری شوند و در log/telemetry ثبت نشوند.

Customer credential نباید در URL request Backend قرار بگیرد.

Web Account Password مربوط به Backend Website است و نباید وارد این flow شود.

## 16. معماری موردنیاز در App

تغییرات App باید ترجیحاً با یک transport/adapter در پشت abstraction فعلی انجام شود تا Repositoryهای Domain بی‌دلیل بازنویسی نشوند:

```text
UI
 ↓
Repository / Use Case
 ↓
WooCommerce API abstraction
 ↓
Backend Transport
 ├─ X-WooGit-Session
 ├─ X-WooGit-App-Version  ← REQUIRED
 ├─ Idempotency-Key
 ├─ Customer path
 ├─ Customer credential headers
 └─ Backend error mapping
 ↓
Backend /forward
 ↓
Customer WooCommerce / WordPress
```

Flowهای Backend-specific جدا باشند:

```text
Backend API
 ├─ Site Verify
 ├─ Session lifecycle
 ├─ Billing
 ├─ Operations
 ├─ Announcements
 └─ Version Gate
```

هدف حذف featureهای فعلی یا بازنویسی بی‌دلیل Domain نیست؛ هدف تغییر transport و اضافه کردن contractهای Backend است.

## 17. V1 Acceptance Checklist

- [ ] هیچ request مستقیم App به Customer WooCommerce وجود نداشته باشد.
- [ ] هیچ request مستقیم App به Customer WordPress Media وجود نداشته باشد.
- [ ] `/sites/verify` برای اتصال اولیه استفاده شود.
- [ ] Session در `X-WooGit-Session` ارسال شود.
- [ ] Billing و Operational Session جدا باشند.
- [ ] `/forward` تنها مسیر عملیاتی Customer باشد.
- [ ] تمام mutationهای Customer `Idempotency-Key` پایدار داشته باشند.
- [ ] timeout/unknown بدون ایجاد mutation دوم reconcile شود.
- [ ] `X-WooGit-App-Version` در implementation اپ همیشه ارسال شود.
- [ ] `426 APP_VERSION_DEPRECATED` به typed update-required state تبدیل شود.
- [ ] `/announcements` به‌عنوان In-App UI پشتیبانی شود، نه Push.
- [ ] `display_type` فقط به‌عنوان عدد opaque مصرف شود.
- [ ] `operation_pending`/`operation_in_progress` به pending و `operation_unknown`/`operation_status_unknown` به unknown نگاشت شوند.
- [ ] `X-WP-Total` و `X-WP-TotalPages` برای pagination مصرف شوند.
- [ ] Customer credentialها در URL یا log/telemetry قرار نگیرند.
- [ ] Web Account password وارد App flow نشود.
- [ ] body/timeout limits رعایت شوند.

## 18. خارج از Scope App CHANGE

موارد زیر عمداً در این سند نیستند:

- Web Account login
- Web Account password
- contact email management
- payment history UI در وب
- Admin UI
- مدیریت Version Policy در WordPress Admin
- مدیریت Announcement در WordPress Admin

این‌ها متعلق به WooGit Backend Website/Admin هستند و نباید به Android App credential/auth flow اضافه شوند.
