# معماری نهایی Backend ووگیت

> وضعیت: **V1 Architecture — Locked**
>
> این سند معماری Backend را بر اساس اپ Android موجود WooGit و تصمیم نهایی V1 تعریف می‌کند. اپ در repository مستقل `samanramezani1377-hub/woogit` قرار دارد و این repository فقط Backend آن را می‌سازد.

## ۱. تصمیم اصلی

Backend ووگیت در V1 نباید یک Backend سنگین با دیتامدل کامل WooCommerce، Mirror دائمی محصولات و سفارش‌ها، یا معماری Microservice باشد.

مدل انتخاب‌شده:

```text
WordPress
+ WooGit Backend Plugin
+ REST API / Gateway
+ Subscription Check
+ Site Identity
+ Secure Credential Storage
+ Controlled Proxy
```

اصل کلیدی:

> **Customer WordPress + WooCommerce منبع اصلی داده فروشگاه است؛ Backend ووگیت عمدتاً Gateway/Proxy و مرجع دسترسی و Subscription است.**

---

## ۲. مبنای معماری: اپ فعلی

اپ فعلی WooGit مستقیماً با سایت مشتری ارتباط برقرار می‌کند:

```text
WooGit Android
      │
      │ HTTPS / HTTP
      │ WooCommerce REST
      │ WordPress REST
      ▼
Customer WordPress + WooCommerce
```

صفحه اتصال فعلی اپ اطلاعات زیر را می‌گیرد:

- HTTP/HTTPS
- دامنه فروشگاه
- WooCommerce Consumer Key
- WooCommerce Consumer Secret
- WordPress Username
- WordPress Application Password

در وضعیت فعلی App-to-Backend authentication، Access Token/Refresh Token و Session مربوط به Backend در اپ وجود ندارند. این‌ها فقط در صورت نیاز، بخشی از Migration به Backend خواهند بود و نباید به‌عنوان قابلیت فعلی گزارش شوند.

---

## ۳. توپولوژی هدف

```text
┌──────────────────────┐
│     WooGit Android   │
│    Existing Client   │
└──────────┬───────────┘
           │ HTTPS
           ▼
┌────────────────────────────┐
│       WooGit Backend       │
│          WordPress         │
│                            │
│  WooGit Backend Plugin     │
│  Site Identity             │
│  Authentication            │
│  Subscription              │
│  Access Control             │
│  Credential Storage         │
│  Controlled Proxy           │
│  Rate Limit / Security     │
└────────────┬───────────────┘
             │ HTTPS
             ▼
┌────────────────────────────┐
│ Customer WordPress         │
│ + WooCommerce              │
└────────────────────────────┘
```

هدف این نیست که WooCommerce دوباره در Backend ساخته شود؛ هدف این است که Backend یک مسیر کنترل‌شده بین اپ و سایت مشتری ایجاد کند.

---

## ۴. WordPress به‌عنوان هسته مدیریتی Backend

Backend V1 بر پایه WordPress ساخته می‌شود.

WordPress مسئول:

- کاربران Backend؛
- نقش‌ها و دسترسی‌های مدیریتی؛
- Site Identity records؛
- پلن‌ها؛
- Subscription؛
- Trial؛
- تنظیمات؛
- پنل مدیریت؛
- داده‌های موردنیاز عملیاتی Backend.

افزونه اختصاصی WooGit مسئول منطق سرویس است:

- REST API؛
- Gateway؛
- Proxy؛
- بررسی Subscription؛
- کنترل Site Ownership؛
- Credential handling؛
- Rate Limit؛
- Security controls؛
- Operation/Idempotency handling در موارد لازم.

در نتیجه برای V1 نیازی به ساخت یک Admin Panel مستقل از WordPress نیست.

---

## ۵. اصل عدم Mirror دائمی

Backend در V1 نباید دیتابیس دوم WooCommerce بسازد.

موارد زیر منبع اصلی خود را در سایت مشتری حفظ می‌کنند:

- Products
- Orders
- Customers
- Categories
- Variations
- Media
- سایر داده‌های WooCommerce

برای مثال:

```text
App
 ↓
Backend
 ↓
Customer WooCommerce
 ↓
Backend
 ↓
App
```

نه:

```text
App
 ↓
Backend Database
 ↓
Products/Orders Mirror
```

عبارت «Mirror» در این معماری به عبور کنترل‌شده درخواست و پاسخ اشاره دارد، نه ذخیره دائمی تمام داده‌های فروشگاه.

---

## ۶. جریان اصلی درخواست

هر درخواست تجاری در V1 تقریباً این مسیر را طی می‌کند:

```text
App
 ↓
Authentication / Session
 ↓
Site Identity
 ↓
Subscription Check
 ↓
Entitlement / Permission
 ↓
Rate Limit / Security
 ↓
Controlled Proxy
 ↓
Customer WordPress/WooCommerce
 ↓
Response filtering
 ↓
App
```

اگر Subscription یا دسترسی مجاز نباشد، Backend نباید درخواست را به سایت مشتری ارسال کند.

