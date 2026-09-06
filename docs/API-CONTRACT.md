# قرارداد API ووگیت

این سند قرارداد V1 را در سطح عمومی تعریف می‌کند. Backend باید با کمترین تغییر ممکن با Android Client موجود سازگار شود.

## ۱. اصول احراز هویت

در درخواست عادی Backend دو دسته اطلاعات دریافت می‌کند:

```text
WooGit Session
    → احراز و مجوز مصرف‌کننده در Backend

WP Username
WP Application Password
WC Consumer Key
WC Consumer Secret
    → احراز دسترسی به Customer Site
```

Customer Credentials ممکن است در Client موجود باشند و برای همان Request به Backend ارسال شوند. Backend نباید برای Proxy عادی به Credential Vault lookup وابسته باشد.

## ۲. Bootstrap / Connection Verification

Flow اولیه ممکن است بدون WooGit Session کامل انجام شود:

### POST `/api/v1/sites/verify`

نمونه مفهومی:

```json
{
  "url": "https://example.com",
  "wordpress_username": "...",
  "wordpress_application_password": "...",
  "woocommerce_consumer_key": "...",
  "woocommerce_consumer_secret": "..."
}
```

Backend ابتدا WordPress reachability/authentication و سپس WooCommerce verification را انجام می‌دهد. تا Verification کامل موفق نشود، Site/Account/Trial به‌عنوان اتصال موفق اعلام نمی‌شوند.

Credentialها هرگز در Response بازگردانده نمی‌شوند.

## ۳. Session

پس از Bootstrap/Account lifecycle یک WooGit Session معتبر برای درخواست‌های عادی صادر/فعال می‌شود.

جزئیات مکانیزم Session (نوع دقیق Token، Rotation و غیره) در سؤال معماری مربوط به Session تعیین می‌شود و این قرارداد آن را به یک مدل خاص قفل نمی‌کند.

## ۴. Gateway / Lightweight Proxy

برای جلوگیری از SSRF، Client نباید URL مقصد دلخواه ارسال کند.

قرارداد بیرونی می‌تواند مسیرهای شناخته‌شده داشته باشد:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

اما رفتار داخلی Lightweight Proxy است:

```text
Client Request
   ↓
Backend authorization checks
   ↓
Forward same path/query/body as applicable
   ↓
Customer WordPress/WooCommerce
   ↓
Return response with minimum necessary transformation
```

Backend نباید برای هر عملیات داده را دوباره مدل یا Mirror کند.

## ۵. اطلاعات Request Gateway

هر درخواست عادی Gateway شامل این اطلاعات منطقی است:

```text
WooGit Session
site_id / destination reference
WP Username
WP Application Password
WC Consumer Key
WC Consumer Secret
HTTP method
path/query
body
Idempotency-Key (برای mutationهای لازم)
```

Backend قبل از Forward باید حداقل این موارد را بررسی کند:

1. Session معتبر باشد.
2. Account بسته/غیرفعال نباشد.
3. Trial/Subscription منقضی نشده باشد.
4. Account مالک/مجاز Site باشد.
5. Entitlement عملیات را اجازه دهد.
6. Version/Security/Rate Limit برقرار باشد.

در صورت شکست، Request به Customer Site ارسال نمی‌شود.

## ۶. Customer Response

Response Customer Site باید تا حد امکان بدون تغییر غیرضروری به Client برگردد. Backend فقط در موارد لازم برای قرارداد امنیتی/خطا/Request ID آن را حداقل transform می‌کند.

Customer Credential نباید در Response قرار بگیرد.

## ۷. Idempotency

برای CREATE و سایر mutationهای non-idempotent که نیاز به محافظت دارند:

```http
Idempotency-Key: <stable-client-key>
```

Retry با همان کلید باید نتیجه عملیات قبلی را برگرداند یا وضعیت canonical آن را مشخص کند و نباید CREATE دوم اجرا کند.

## ۸. Timeout-after-success

Client باید بتواند بعد از Timeout وضعیت Operation را بررسی کند:

```text
GET /api/v1/operations/{operation_id}
```

این نقطه باید برای عملیات‌هایی که Operation Identity دارند امکان بازیابی نتیجه را فراهم کند.

## ۹. Account / Site

```text
GET /api/v1/sites
GET /api/v1/sites/{site_id}
```

این نقاط فقط Siteهای مجاز Account را برمی‌گردانند و Customer Credential را هرگز نمایش نمی‌دهند.

## ۱۰. Subscription / Entitlement

Subscription و Entitlement مرجع تصمیم Backend هستند. Client نباید با ارسال Flag یا وضعیت محلی، انقضا یا محدودیت را دور بزند.

## ۱۱. Bridge / Chat / Analytics / AI

این قابلیت‌ها در صورت فعال بودن APIهای جداگانه دارند و نباید برای مسیر اصلی Lightweight Proxy اجباری باشند.

## ۱۲. Error Contract

خطاها باید پایدار و قابل پردازش باشند:

```json
{
  "error": {
    "code": "subscription_expired",
    "message": "WooGit subscription has expired.",
    "request_id": "..."
  }
}
```

هرگز Customer Credential، SQL، Stack Trace یا Secret ارائه‌دهنده در Response عمومی قرار نگیرد.

## ۱۳. Request ID

هر درخواست باید یک Request ID داشته باشد. این شناسه می‌تواند برای عیب‌یابی در Log داخلی استفاده شود، بدون اینکه Body یا Headerهای حاوی Secret ثبت شوند.
