# APP CHANGE — قرارداد تطبیق اپ با WooGit Backend

> هدف این سند: قرارداد اجرایی بین WooGit Android App و `backend-site` است. هر تغییری در لایه شبکه، Repository، Session، Store Connection یا عملیات WooCommerce در App باید با این سند تطبیق داده شود.

## 1. اصل معماری

اپ نباید مستقیماً به سایت مشتری متصل شود. مسیر عملیاتی همیشه باید این باشد:

```text
App
  │
  │ HTTPS + WooGit Session
  ▼
WooGit Backend
  │
  │ اعتبارسنجی Session + Account + Site Ownership + Entitlement
  │ HTTPS + Customer Credentials
  ▼
Customer WordPress / WooCommerce
  │
  ▼
WooGit Backend
  │
  ▼
App
```

هیچ ارتباط مستقیمی بین App و Customer Site نباید وجود داشته باشد؛ حتی GETهای ساده محصولات، سفارش‌ها یا تصاویر نیز باید از Backend عبور کنند.

---

## 2. Base URLها

App باید دو مفهوم جدا داشته باشد:

### Backend Base URL

آدرس Backend نصب‌شده WooGit. تمام APIهای WooGit از این Base URL استفاده می‌کنند:

```text
/wp-json/woogit/v1/...
```

### Customer Site URL

آدرس سایت مشتری فقط به‌عنوان هویت Site در Backend استفاده می‌شود و App نباید آن را به‌عنوان مقصد مستقیم API استفاده کند.

هر request عملیاتی به Customer باید به Backend ارسال شود و `path` مقصد Customer به‌عنوان داده request در `/forward` قرار گیرد.

---

## 3. اتصال اولیه Site

Endpoint:

```http
POST /wp-json/woogit/v1/sites/verify
```

این endpoint برای ایجاد Session اولیه است و Session قبلی نمی‌خواهد.

Request JSON باید شامل موارد زیر باشد:

```json
{
  "url": "https://customer-site.example",
  "wordpress_username": "...",
  "wordpress_application_password": "...",
  "consumer_key": "ck_...",
  "consumer_secret": "cs_..."
}
```

`email` در صورت ارسال فقط metadata تماس است و نباید هویت Account محسوب شود.

Backend در این مرحله Customer را verify می‌کند، Account/Site را resolve می‌کند و بر اساس Entitlement یک Session صادر می‌کند.

Response ممکن است Session عملیاتی بدهد:

```json
{
  "account_id": "...",
  "site_id": "...",
  "session": "...",
  "scope": "operational",
  "access_enabled": true,
  "billing_required": false
}
```

یا Billing Session بدهد:

```json
{
  "account_id": "...",
  "site_id": "...",
  "session": "...",
  "scope": "billing",
  "access_enabled": false,
  "billing_required": true
}
```

اگر scope برابر `billing` است، App نباید هیچ عملیات Customer را با آن Session اجرا کند.

---

## 4. Session Contract

برای endpointهایی که نیازمند Session هستند، App باید:

```http
X-WooGit-Session: <session-token>
```

را ارسال کند.

طبقه‌بندی فعلی endpointها:

| Endpoint | Session موردنیاز |
|---|---|
| `POST /sites/verify` | ندارد؛ برای ایجاد Session اولیه است |
| `GET /billing/plans` | ندارد |
| `GET /billing/status` | هر Session معتبر Account کافی است |
| `POST /billing/checkout` | هر Session معتبر Account کافی است |
| `POST /billing/activate-session` | فقط Billing Session |
| `GET /operations/{operation_id}` | فقط Operational Session |
| `POST /sessions/revoke` | Token را دریافت می‌کند و revoke می‌کند؛ در Backend فعلی `authenticateContext` اجرا نمی‌شود |
| `/forward` | فقط Operational Session |

Session منقضی یا revoke شده نباید به‌عنوان مجوز معتبر استفاده شود.

پس از expiry، App باید طبق قرارداد احراز هویت موجود دوباره فرآیند لازم برای ایجاد Session را طی کند؛ Backend هنگام ایجاد Session جدید باید Account + Site Ownership و در مسیر operational، Entitlement را دوباره بررسی کند.

