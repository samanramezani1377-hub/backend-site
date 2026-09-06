# قرارداد API ووگیت

> وضعیت: **V1 Target Contract — Locked**
>
> این قرارداد باید با اپ Android موجود WooGit هماهنگ باشد و کمترین تغییر ممکن را در Client تحمیل کند. اپ فعلی هنوز مستقیماً به Customer WordPress/WooCommerce متصل است؛ اتصال به Backend بخشی از Migration است.

## ۱. مدل احراز هویت

در V1 دو دسته Credential وجود دارد:

```text
WooGit Session
    → احراز هویت و مجوز Client در WooGit Backend

WP Username
WP Application Password
WC Consumer Key
WC Consumer Secret
    → احراز Backend نزد Customer WordPress/WooCommerce
```

**Access Token + Refresh Token جزو قرارداد WooGit نیست.** Client برای درخواست عادی فقط یک WooGit Session معتبر ارسال می‌کند.

Customer Credentials برای Proxy عادی می‌توانند همراه همان Request از Client به Backend برسند. Credential Vault برای مسیر عادی اجباری نیست.

## ۲. Bootstrap / Connection Verification

اولین اتصال ممکن است هنوز Session کامل نداشته باشد و Flow جداگانه‌ای دارد.

### `POST /api/v1/sites/verify`

Request مفهومی:

```json
{
  "url": "https://example.com",
  "wordpress_username": "...",
  "wordpress_application_password": "...",
  "woocommerce_consumer_key": "...",
  "woocommerce_consumer_secret": "..."
}
```

ترتیب Verification:

```text
Network / HTTPS
    ↓
WordPress reachability
    ↓
WordPress authentication / identity
    ↓
WooCommerce availability / authentication
    ↓
Site Identity
    ↓
Account / Trial lifecycle
    ↓
WooGit Session
```

Verification باید read-only باشد و نباید برای تست اتصال محصول، سفارش یا رسانه ایجاد کند.

تا Verification کامل موفق نشود، Site/Account/Trial/Session به‌عنوان اتصال موفق اعلام نمی‌شوند.

Credential خام هرگز در Response بازگردانده نمی‌شود.

## ۳. WooGit Session

پس از Onboarding، Backend یک WooGit Session معتبر برای Client فعال می‌کند.

Session باید:

- قابل اعتبارسنجی باشد؛
- expiration داشته باشد؛
- قابل revoke باشد؛
- به Account متصل باشد؛
- در صورت نیاز context مربوط به Site را مشخص کند؛
- برای logout قابل پایان دادن باشد.

مقدار خام Session نباید در Log یا storage ناامن ثبت شود.

## ۴. درخواست عادی

```text
Client
 ↓
WooGit Session + site_id
+ 4 Customer Credentials
+ operation/path/query/body
 ↓
Backend authorization
 ↓
Controlled Forwarding
 ↓
Customer WordPress/WooCommerce
 ↓
Response
 ↓
Client
```

Backend قبل از Forward باید حداقل بررسی کند:

1. Session معتبر باشد.
2. Account فعال باشد.
3. Trial/Subscription منقضی نشده باشد.
4. Site متعلق به Account باشد.
5. Entitlement عملیات را اجازه دهد.
6. Version/Security/Rate Limit برقرار باشد.
7. در mutationهای لازم Idempotency رعایت شود.

در صورت شکست هر مورد، Request به Customer Site ارسال نمی‌شود.

## ۵. Controlled Gateway Surface

برای جلوگیری از SSRF، Client حق تعیین URL دلخواه ندارد.

نمونه endpointهای کنترل‌شده:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

وجود segment `gateway` در API به معنی `WooGit Gateway Plugin` روی سایت مشتری نیست؛ این فقط integration surface داخل `WooGit Main Plugin` است.

مقصد Customer از Site Identity ثبت‌شده resolve می‌شود.

## ۶. حداقل تغییر در Request/Response

پیاده‌سازی داخلی می‌تواند Lightweight Forwarding باشد:

```text
HTTP method
path
query
body
relevant headers
```

تا حد امکان حفظ می‌شوند؛ Backend فقط برای امنیت، authorization، استاندارد خطا، Request ID و قرارداد API تغییر لازم را اعمال می‌کند.

Backend نباید Products/Orders/Customers/Media را بدون نیاز Mirror کند.

## ۷. Idempotency

برای CREATE و سایر mutationهای non-idempotent موردنیاز:

```http
Idempotency-Key: <stable-client-operation-key>
```

Backend باید حداقل این اطلاعات را برای عملیات نگهداری کند:

```text
Account
Site
Idempotency Key
Operation Type
Request Fingerprint
State
Remote Reference
Result / canonical status
```

Retry با همان کلید و همان Request نباید عملیات دوم را روی Customer Site اجرا کند.

اگر همان کلید با Request متفاوت استفاده شود، Backend باید آن را Conflict تلقی کند.

## ۸. Timeout-after-success

سناریوی اجباری:

```text
Client → Backend → Customer: CREATE
Customer → SUCCESS
Backend ← response
Client ← timeout / response lost
Client → retry same operation identity
Backend → previous result / known state
```

برای عملیات دارای Operation Identity:

```text
GET /api/v1/operations/{operation_id}
```

باید امکان بازیابی وضعیت نهایی وجود داشته باشد.

## ۹. Sites

```text
GET /api/v1/sites
GET /api/v1/sites/{site_id}
```

فقط Siteهای مجاز Account برگردانده می‌شوند. Customer Credentials هیچ‌گاه در این Responseها نمایش داده نمی‌شوند.

Local Store ID فعلی Android، که از Domain Hash ساخته می‌شود، نباید مستقیماً `site_id` Backend تلقی شود.

## ۱۰. Subscription / Entitlement

Subscription و Entitlement مرجع Backend هستند.

Client نمی‌تواند با ارسال flag، status یا تاریخ محلی، policy سمت سرور را تغییر دهد.

در صورت انقضا یا بسته/غیرفعال شدن Account، هیچ outbound Customer request مجاز نیست.

## ۱۱. Currency

Backend نباید Currency را hard-code، تبدیل یا حذف کند. Currency و context مالی لازم برای Client باید از Customer WooCommerce عبور داده شود.

## ۱۲. Pagination / Filtering / Sorting

Collection endpointها باید قرارداد پایدار برای:

- page/cursor
- page size
- filters
- search
- sort
- total/has-more در صورت پشتیبانی

داشته باشند و با الگوی فعلی Client، از جمله listهای Products و Orders با page size فعلی 30، قابل تطبیق باشند.

## ۱۳. Error Contract

خطاها باید پایدار و machine-readable باشند:

```json
{
  "error": {
    "code": "subscription_expired",
    "message": "دسترسی اشتراک منقضی شده است.",
    "request_id": "...",
    "retryable": false
  }
}
```

هیچ Customer Credential، SQL، Stack Trace یا Secret ارائه‌دهنده نباید در Response عمومی قرار گیرد.

## ۱۴. Request ID

هر درخواست باید Request ID داشته باشد. Request ID می‌تواند برای troubleshooting استفاده شود، اما Body یا Header حاوی Secret نباید Log شود.

## ۱۵. Bridge / Chat / Analytics / AI

این قابلیت‌ها optional هستند و نباید برای مسیر اصلی اتصال و Lightweight Proxy وابستگی اجباری ایجاد کنند.

هر قابلیت background که به Customer Credentials بدون حضور Client نیاز داشته باشد، باید قبل از اجرا storage امن Credential و مدل عملیاتی خودش را به‌صورت یک تصمیم جداگانه مشخص کند.
