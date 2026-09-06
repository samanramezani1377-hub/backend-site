# APP CHANGE — قرارداد تطبیق اپ با WooGit Backend

> هدف این سند: این فایل قرارداد اجرایی بین WooGit Android App و `backend-site` است. هر تغییری در لایه شبکه، Repository، Session، Store Connection یا عملیات WooCommerce در App باید با این سند تطبیق داده شود.

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

تنها استثنای ارتباط App با Customer، نباید وجود داشته باشد؛ حتی GETهای ساده محصولات، سفارش‌ها یا تصاویر نیز باید از Backend عبور کنند.

---

## 2. Base URLها

App باید دو مفهوم جدا داشته باشد:

### Backend Base URL

آدرس Backend نصب‌شده WooGit. تمام APIهای WooGit از این Base URL استفاده می‌کنند:

```text
/wp-json/woogit/v1/...
```

### Customer Site URL

آدرس سایت مشتری فقط به‌عنوان هویت Site در Backend نگهداری می‌شود و App نباید آن را به‌عنوان مقصد مستقیم API استفاده کند.

هر request عملیاتی به Customer باید به Backend ارسال شود و `path` مقصد Customer به‌صورت داده request در `/forward` قرار گیرد.

---

## 3. اتصال اولیه Site

Endpoint:

```http
POST /wp-json/woogit/v1/sites/verify
```

Request باید شامل موارد زیر باشد:

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

Response ممکن است یکی از این scopeها را داشته باشد:

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

یا:

```json
{
  "scope": "billing",
  "access_enabled": false,
  "billing_required": true
}
```

اگر scope برابر `billing` است، App نباید هیچ عملیات Customer را با آن Session اجرا کند.

---

## 4. Session Contract

برای تمام endpointهای operational، App باید:

```http
X-WooGit-Session: <session-token>
```

را ارسال کند.

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

## 5. مسیر واحد عملیات Customer

تمام عملیات Customer از endpoint زیر عبور می‌کنند:

```http
GET|POST|PUT|PATCH|DELETE /wp-json/woogit/v1/forward
```

Request باید شامل حداقل این مفهوم‌ها باشد:

```text
path
method
query/body در صورت نیاز
X-WooGit-Session
Customer credential headers
Idempotency-Key برای mutationها
```

Backend خودش Customer Site URL را از Site identity resolve می‌کند. App نباید مقصد نهایی Customer را در URL request جایگزین Backend کند.

---

## 6. مسیرهای مجاز Customer

### WooCommerce

Backend فعلاً این namespace را برای forward اجازه می‌دهد:

```text
/wp-json/wc/v3/*
```

بنابراین عملیات زیر باید از همین مسیر استفاده کنند:

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

```text
GET /wp-json/wc/v3/products
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

Backend بر اساس path تشخیص می‌دهد که درخواست Media است یا WooCommerce.

---

## 10. Query Parameters

Query مربوط به Customer باید داخل request به `/forward` ارسال شود؛ App نباید query را به URL Backend به‌صورت دلخواهی بچسباند مگر اینکه Client networking contract آن را صریحاً تعریف کرده باشد.

`path` نباید داخل query به Customer منتقل شود. Backend خودش `path` را از query جدا کرده و فقط query واقعی Customer را forward می‌کند.

نمونه مفهومی:

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

Backend body را با سقف فعلی درخواست کنترل می‌کند. App نباید payloadهای غیرضروری یا credentialها را داخل body قرار دهد.

برای Media upload باید قرارداد multipart/form-data موجود در Client networking layer رعایت شود و body خام request خراب یا JSON-encode نشود.

---

## 12. Pagination

برای collectionهایی مانند products و orders، App باید pagination را از response Backend بخواند.

Backend headerهای pagination لازم WooCommerce/WordPress را عبور می‌دهد، از جمله:

```text
X-WP-Total
X-WP-TotalPages
```

App نباید تعداد کل صفحات را حدس بزند.

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

---

## 14. Timeout-after-success

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
```

این endpoint Backend-side است و خودش مستقیماً به Customer request نمی‌فرستد.

---

## 15. Error Handling

App باید status code و error body Backend را جدی بگیرد و صرفاً با متن خطا تصمیم نگیرد.

موارد مهم:

```text
401 → Session/authentication مشکل دارد
403 → scope / entitlement / ownership اجازه نمی‌دهد
404 → resource یا operation پیدا نشد
409 → Idempotency conflict یا state conflict
429 → rate limit
502/504 → upstream/timeout
```