### Privilege elevation

پس از پرداخت موفق، App نباید Billing Session را به Operational Session تبدیل کند. باید:

```http
POST /wp-json/woogit/v1/billing/activate-session
```

را با Billing Session صدا بزند و token جدید را دریافت کند.

سپس token قبلی نباید برای عملیات operational استفاده شود.

---

## 5. مسیر واحد عملیات Customer — قرارداد Wire-Level

تمام عملیات Customer از endpoint زیر عبور می‌کنند:

```http
GET    /wp-json/woogit/v1/forward?path=...
POST   /wp-json/woogit/v1/forward?path=...
PUT    /wp-json/woogit/v1/forward?path=...
PATCH  /wp-json/woogit/v1/forward?path=...
DELETE /wp-json/woogit/v1/forward?path=...
```

**نکته بسیار مهم:** متد HTTP از خود request (`GET` / `POST` / `PUT` / `PATCH` / `DELETE`) تعیین می‌شود. Backend هیچ فیلد JSON به نام `method` را برای تعیین متد قبول نمی‌کند.

`path` یک query parameter در Backend است. سایر query parameterها نیز به‌صورت query واقعی HTTP ارسال می‌شوند و Backend بعد از حذف `path`، باقی query را به Customer forward می‌کند.

در نتیجه App نباید چنین قراردادی را به‌عنوان wire contract فرض کند:

```json
{
  "path": "/wp-json/wc/v3/products",
  "method": "GET",
  "query": {"page": 1}
}
```

نمونه صحیح wire-level:

```http
GET /wp-json/woogit/v1/forward?path=%2Fwp-json%2Fwc%2Fv3%2Fproducts&page=1&per_page=20&search=phone
X-WooGit-Session: <session-token>
X-WooGit-Consumer-Key: ck_...
X-WooGit-Consumer-Secret: cs_...
```

در این مثال Backend مقدار `path` را مصرف می‌کند و فقط `page`, `per_page` و `search` را به Customer forward می‌کند.

برای `POST`, `PUT` و `PATCH`، payload واقعی Customer در body همان HTTP request قرار می‌گیرد؛ `path` و query داخل body JSON قرار نمی‌گیرند مگر اینکه API Customer خودش چنین فیلدی را بخواهد.

---

## 6. مسیرهای مجاز Customer

### WooCommerce

Backend فعلاً مسیرهایی را که با این prefix منطبق هستند برای forward اجازه می‌دهد:

```text
/wp-json/wc/v3/*
```

بنابراین در قرارداد فعلی، مسیرهای دارای ادامه بعد از `wc/v3/` مجاز هستند؛ روی مسیر bare زیر بدون `/` بعد از `v3` نباید حساب شود:

```text
/wp-json/wc/v3
```

عملیات زیر باید از همین namespace استفاده کنند:

- محصولات
- جزئیات محصول
- ایجاد محصول
- ویرایش محصول
- حذف محصول
- سفارش‌ها
- جزئیات سفارش
- ویرایش سفارش / تغییر وضعیت
- دسته‌بندی محصولات
- موجودی
- مشتریان
- گزارش‌ها و endpointهای WooCommerce که در `wc/v3` قرار دارند
- `system_status`

### WordPress Media

برای Media مسیر مجاز:

```text
/wp-json/wp/v2/media
/wp-json/wp/v2/media/*
```

این مسیر برای upload/read/update/delete رسانه‌هایی است که از WordPress Media API استفاده می‌کنند.

هیچ WordPress REST namespace دیگری فعلاً به‌صورت عمومی از Proxy مجاز نیست.

---

## 7. محصولات

### لیست محصولات

Customer path:

```text
GET /wp-json/wc/v3/products
```

Wire request از App به Backend:

```http
GET /wp-json/woogit/v1/forward?path=%2Fwp-json%2Fwc%2Fv3%2Fproducts
```

### محصول

```text
GET /wp-json/wc/v3/products/{id}
```

### ایجاد

```text
POST /wp-json/wc/v3/products
```

### ویرایش

```text
PUT /wp-json/wc/v3/products/{id}
```

### حذف