---

## ۷. Proxy آزاد ممنوع

Backend نباید endpointی داشته باشد که Client بتواند URL دلخواه اینترنتی به آن بدهد:

```text
/proxy?url=https://anything.com
```

این مدل می‌تواند باعث SSRF و سوءاستفاده به‌عنوان Proxy عمومی شود.

مقصد باید از Site Identity ثبت‌شده تعیین شود:

```text
/v1/sites/{site_id}/wc/products
```

Backend خودش مقصد واقعی Site را resolve می‌کند:

```text
site_id
   ↓
registered customer domain
   ↓
https://customer-site.com/wp-json/wc/...
```

---

## ۸. Site Identity

Backend باید Site Identity مستقل و authoritative داشته باشد.

Store ID فعلی اپ که از URL فروشگاه به‌صورت local hash ساخته می‌شود، نباید مستقیماً به‌عنوان `site_id` Backend پذیرفته شود.

در Migration باید mapping صریح انجام شود:

```text
Client Store ID
      ↓
Migration Mapping
      ↓
Backend Site Identity
```

مالکیت Site Identity با موفقیت احراز اتصال واقعی به همان سایت اثبات می‌شود.

---

## ۹. Subscription و Trial

مهم‌ترین منطق تجاری Gateway در V1 بررسی وضعیت دسترسی است:

```text
Request
 ↓
Site Identity
 ↓
Subscription active?
 ├── YES → Proxy
 └── NO  → Reject
```

Trial به Site Identity/دامنه وابسته است، نه صرفاً Email یا Google Account.

```text
Site Identity
 ↓
Trial history
 ↓
Already used?
 ├── YES → no new trial
 └── NO  → eligible for trial
```

تغییر ایمیل یا حساب Google نباید برای همان Site Identity Trial جدید ایجاد کند.

---

## ۱۰. Credentials

در معماری فعلی اپ، WooCommerce Consumer Key/Secret و WordPress Application Password برای اتصال مستقیم استفاده می‌شوند.

در Migration هدف این است که Credentialهای سایت به Backend منتقل و در Credential Storage امن نگهداری شوند و برای عملیات عادی دوباره به App برگردانده نشوند.

WordPress Application Password یک credential برنامه‌ای قابل ابطال است و برای REST API طراحی شده است؛ در ارتباط خارجی باید HTTPS استفاده شود. urlWordPress Application Passwordshttps://developer.wordpress.org/rest-api/reference/application-passwords/ urlWordPress REST API Authenticationhttps://developer.wordpress.org/rest-api/using-the-rest-api/authentication/

WooCommerce نیز REST API و Consumer Key/Consumer Secret برای دسترسی به API ارائه می‌کند. urlWooCommerce REST API Authenticationhttps://woocommerce.github.io/woocommerce-rest-api-docs/wp-api-v2.html

قواعد امنیتی:

- Secret در response عادی برنگردد.
- Secret در log ثبت نشود.
- دسترسی به Credential حداقلی باشد.
- Credential rotation/revocation قابل مدیریت باشد.
- HTTPS در Production الزامی باشد.

---

## ۱۱. عملیات WooCommerce

Backend باید عملیات واقعی مورد استفاده Client را پوشش دهد، اما آن‌ها را دوباره در دیتابیس خودش مدل نکند.

حوزه‌های اصلی:

- Products
- Orders
- Customers در صورت استفاده
- Categories
- Variations
- Media
- Product mutations
- Order mutations
- Sync/reconciliation موردنیاز Client

API می‌تواند typed باشد و مسیرهای شناخته‌شده مانند موارد زیر داشته باشد:

```text
/v1/sites/{site_id}/products
/v1/sites/{site_id}/orders
/v1/sites/{site_id}/customers
/v1/sites/{site_id}/media
```

اما پیاده‌سازی داخلی همچنان می‌تواند Proxy کنترل‌شده به WordPress/WooCommerce باشد.

---

## ۱۲. Media

Media نیز نباید در Backend به یک مخزن دائمی از فایل‌های فروشگاه تبدیل شود، مگر جایی که یک قابلیت مشخص چنین چیزی را لازم کند.

Flow هدف:

```text
App image
 ↓
Backend
 ↓
Customer WordPress Media
 ↓
Media ID + URL
 ↓
Product/media association
```

Upload باید در برابر retry و timeout ایمن باشد تا یک درخواست تکراری باعث ایجاد Media تکراری نشود.

---

## ۱۳. Idempotency و Timeout-after-success

Proxy بودن Backend مشکلات ambiguity در mutationها را حذف نمی‌کند.

سناریوی اجباری:

```text
App → CREATE
Backend → Customer WooCommerce
Customer WooCommerce → SUCCESS
Backend → response lost
App → retry
Backend → detect same operation
Backend → return previous result
```

برای CREATE mutationها و سایر عملیات non-idempotent باید Operation Identity/Idempotency داشته باشیم.

هدف این است که timeout شبکه هرگز به‌صورت خودکار به معنی «عملیات انجام نشده» تفسیر نشود.

