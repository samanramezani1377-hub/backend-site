# قرارداد API ووگیت

> وضعیت: V1 Target Contract — Locked

## ۱. مدل احراز هویت

```text
WooGit Session
    → Authentication / Authorization در Backend

WP Username
WP Application Password
WC Consumer Key
WC Consumer Secret
    → Authentication نزد Customer WordPress/WooCommerce
```

Access Token + Refresh Token جزو قرارداد V1 نیست.

Customer Credentials برای Request لازم، همراه همان Request ارسال می‌شوند و Backend آن‌ها را در V1 ذخیره نمی‌کند.

## ۲. Bootstrap / Verification

`POST /api/v1/sites/verify`

Request مفهومی شامل URL و چهار Customer Credential است.

ترتیب:

```text
Network / HTTPS
 ↓
WordPress reachability/authentication
 ↓
WooCommerce availability/authentication
 ↓
Site Identity
 ↓
Account / Trial lifecycle
 ↓
WooGit Session
```

Verification read-only است و قبل از هر mutation تجاری انجام می‌شود.

## ۳. WooGit Session

Session باید قابل اعتبارسنجی، expiration و revoke باشد و به Account متصل باشد. Session منقضی‌شده معتبر نیست و locally revive نمی‌شود.

Automatic re-login یک Session Creation جدید است؛ Backend در آن دوباره Account + Site Ownership + Entitlement را بررسی می‌کند.

## ۴. درخواست عادی

```text
Client
 ↓
WooGit Session + site_id
+ Customer Credentials (request-scoped)
+ operation/path/query/body
 ↓
Session / Account / Subscription / Entitlement / Site ownership / Security
 ↓
Controlled Forwarding
 ↓
Customer WordPress/WooCommerce
```

در صورت شکست authorization هیچ outbound request ارسال نمی‌شود.

## ۵. Controlled Gateway Surface

Client URL دلخواه تعیین نمی‌کند. مقصد از Site Identity ثبت‌شده resolve می‌شود و فقط operation/pathهای مجاز قابل Forward هستند.

## ۶. Idempotency و Timeout-after-success

برای CREATE و mutationهای لازم:

```http
Idempotency-Key: <stable-client-operation-key>
```

Retry با همان Key و همان Request نباید عملیات دوم ایجاد کند. استفاده از همان Key برای Request متفاوت باید Conflict باشد.

`GET /api/v1/operations/{operation_id}` باید در صورت پشتیبانی operation state نهایی را قابل بازیابی کند.

## ۷. Sites / Subscription

فقط Siteهای مجاز Account برگردانده می‌شوند. Customer Credentials هرگز در Response سایت نمایش داده نمی‌شوند.

Subscription و Entitlement مرجع Backend هستند و Account/Plan منقضی نباید outbound request داشته باشد.

## ۸. Currency / Collections / Errors

Currency از Customer WooCommerce حفظ می‌شود و hard-code یا بی‌دلیل تبدیل نمی‌شود.

Collection APIها باید pagination/filter/sort پایدار داشته باشند.

Errorها machine-readable هستند و Secret، SQL، Stack Trace یا Customer Credential در Response عمومی قرار نمی‌گیرد.

## ۹. Customer Credential Storage

در V1:

- DB: ممنوع؛
- Vault: ممنوع؛
- persistent cache: ممنوع؛
- Log/Telemetry/Audit/Crash: ممنوع؛
- reuse برای Request یا Site دیگر: ممنوع.

هیچ قابلیت V1 نباید فرض کند Customer Credential پایدار در Backend وجود دارد.