```text
DELETE /wp-json/wc/v3/products/{id}
```

تمام این درخواست‌ها باید App → Backend `/forward` → Customer → Backend → App باشند.

Mutationها (`POST/PUT/PATCH/DELETE`) بدون `Idempotency-Key` مجاز نیستند.

---

## 8. سفارش‌ها

### لیست

```text
GET /wp-json/wc/v3/orders
```

### جزئیات

```text
GET /wp-json/wc/v3/orders/{id}
```

### تغییر سفارش

```text
PUT /wp-json/wc/v3/orders/{id}
```

و هر mutation مشابه باید با `Idempotency-Key` انجام شود.

App نباید وضعیت سفارش را محلی و مستقل از Customer به‌عنوان حقیقت اصلی نگهداری کند. Customer WooCommerce منبع اصلی وضعیت سفارش است و Backend فقط Proxy کنترل‌شده آن است.

---

## 9. تصاویر و Media

برای upload تصویر:

```text
POST /wp-json/wp/v2/media
```

برای دریافت/مدیریت Media نیز از namespace بالا استفاده شود.

### Credential مخصوص Media

App باید credentialهای WordPress را در headerهای اختصاصی Backend بفرستد:

```http
X-WooGit-Wordpress-Username: ...
X-WooGit-Wordpress-Application-Password: ...
```

برای WooCommerce API باید credentialهای WooCommerce ارسال شوند:

```http
X-WooGit-Consumer-Key: ...
X-WooGit-Consumer-Secret: ...
```

Backend بر اساس Customer path تشخیص می‌دهد که درخواست Media است یا WooCommerce و credential مناسب را برای Customer می‌سازد.

---

## 10. Query Parameters

Query مربوط به Customer باید به‌صورت query parameter واقعی در request به `/forward` ارسال شود.

`path` نیز query parameter مخصوص Backend است و نباید به Customer منتقل شود. Backend خودش `path` را از query جدا کرده و فقط query واقعی Customer را forward می‌کند.

**نمونه صحیح:**

```http
GET /wp-json/woogit/v1/forward?path=%2Fwp-json%2Fwc%2Fv3%2Fproducts&page=1&per_page=20&search=phone
```

در این request:

```text
Backend path parameter: path=/wp-json/wc/v3/products
Customer query: page=1&per_page=20&search=phone
```

بنابراین JSON زیر صرفاً می‌تواند یک مدل داخلی UI/Repository باشد، نه wire contract با Backend:

```json
{
  "path": "/wp-json/wc/v3/products",
  "method": "GET",
  "query": {
    "page": 1,
    "per_page": 20,
    "search": "phone"
  }
}
```

---

## 11. Body و Content-Type

برای `POST`, `PUT`, `PATCH`، App باید body واقعی API Customer را ارسال کند و `Content-Type` صحیح را حفظ کند.

برای JSON معمولاً:

```http
Content-Type: application/json
```

Backend body را با سقف فعلی درخواست کنترل می‌کند: سقف معمول 5 MiB و برای مسیرهای WordPress Media تا 10 MiB است.

App نباید payloadهای غیرضروری یا credentialها را داخل body قرار دهد.

برای Media upload باید قرارداد multipart/form-data موجود در Client networking layer رعایت شود و body خام request خراب یا JSON-encode نشود.

برای `DELETE`، Backend در proxy فعلی body را به Customer forward نمی‌کند؛ بنابراین App نباید برای DELETE روی ارسال body حساب کند.

---

## 12. Pagination و Response Headers

برای collectionهایی مانند products و orders، App باید pagination را از response Backend بخواند.

Backend headerهای منتخب لازم را عبور می‌دهد، از جمله:

```text
X-WP-Total
X-WP-TotalPages
Content-Type
```

App نباید تعداد کل صفحات را حدس بزند.

Backend عمداً headerهایی مانند upstream `Location` را به App عبور نمی‌دهد.

---

## 13. Mutation و Idempotency

برای هر عملیات ایجاد/ویرایش/حذف:

```text
POST
PUT
PATCH
DELETE
```

App باید یک `Idempotency-Key` یکتا برای همان logical operation ایجاد کند.

نمونه:

