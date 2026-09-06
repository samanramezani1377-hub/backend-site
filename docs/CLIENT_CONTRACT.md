# قرارداد Client ↔ Backend ووگیت

> وضعیت این سند: **Baseline / Locked Reference**
>
> این سند بر اساس پیاده‌سازی فعلی اپ Android WooGit تهیه شده است. هرجا رفتار فعلی اپ با معماری هدف Backend تفاوت دارد، تفاوت صریحاً با برچسب «فعلی» و «هدف» مشخص شده است.

## ۱. اصل مهم معماری

اپ Android ووگیت از قبل ساخته شده و Client مستقل است. Backend این repository نباید UI، Navigation، State Management یا معماری داخلی اپ را بازسازی کند.

مرجع Client:

- `samanramezani1377-hub/woogit`

مرجع Backend:

- `samanramezani1377-hub/backend-site`

قرارداد نهایی API باید با پیاده‌سازی واقعی Client تطبیق داده شود و صرفاً بر اساس معماری پیشنهادی نوشته نشود.

## ۲. توپولوژی فعلی اپ

در وضعیت فعلی، Backend هنوز در مسیر ارتباطی اپ قرار نگرفته است:

```text
WooGit Android
      │
      │ HTTPS / HTTP
      │ WooCommerce REST
      │ WordPress REST
      ▼
Customer WordPress / WooCommerce
```

بنابراین در کد فعلی Client:

- App-to-Backend authentication وجود ندارد.
- WooGit Session برای Backend هنوز وجود ندارد.
- اعتبارهای WooCommerce/WordPress در Client برای اتصال مستقیم استفاده می‌شوند.

این موارد **نباید به‌عنوان قابلیت موجود گزارش شوند**؛ WooGit Session بخشی از Migration Target است.

## ۳. اتصال فعلی Client

صفحه Connection فعلی این ورودی‌ها را می‌گیرد:

1. پروتکل `HTTP/HTTPS`
2. دامنه/آدرس فروشگاه
3. WooCommerce Consumer Key
4. WooCommerce Consumer Secret
5. WordPress Username
6. WordPress Application Password

`WordPress Application Password` با رمز معمول ورود به `wp-admin` یکی نیست و باید در تمام قراردادهای Backend همین معنا را داشته باشد.

Client فعلی این اطلاعات را به لایه اتصال فروشگاه می‌دهد و Repository مستقیماً endpoint زیر را روی سایت مشتری صدا می‌زند:

```text
/wp-json/wc/v3/system_status
```

پس موفقیت اتصال فعلی بر اساس دسترسی واقعی به WordPress/WooCommerce سنجیده می‌شود.

## ۴. Store ID فعلی Client

Client فعلی برای Store یک شناسه محلی بر اساس URL فروشگاه تولید می‌کند؛ الگوی آن به‌صورت مفهومی چنین است:

```text
store-{hash(canonicalized-url)}
```

این شناسه:

- Local Client ID است.
- نباید بدون Migration Mapping به‌عنوان Backend `site_id` پذیرفته شود.
- نباید منبع حقیقت مالکیت سایت باشد.

Backend باید Site Identity مستقل و authoritative خود را داشته باشد و در Migration یک mapping صریح بین Client Store ID و Backend Site Identity ایجاد شود.

## ۵. اصل جدید و اجباری: اولین درخواست Backend به سایت مشتری

**اولین درخواست واقعی Backend به هر سایت مشتری باید یک درخواست Verification/Connection باشد، نه درخواست Products، Orders یا سایر عملیات تجاری.**

هدف این مرحله این است که Backend ابتدا ثابت کند:

1. سایت مشتری از نظر شبکه و HTTPS قابل دسترسی است؛
2. WordPress REST API در دسترس است؛
3. Credential مربوط به WordPress معتبر است؛
4. WordPress هویت/دسترسی ارائه‌شده را می‌پذیرد؛
5. سپس WooCommerce قابل دسترسی و احراز هویت است؛
6. پس از موفقیت کامل، Site Identity می‌تواند ایجاد یا به سایت موجود متصل شود.

### ۵.۱ ترتیب Verification

Flow استاندارد اتصال:

