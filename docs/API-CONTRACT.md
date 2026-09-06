# قرارداد API ووگیت

> وضعیت: V1 Target Contract — Locked

## ۱. معماری

Backend در V1 یک **secure transparent gateway/proxy** است، نه یک WooCommerce business API.

```text
Android WooGit App
        ↓
   WooGit Backend
   Auth / Ownership / Entitlement / Security / Proxy
        ↓
Customer WordPress / WooCommerce
```

Backend نباید resource model، Product API، Order API یا response model مستقل از Customer site بسازد. App درخواست واقعی خود را به Backend می‌دهد و Backend همان request را، با مقصدی که از Site Identity resolve شده، به Customer site forward می‌کند و response upstream را تا حد ممکن بدون تغییر برمی‌گرداند.

## ۲. مدل احراز هویت

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

## ۳. Bootstrap / Verification

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

Verification read-only است و قبل از استفاده از gateway انجام می‌شود.

## ۴. WooGit Session

Session باید قابل اعتبارسنجی، expiration و revoke باشد و به Account و Site متصل باشد. Session منقضی‌شده معتبر نیست و locally revive نمی‌شود.

Automatic re-login یک Session Creation جدید است؛ Backend در آن دوباره Account + Site Ownership + Entitlement را بررسی می‌کند.

## ۵. درخواست عادی Gateway

`POST/GET/PUT/PATCH/DELETE /api/v1/forward` با Session و مشخصات request.

```text
App
 ↓
X-WooGit-Session
site_id از Session
request path + query + raw body
request-scoped Customer Credentials
 ↓
Session / Account / Site Ownership / Entitlement / Security
 ↓
resolve destination from registered Site Identity
 ↓
Customer WordPress/WooCommerce
 ↓
upstream status + body + relevant headers
 ↓
App
```

`path` تنها مسیر درخواست است و هرگز URL مقصد نیست. Client حق تعیین host/scheme مقصد را ندارد.

در صورت شکست authorization هیچ outbound request ارسال نمی‌شود.

Gateway body را به مدل تجاری Backend تبدیل نمی‌کند؛ JSON، multipart/binary و سایر payloadهای مورد نیاز باید به‌صورت request body عبور داده شوند.

## ۶. Controlled Gateway Surface

Client URL دلخواه تعیین نمی‌کند. Backend فقط pathهایی را قبول می‌کند که بخشی از network surface فعلی WooGit App هستند. این کنترل یک **security boundary** است و نباید به مجموعه‌ای از endpointهای business-domain در Backend تبدیل شود.

در V1 مسیرهای WooCommerce REST و WordPress Media که App استفاده می‌کند قابل forward هستند. مقصد همیشه از Site Identity ثبت‌شده resolve می‌شود.

SSRF protection شامل HTTPS-only، رد localhost/private/reserved IP، نبود credential در URL و جلوگیری از path traversal است.

## ۷. Idempotency و Timeout-after-success

برای mutationها:

```http
Idempotency-Key: <stable-client-operation-key>
```

Fingerprint شامل method + path + query + hash بدنه خام request است. Retry با همان Key و همان Request نباید عملیات دوم ایجاد کند. استفاده از همان Key برای Request متفاوت باید Conflict باشد.

در صورت timeout پس از ارسال request، Backend نباید موفقیت یا شکست عملیات Customer site را جعل کند؛ operation به وضعیت `unknown` می‌رود و App می‌تواند با `operation_id` وضعیت را بررسی کند.

`GET /api/v1/operations/{operation_id}` فقط برای بازیابی state عملیات gateway است و API محصول/سفارش محسوب نمی‌شود.

## ۸. Sites / Subscription

فقط Site مجاز Account قابل استفاده است. Customer Credentials هرگز در Response سایت نمایش داده نمی‌شوند.

Subscription و Entitlement مرجع Backend هستند و Account/Plan منقضی نباید outbound request داشته باشد.

## ۹. Currency / Collections / Errors

Backend مقدار response و query semantics Customer WooCommerce را حفظ می‌کند و currency را hard-code یا بی‌دلیل تبدیل نمی‌کند.

Pagination/filter/sort متعلق به upstream WooCommerce است و gateway باید query و response headerهای مرتبط را عبور دهد؛ Backend برای این موارد collection API مستقل نمی‌سازد.

Errorها machine-readable هستند و Secret، SQL، Stack Trace یا Customer Credential در Response عمومی قرار نمی‌گیرد.

## ۱۰. Customer Credential Storage

در V1:

- DB: ممنوع؛
- Vault: ممنوع؛
- persistent cache: ممنوع؛
- Log/Telemetry/Audit/Crash: ممنوع؛
- reuse برای Request یا Site دیگر: ممنوع.

هیچ قابلیت V1 نباید فرض کند Customer Credential پایدار در Backend وجود دارد.