```http
Idempotency-Key: <unique-operation-key>
```

اگر request به علت timeout پاسخ نگرفت، App نباید همان عملیات را با key جدید دوباره اجرا کند؛ ابتدا باید operation قبلی را reconcile کند.

Key باید برای retry همان logical operation حفظ شود و نباید با هر تلاش مجدد تغییر کند.

Backend ممکن است خطاهای زیر را در این مسیر برگرداند:

```text
400 invalid_idempotency_key → کلید Idempotency نامعتبر است
400 invalid_mutation_request → mutation شرایط لازم را ندارد
409 idempotency_conflict → همان key با fingerprint متفاوت استفاده شده است
202 operation_in_progress → عملیات قبلی هنوز در حال پردازش است
500 operation_unavailable → وضعیت/عملیات از Backend قابل بازیابی نیست
```

App باید برای `idempotency_conflict` هرگز همان key را با payload یا request متفاوت reuse نکند.

---

## 14. Timeout-after-success و Reconciliation

این سناریو حیاتی است:

```text
App → Backend → Customer
                    │
                    │ operation انجام شد
                    ▼
                 Customer
                    │
                    X response lost/timeout
                    │
                    ▼
                 Backend
                    │
                 UNKNOWN
                    │
                    ▼
                   App
```

در چنین وضعیتی App نباید فرض کند create/update/delete شکست خورده است.

اگر `operation_id` دریافت شده باشد، App باید وضعیت را از این endpoint پیگیری کند:

```http
GET /wp-json/woogit/v1/operations/{operation_id}
X-WooGit-Session: <session-token>
```

این endpoint Backend-side است و خودش مستقیماً به Customer request نمی‌فرستد.

در mutationای که با `504 upstream_timeout` یا وضعیت unknown مواجه شده، retry با `Idempotency-Key` جدید ممنوع است.

---

## 15. Error Handling

App باید status code و error body Backend را جدی بگیرد و صرفاً با متن خطا تصمیم نگیرد.

موارد مهم:

```text
400 → request/validation/idempotency مشکل دارد
401 → Session/authentication مشکل دارد
403 → scope / entitlement / ownership اجازه نمی‌دهد
404 → resource یا operation پیدا نشد
409 → Idempotency conflict یا state conflict
413 → request_body_too_large؛ body از سقف Backend بزرگ‌تر است
429 → rate limit
502 → upstream/network failure
503 → operation persistence failure
504 → upstream timeout؛ برای mutation ممکن است عملیات در Customer انجام شده باشد
```

در `504` مربوط به mutation، امکان انجام‌شدن عملیات وجود دارد؛ بنابراین retry با Idempotency-Key جدید ممنوع است.

---

## 16. Billing Flow

Billing با Customer WooCommerce فرق دارد و endpointهای آن در خود Backend اجرا می‌شوند.

### Plans

```http
GET /wp-json/woogit/v1/billing/plans
```

این endpoint در Backend فعلی Session نمی‌خواهد.

### Status

```http
GET /wp-json/woogit/v1/billing/status
```

این endpoint به Session معتبر نیاز دارد، اما برای بررسی وضعیت Billing به Entitlement فعال نیاز ندارد.

### Checkout

```http
POST /wp-json/woogit/v1/billing/checkout
```

این endpoint به هر Session معتبر Account نیاز دارد و هویت Account/Site را از Session می‌گیرد؛ App نباید `account_id` یا `site_id` را به‌عنوان هویت قابل اعتماد از body تعیین کند.

### Activate operational session

```http
POST /wp-json/woogit/v1/billing/activate-session
```

این endpoint فقط با Billing Session قابل استفاده است و پس از احراز Entitlement عملیاتی، token جدید صادر می‌کند.

این endpointها مستقیماً Customer Site را proxy نمی‌کنند.

پرداخت توسط WooCommerce/WooCommerce Subscriptions در Backend معتبر شناخته می‌شود؛ App نباید صرفاً با اعلام موفقیت پرداخت، Entitlement یا Session را محلی فعال کند.

پس از پرداخت موفق، App باید وضعیت Billing را refresh کند و در صورت نیاز `activate-session` را صدا بزند تا token عملیاتی جدید دریافت شود.