```text
Client credentials
       ↓
WooGit Backend
       ↓
[1] WordPress reachability
       ↓
[2] WordPress authentication
       ↓
[3] WordPress identity/access verification
       ↓
[4] WooCommerce authentication/availability
       ↓
[5] Site Identity verification/creation
       ↓
[6] Session / onboarding continuation
       ↓
Products / Orders / Customers / Media
```

**هیچ عملیات تجاری نباید قبل از موفقیت Verification Flow اجرا شود.**

### ۵.۲ Verification مربوط به WordPress

برای Credentialهای WordPress، Backend باید ابتدا یک درخواست read-only به WordPress REST API ارسال کند. در صورت استفاده از WordPress Application Password، این Credential باید از طریق HTTPS به REST API ارائه شود.

نمونه مفهومی:

```http
GET https://customer-site.example/wp-json/wp/v2/users/me
Authorization: Basic <username:application-password>
```

موفقیت این درخواست نشان می‌دهد که در سطح WordPress:

```text
WordPress reachable       ✓
HTTPS                     ✓
Credentials valid         ✓
WordPress authentication  ✓
```

این مرحله باید **قبل از هر درخواست تجاری WooCommerce** انجام شود.

### ۵.۳ Verification مربوط به WooCommerce

پس از موفقیت WordPress verification، Backend باید دسترسی WooCommerce را جداگانه بررسی کند.

نمونه:

```http
GET https://customer-site.example/wp-json/wc/v3/system_status
```

موفقیت این مرحله نشان می‌دهد که:

```text
WooCommerce reachable       ✓
WooCommerce authenticated    ✓
WooCommerce API usable       ✓
```

این درخواست همان endpointی است که Client فعلی نیز برای اعتبارسنجی اتصال مستقیم استفاده می‌کند و باید در Migration Contract با رفتار واقعی Client تطبیق داده شود.

### ۵.۴ Verification نباید mutation باشد

Connection Verification باید تا حد امکان read-only باشد.

Verification نباید برای اثبات اتصال، محصول/سفارش/رسانه‌ای ایجاد یا تغییر دهد.

بنابراین این موارد در Verification ممنوع‌اند:

- CREATE product
- CREATE order
- CREATE media صرفاً برای تست
- UPDATE product
- DELETE هر resource

### ۵.۵ خطاها باید مرحله مشخص داشته باشند

Backend نباید تمام خطاهای اتصال را به یک خطای مبهم تبدیل کند.

حداقل stageهای منطقی:

```text
network
https
wordpress
wordpress_auth
woocommerce
woocommerce_auth
site_identity
```

نمونه خطای WordPress:

```json
{
  "code": "WORDPRESS_CONNECTION_FAILED",
  "stage": "wordpress",
  "message": "اتصال به سایت وردپرس برقرار نشد.",
  "retryable": true
}
```

نمونه خطای Authentication:

```json
{
  "code": "WORDPRESS_AUTH_FAILED",
  "stage": "wordpress_auth",
  "message": "اطلاعات احراز هویت وردپرس معتبر نیست.",
  "retryable": false
}
```

نمونه خطای WooCommerce:

```json
{
  "code": "WOOCOMMERCE_CONNECTION_FAILED",
  "stage": "woocommerce",
  "message": "اتصال به WooCommerce برقرار نشد.",
  "retryable": true
}
```

پیام عمومی نباید Secret، Application Password، Consumer Secret، stack trace یا جزئیات حساس زیرساخت را افشا کند.

## ۶. نتیجه Verification و Onboarding

### ۶.۱ Verification ناموفق

```text
Client credentials
    ↓
Backend Verification
    ↓
FAIL
```

در این حالت:

- Account جدید صرفاً به خاطر این تلاش اتصال ساخته نمی‌شود.
- Trial فعال نمی‌شود.
- Session نهایی ساخته نمی‌شود.
- Dashboard unlock نمی‌شود.
- Site Identity به‌عنوان اتصال موفق ثبت نمی‌شود.

### ۶.۲ Site Identity موجود

```text
Client credentials
    ↓
WordPress verification
    ↓
WooCommerce verification
    ↓
existing Site Identity
    ↓
existing site account
    ↓
WooGit session
    ↓
Dashboard
```

### ۶.۳ Site Identity جدید