---

## ۱۴. Reconciliation

برای عملیات ambiguous باید امکان پیدا کردن نتیجه واقعی وجود داشته باشد:

```text
operation_id
 ↓
operation state
 ↓
remote result / resource lookup
 ↓
canonical final state
```

این بخش باید حداقلی و هدفمند باشد و نباید Backend را به یک سیستم Sync دائمی تبدیل کند.

---

## ۱۵. Error Contract

Backend باید خطاهای استاندارد برای Client تولید کند و Client نباید مجبور باشد متن خام PHP/WordPress را parse کند.

نمونه:

```json
{
  "code": "STORE_CONNECTION_FAILED",
  "message": "اتصال به فروشگاه انجام نشد.",
  "request_id": "...",
  "operation_id": "...",
  "retryable": false
}
```

Secret، Application Password، Consumer Secret و stack trace نباید در پاسخ عمومی قرار بگیرند.

---

## ۱۶. Currency و داده‌های مالی

Backend نباید context مالی را حذف کند.

برای Products، Orders و سایر داده‌های مالی، currency/currency context موردنیاز Client باید از داده canonical فروشگاه حفظ و منتقل شود.

Backend نباید فرض کند واحد پول همیشه USD، EUR یا مقدار ثابت دیگری است.

---

## ۱۷. منابع و زیرساخت V1

هدف V1 کمترین منابع عملیاتی ممکن است.

مدل پایه:

```text
WordPress
 + WooGit Plugin
 + Database
 + REST API
 + Proxy
 + Subscription Check
```

در V1 موارد زیر الزام معماری نیستند:

- Microservices
- Kubernetes
- دیتابیس جدا برای هر سایت
- Mirror کامل WooCommerce
- Full Sync دائمی
- Workerهای سنگین بدون نیاز واقعی
- Admin Panel مستقل

اگر در آینده بار واقعی ایجاد شود، اجزای خاص می‌توانند جدا شوند.

---

## ۱۸. مرزهای خرابی

اگر سایت مشتری Down باشد:

- Subscription ووگیت نباید خودکار invalid شود.
- خطای اتصال سایت باید جداگانه گزارش شود.
- Retry فقط طبق policy امن انجام شود.

اگر Backend Down باشد:

- App نباید مسیر تجاری مستقیم و مخفی برای دور زدن Gateway داشته باشد.
- Cache محلی غیرحساس می‌تواند توسط App نمایش داده شود.
- عملیات جدید وابسته به Backend باید طبق contract fail شود.

---

## ۱۹. Migration از معماری فعلی App

وضعیت فعلی:

```text
App → Customer WordPress/WooCommerce
```

هدف:

```text
App → WooGit Backend → Customer WordPress/WooCommerce
```

Migration نباید باعث بازنویسی UI، Navigation یا معماری داخلی Android شود.

هر endpoint Backend باید با مصرف واقعی Client تطبیق داده شود و قبل از cutover، رفتار direct API فعلی با contract test پوشش داده شود.

---

## ۲۰. چیزهایی که در V1 انجام نمی‌دهیم

برای جلوگیری از Scope Creep:

- بازسازی Product/Order database در Backend
- Mirror دائمی تمام فروشگاه‌ها
- Full Store Sync
- Proxy آزاد برای URL دلخواه
- Microservice architecture
- Kubernetes
- Admin Panel جدا از WordPress
- بازطراحی Android
- بازسازی WooGit Client
- انتقال مالکیت داده WooCommerce از سایت مشتری به Backend

---

## ۲۱. تعریف نهایی معماری V1

```text
                    ┌──────────────────────┐
                    │     WooGit App       │
                    │   Existing Android   │
                    └──────────┬───────────┘
                               │
                               │ HTTPS
                               ▼
                    ┌──────────────────────┐
                    │   WooGit Backend     │
                    │      WordPress       │
                    │                      │
                    │ WooGit Plugin        │
                    │ Site Identity        │
                    │ Subscription         │
                    │ Authentication       │
                    │ Credential Storage   │
                    │ Controlled Proxy     │
                    │ Rate Limit/Security  │
                    └──────────┬───────────┘
                               │
                               │ HTTPS
                               ▼
                    ┌──────────────────────┐
                    │ Customer WordPress   │
                    │ + WooCommerce        │
                    └──────────────────────┘
```

اصل نهایی:

> **Backend ووگیت نباید WooCommerce را دوباره بسازد؛ باید آن را به‌صورت کنترل‌شده، امن و وابسته به Subscription به اپ متصل کند.**

در V1، Gateway عمدتاً سه سؤال را پاسخ می‌دهد:

```text
1. این درخواست برای کدام Site Identity است؟
2. آیا این سایت/حساب اجازه استفاده دارد؟
3. اگر اجازه دارد، درخواست را امن و کنترل‌شده به همان سایت ارسال کن.
```

هر قابلیت سنگین‌تر فقط زمانی وارد معماری می‌شود که نیاز واقعی، بار واقعی یا قرارداد محصول آن را توجیه کند.