---

## 17. تفکیک Credentialها

Credentialهای Customer دو دسته‌اند:

### WordPress

```text
X-WooGit-Wordpress-Username
X-WooGit-Wordpress-Application-Password
```

فقط برای:

```text
/wp-json/wp/v2/media
/wp-json/wp/v2/media/*
```

### WooCommerce

```text
X-WooGit-Consumer-Key
X-WooGit-Consumer-Secret
```

برای:

```text
/wp-json/wc/v3/*
```

Backend این credentialها را request-scoped مصرف می‌کند و آن‌ها را به‌عنوان credentialهای دائمی Session ذخیره نمی‌کند.

App نیز نباید credentialها را در log، analytics، crash report یا operation payload ذخیره کند.

---

## 18. Store Connection در App

اتصال Store در App نباید با request مستقیم به Customer انجام شود.

هر چیزی مشابه این الگو باید حذف/جایگزین شود:

```kotlin
httpClient.get("$customerUrl/wp-json/wc/v3/...")
```

الگوی صحیح:

```text
App
  ↓
POST /wp-json/woogit/v1/sites/verify
  ↓
Backend verifies Customer credentials
  ↓
Backend resolves Account + Site
  ↓
Backend issues billing/operational Session
  ↓
App
```

پس از اتصال نیز تمام Products / Orders / Media / WooCommerce operations باید از `/forward` عبور کنند.

---

## 19. Checklist پیاده‌سازی App

- [ ] هیچ request مستقیمی به Customer URL در App وجود نداشته باشد.
- [ ] Store Connection فقط از `/sites/verify` استفاده کند.
- [ ] Backend Base URL و Customer Site URL در لایه شبکه از هم تفکیک شوند.
- [ ] برای `/forward` متد HTTP واقعی request استفاده شود و `method` به‌عنوان فیلد JSON ارسال نشود.
- [ ] `path` به‌عنوان query parameter `/forward` ارسال شود.
- [ ] Queryهای Customer به‌صورت query parameter واقعی ارسال شوند.
- [ ] Body واقعی Customer برای POST/PUT/PATCH حفظ شود.
- [ ] Media upload به‌صورت multipart/form-data خراب نشود.
- [ ] Credentialهای WordPress فقط برای Media و credentialهای WooCommerce فقط برای `wc/v3` استفاده شوند.
- [ ] `X-WooGit-Session` در endpointهای نیازمند Session ارسال شود.
- [ ] Billing Session برای Customer operation استفاده نشود.
- [ ] بعد از پرداخت، Billing Session mutate نشود؛ token جدید از `activate-session` گرفته شود.
- [ ] Mutationها همیشه `Idempotency-Key` داشته باشند.
- [ ] Retry همان logical mutation با همان Idempotency-Key انجام شود.
- [ ] در timeout/unknown، operation reconciliation انجام شود.
- [ ] `X-WP-Total` و `X-WP-TotalPages` برای pagination خوانده شوند.
- [ ] Error handling بر اساس status/code انجام شود، نه فقط متن.
- [ ] `413 request_body_too_large` به‌درستی مدیریت شود.
- [ ] خطاهای Idempotency و `operation_in_progress`/`operation_unavailable` به‌درستی مدیریت شوند.
- [ ] Session منقضی/revoked هرگز معتبر فرض نشود.
- [ ] پس از expiry، Session جدید طبق قرارداد احراز هویت ایجاد شود و Backend مجدداً Account + Site Ownership + Entitlement را بررسی کند.

---

## 20. نکته امنیتی نهایی

App نباید Session منقضی‌شده را به‌عنوان مجوز معتبر نگه دارد یا صرفاً با داشتن token قبلی، دسترسی operational را ادامه دهد. اطلاعات لازم برای ایجاد Session جدید باید طبق قرارداد احراز هویت موجود ارسال شود و Backend در ایجاد Session جدید دوباره همه کنترل‌های لازم شامل Account، Site Ownership و در مسیر operational، Entitlement را بررسی کند.

این سند قرارداد App ↔ Backend است؛ هر implementation جدید در App باید قبل از merge با آن تطبیق داده شود.