```text
Client credentials
    ↓
WordPress verification
    ↓
WooCommerce verification
    ↓
new Site Identity
    ↓
second in-app page
    ↓
email + first name + last name
    ↓
account creation
    ↓
trial eligibility check
    ↓
WooGit session
    ↓
Dashboard
```

صفحه دوم فقط بعد از Verification موفق فعال می‌شود.

## ۷. WooGit Session — قرارداد هدف

پس از Onboarding، Client برای عملیات عادی یک **WooGit Session معتبر** را همراه درخواست ارسال می‌کند.

WooGit Session تنها مکانیزم احراز هویت و مجوز مصرف‌کننده در WooGit Backend است. این قرارداد از مدل جداگانه Access Token + Refresh Token استفاده نمی‌کند.

```text
WooGit Session
    → احراز هویت و مجوز Client در Backend

Customer Credentials
    → احراز دسترسی Backend نزد Customer Site
```

Backend باید برای Session حداقل این وضعیت‌ها را پشتیبانی کند:

- معتبر / نامعتبر بودن Session
- expiration
- revocation
- اتصال Session به Account و در صورت نیاز Site context
- logout / پایان Session
- ثبت زمان آخرین استفاده در صورت نیاز عملیاتی

درخواست عادی بدون WooGit Session معتبر نباید از کنترل‌های Backend عبور کند.

این Session با Credentialهای سایت مشتری متفاوت است و جایگزین WordPress Application Password یا WooCommerce Consumer Credentials نیست.

## ۸. عدم نگهداری Customer Credential در Backend

در V1، Backend **هیچ Customer Credentialای را در DB، Vault، Cache پایدار یا هر storage دائمی نگهداری نمی‌کند**.

الگوی اجباری:

```text
Client
  → WooGit Session + site_id + Customer Credentials
  → Backend
  → Customer WordPress / WooCommerce
```

Credentialهای Customer فقط برای همان Request مصرف می‌شوند و پس از پایان پردازش نباید به‌عنوان Credential پایدار نگهداری یا برای Request یا Site دیگری reuse شوند.

Backend نباید:

- Customer Credential را در DB ذخیره کند؛
- برای Customer Credential، Vault یا secret storage پایدار داشته باشد؛
- Customer Credential را در Cache پایدار نگهداری کند؛
- Customer Credential را در Log/Telemetry/Crash Report/Audit ذخیره کند؛
- Customer Credential را در Error/Response برگرداند؛
- Customer Credential را برای Request یا Site دیگری reuse کند.

هر قابلیت آینده‌ای که به Credential پایدار نیاز داشته باشد خارج از این قرارداد V1 است و نمی‌تواند با فرض وجود Credential Storage در Backend طراحی شود.

## ۹. عملیات Client که باید در Migration پوشش داده شوند

Backend API باید در نهایت عملیات واقعی Client را پوشش دهد، نه اینکه یک API انتزاعی بدون مصرف‌کننده واقعی ساخته شود.

حداقل حوزه‌ها:

| حوزه | وضعیت فعلی Client | هدف Backend |
|---|---|---|
| Connection | مستقیم به WP/WC | Backend Gateway |
| Store Identity | Local Store ID | Authoritative Site Identity |
| Products | WooCommerce REST | Typed Backend operation |
| Orders | WooCommerce REST | Typed Backend operation |
| Customers | WooCommerce REST در صورت استفاده | Typed Backend operation |
| Categories | WooCommerce REST | Typed Backend operation |
| Media | WordPress media flow | Backend upload/media operation |
| Product mutations | مستقیم | Backend + idempotency |
| Order mutations | مستقیم | Backend + idempotency |
| Sync | Client repository/sync layer | Backend-aware sync/reconciliation |
| Pending mutations | Local queue | Backend-compatible operation identity |
| Errors | HTTP/API errors از WP/WC | استاندارد Backend error contract |

این جدول باید در زمان پیاده‌سازی هر API با کد واقعی Client دوباره validate شود.

## ۱۰. Media Contract

Flow فعلی Client برای رسانه به مدل WordPress/WooCommerce وابسته است و در Migration نباید با یک abstraction ناسازگار جایگزین شود.

هدف Backend:

```text
Client image
   -> Backend upload
   -> WordPress media
   -> Media ID + URL
   -> product/media association
```

Backend باید وضعیت upload را طوری طراحی کند که retry و timeout باعث ایجاد رسانه تکراری نشود.