در `504` مربوط به mutation، امکان انجام‌شدن عملیات وجود دارد؛ بنابراین retry با Idempotency-Key جدید ممنوع است.

---

## 16. Billing Flow

Billing با Customer WooCommerce فرق دارد و در Backend انجام می‌شود.

### Plans

```http
GET /wp-json/woogit/v1/billing/plans
```

### Status

```http
GET /wp-json/woogit/v1/billing/status
```

### Checkout

```http
POST /wp-json/woogit/v1/billing/checkout
```

### Activate operational session

```http
POST /wp-json/woogit/v1/billing/activate-session
```

این endpointها به‌صورت مستقیم Customer Site را Proxy نمی‌کنند.

بعد از پرداخت موفق، Backend از وضعیت WooCommerce/WooCommerce Subscriptions برای فعال‌سازی Entitlement استفاده می‌کند.

---

## 17. Session Revoke

برای logout/disconnect session:

```http
POST /wp-json/woogit/v1/sessions/revoke
```

پس از revoke، App نباید token را دوباره استفاده کند.

---

## 18. Disconnect با Customer Site فرق دارد

Disconnect در App نباید به معنی حذف Account یا حذف Site در Backend تلقی شود.

App باید local credential/reference خود را نیز پاک کند و session lifecycle را مطابق Backend contract مدیریت کند.

---

## 19. ممنوعیت Direct Customer Calls

در App این الگوها ممنوع هستند:

```kotlin
httpClient.get("$customerUrl/wp-json/wc/v3/products")
httpClient.get("$customerUrl/wp-json/wc/v3/orders")
httpClient.post("$customerUrl/wp-json/wp/v2/media")
```

همچنین ممنوع است که repositoryهای Product/Order/Image مستقیماً `customerUrl` را مقصد HTTP قرار دهند.

الگوی صحیح:

```text
ProductRepository
      ↓
BackendClient
      ↓
POST/GET /wp-json/woogit/v1/forward
      ↓
Backend
      ↓
Customer
```

---

## 20. Credential Security

Credentialهای Customer نباید:

- داخل URL باشند
- داخل query parameter باشند
- داخل log چاپ شوند
- داخل analytics/crash report ذخیره شوند
- داخل response App برگردانده شوند
- در مدل Session قرار گیرند

App باید credentialها را فقط از Secure Credential Store بخواند و در زمان request به Backend ارسال کند.

Credentialها request-scoped هستند.

---

## 21. StoreRepository / Connection

Connection اولیه نباید برای عملیات معمول Customer به direct HTTP تبدیل شود.

اگر App برای health check یا connection verification از Customer endpoint استفاده می‌کند، باید این رفتار با قرارداد جدید Backend یکسان‌سازی شود.

**نکته مهم فعلی برای App:** در کد فعلی App، `StoreRepositoryImpl.connect()` مستقیماً به Customer می‌رود و `GET /wp-json/wc/v3/system_status` را صدا می‌زند. این رفتار با معماری Backend Proxy منطبق نیست و باید در App اصلاح شود تا verification از `/woogit/v1/sites/verify` انجام شود. این مورد عمداً در این سند به‌عنوان تغییر لازم ثبت شده است.

---

## 22. Mapping عملیاتی App

| قابلیت App | Customer Path | Method | Backend Route | Credential | Idempotency |
|---|---|---|---|---|---|
| Products list | `/wp-json/wc/v3/products` | GET | `/woogit/v1/forward` | WC | No |
| Product detail | `/wp-json/wc/v3/products/{id}` | GET | `/woogit/v1/forward` | WC | No |
| Create product | `/wp-json/wc/v3/products` | POST | `/woogit/v1/forward` | WC | Yes |
| Update product | `/wp-json/wc/v3/products/{id}` | PUT | `/woogit/v1/forward` | WC | Yes |
| Delete product | `/wp-json/wc/v3/products/{id}` | DELETE | `/woogit/v1/forward` | WC | Yes |
| Product categories | `/wp-json/wc/v3/products/categories` | GET/POST/etc. | `/woogit/v1/forward` | WC | mutation: Yes |
| Orders list | `/wp-json/wc/v3/orders` | GET | `/woogit/v1/forward` | WC | No |
| Order detail | `/wp-json/wc/v3/orders/{id}` | GET | `/woogit/v1/forward` | WC | No |
| Update order | `/wp-json/wc/v3/orders/{id}` | PUT | `/woogit/v1/forward` | WC | Yes |
| Media list/detail | `/wp-json/wp/v2/media*` | GET | `/woogit/v1/forward` | WP | No |
| Media upload | `/wp-json/wp/v2/media` | POST | `/woogit/v1/forward` | WP | Yes |
| Billing plans | Backend-only | GET | `/woogit/v1/billing/plans` | Session not necessarily operational | No |
| Billing status | Backend-only | GET | `/woogit/v1/billing/status` | Session | No |
| Checkout | Backend-only | POST | `/woogit/v1/billing/checkout` | Billing Session | N/A |
| Activate session | Backend-only | POST | `/woogit/v1/billing/activate-session` | Billing Session | N/A |
| Revoke session | Backend-only | POST | `/woogit/v1/sessions/revoke` | Session | N/A |
| Operation status | Backend-only | GET | `/woogit/v1/operations/{id}` | Operational Session | No |

---

## 23. Network Layer Requirements

تمام repositoryهای operational باید از یک abstraction مشترک استفاده کنند؛ ترجیحاً چیزی در سطح:

```text
BackendClient
   └── forward(...)
```

نه اینکه هر repository خودش header/session/URL را بسازد.

این abstraction باید مسئول موارد زیر باشد:

1. Backend base URL
2. Session header
3. Customer credential headers
4. Forward path
5. HTTP method
6. Query
7. Body
8. Content-Type
9. Idempotency-Key
10. Response parsing
11. 401/403/429/502/504 handling
12. operation reconciliation

---

## 24. Definition of Done برای App Change

تطبیق App با Backend فقط زمانی کامل محسوب می‌شود که:

- [ ] هیچ Product API مستقیماً به Customer URL نرود.
- [ ] هیچ Order API مستقیماً به Customer URL نرود.
- [ ] هیچ Image/Media API مستقیماً به Customer URL نرود.
- [ ] Category/Customer/Inventory و سایر WooCommerce APIها نیز از `/forward` عبور کنند.
- [ ] Connection verification از `/sites/verify` استفاده کند.
- [ ] Session در همه operational requestها ارسال شود.
- [ ] Session expired/revoked دوباره استفاده نشود.
- [ ] Billing Session برای Customer operations استفاده نشود.
- [ ] بعد از پرداخت، Operational Session جدید از `activate-session` گرفته شود.
- [ ] تمام mutationها Idempotency-Key داشته باشند.
- [ ] retry بعد از timeout با همان Idempotency-Key انجام شود.
- [ ] timeout-after-success به‌عنوان failure قطعی نمایش داده نشود.
- [ ] operation reconciliation پیاده باشد.
- [ ] pagination headers از Backend مصرف شود.
- [ ] Customer credentials در URL/query/log ذخیره نشوند.
- [ ] Media با WordPress credentials ارسال شود.
- [ ] WooCommerce API با WC credentials ارسال شود.
- [ ] هیچ direct call به `$customerUrl/wp-json/...` در لایه operational باقی نماند.

---

## 25. وضعیت فعلی تطبیق

### Backend

ساختار Proxy، Session، Site ownership، Entitlement، Idempotency و Operation tracking برای این قرارداد در Backend وجود دارد.

### App

بررسی فعلی نشان داده است که لایه Store Connection هنوز یک direct call به Customer دارد:

```text
StoreRepositoryImpl.connect()
    → GET {customer}/wp-json/wc/v3/system_status
```

این مورد باید به verification Backend منتقل شود.

همچنین تمام repositoryهای Product/Order/Image و سایر repositoryهای operational باید با این سند تطبیق داده شوند تا هیچ direct Customer HTTP call باقی نماند.

---

## 26. قانون تغییرات آینده

هر endpoint جدید Customer قبل از اضافه‌شدن به App باید:

1. در `ProxyPolicy` Backend مجاز شده باشد.
2. credential type آن مشخص باشد.
3. method و idempotency requirement آن مشخص باشد.
4. در این سند ثبت شود.
5. در App از `BackendClient.forward()` استفاده کند.
6. direct Customer URL در App نداشته باشد.

هر تغییری که این مسیر را دور بزند، تغییر معماری محسوب می‌شود و نباید صرفاً در یک repository یا UI layer پیاده شود.

---

## 27. خلاصه یک‌خطی قرارداد

```text
App → WooGit Backend → Customer WooCommerce → WooGit Backend → App
```

و **هیچ عملیات Customer نباید مسیر Backend را دور بزند.**