## ۱۱. Idempotency و Timeout-after-success

هر mutation دارای side effect که از Client به Backend منتقل می‌شود باید operation identity پایدار داشته باشد.

حداقل سناریوی اجباری:

```text
Client -> CREATE request
Backend -> remote WooCommerce success
Backend -> response lost / timeout
Client -> retry same operation
Backend -> detect previous operation
Backend -> return previous result
```

هدف این است که timeout در شبکه هرگز به معنی «عملیات انجام نشده» فرض نشود.

این قرارداد برای mutationهای CREATE و سایر عملیات non-idempotent باید بخشی از API contract باشد.

## ۱۲. Sync و Reconciliation

با توجه به Local-first بودن Client، Backend نباید صرفاً یک proxy ساده باشد.

برای عملیات ambiguous باید امکان reconciliation وجود داشته باشد:

```text
operation_id
   -> operation state
   -> remote result / known resource
   -> final canonical state
```

Client باید بتواند پس از restart، timeout یا retry وضعیت نهایی operation را دریافت کند.

## ۱۳. Error Contract

Backend باید error response استاندارد داشته باشد و Client نباید مجبور شود متن خطای خام WordPress/PHP را parse کند.

حداقل فیلدهای پیشنهادی:

```json
{
  "code": "STORE_CONNECTION_FAILED",
  "message": "اتصال به فروشگاه انجام نشد.",
  "request_id": "...",
  "operation_id": "...",
  "retryable": false
}
```

Secret، Consumer Secret، Application Password و stack trace هرگز نباید در response عمومی قرار گیرند.

## ۱۴. Currency Contract

واحد پول فروشگاه باید از داده canonical فروشگاه/Commerce به Client برسد و Backend در صورت تبدیل شدن به Gateway نباید آن را حذف کند.

برای محصولات، سفارش‌ها و سایر داده‌های مالی، response باید currency یا currency context مورد نیاز Client را حفظ کند.

Backend نباید فرض کند واحد پول همیشه یک مقدار ثابت مانند USD یا EUR است.

## ۱۵. Pagination / Filtering / Sorting

برای collection APIها قرارداد باید صریح باشد:

- page / cursor یا الگوی واحد انتخاب‌شده
- page size
- sort
- filters
- total/has-more در صورت پشتیبانی
- stable ordering

Backend باید این قرارداد را طوری ارائه کند که با listهای محصولات و سفارش‌های Client قابل sync باشد.

## ۱۶. Migration Rules

تا زمان مهاجرت کامل Client:

1. Backend API نباید به‌عنوان API موجود Client فرض شود.
2. هر endpoint جدید باید مصرف‌کننده مشخص در Client داشته باشد.
3. نام‌گذاری مدل‌های Backend نباید با مدل‌های واقعی Client بدون بررسی تطبیق فرض شود.
4. Store ID محلی Client نباید مستقیماً site identity تلقی شود.
5. در قرارداد هدف، احراز هویت Backend با **یک WooGit Session معتبر** انجام می‌شود و مدل جداگانه Access/Refresh Token بخشی از قرارداد نیست.
6. **Customer Credentials در V1 فقط request-scoped هستند و Backend نباید آن‌ها را در DB، Vault، Cache پایدار یا هر storage دائمی نگهداری کند.**
7. رفتار فعلی direct WooCommerce باید قبل از cutover با contract test پوشش داده شود.
8. Migration باید امکان rollback یا coexistence کنترل‌شده داشته باشد.
9. **هیچ عملیات تجاری Backend به سایت مشتری نباید قبل از موفقیت Connection Verification اجرا شود.**
10. **اولین outbound request برای اتصال هر Site Identity جدید باید Verification read-only باشد.**

## ۱۷. Definition of Done برای Client Contract

قرارداد Client ↔ Backend زمانی آماده implementation محسوب می‌شود که برای هر عملیات مورد استفاده Client موارد زیر مشخص باشند:

- endpoint
- HTTP method
- authentication
- request schema
- response schema
- error codes
- retry policy
- idempotency policy
- timeout behavior
- pagination/filter/sort
- operation identity
- reconciliation strategy
- rate-limit behavior
- required entitlement
- **connection verification stage و success/failure semantics**
