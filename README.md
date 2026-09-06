# بک‌اند WooGit

> این مخزن **فقط برای ساخت بک‌اند سرویس WooGit** است. اپ اندروید WooGit قبلاً ساخته شده و یک پروژه مستقل است؛ این مخزن قرار نیست اپ را بسازد، بازطراحی کند یا جایگزین آن شود.

## مخزن مرجع اپ موجود

برای قرارداد واقعی Backend، رفتار Client و بررسی سازگاری API، مخزن رسمی اپ WooGit این است:

**https://github.com/samanramezani1377-hub/woogit**

این repository مرجع Backend است و `woogit` مرجع Client اندروید. هرجا قرارداد Backend باید با رفتار واقعی اپ تطبیق داده شود، ابتدا باید implementation و قراردادهای موجود در repository اپ بررسی شوند.

## محدوده قطعی پروژه

مسئولیت این مخزن ساخت سرویس سمت سرور موردنیاز اپ موجود WooGit است:

```text
اپ موجود WooGit
      │
      │ HTTPS
      ▼
┌──────────────────────────────┐
│        WooGit Backend        │
│                              │
│ Auth / Sessions              │
│ Site Identity                │
│ Subscription / Entitlements │
│ Credential Vault             │
│ API Gateway                 │
│ WooCommerce Operations       │
│ WordPress Bridge Integration │
│ Webhooks / Jobs              │
│ Idempotency / Reliability    │
│ Audit / Security             │
└──────────────┬───────────────┘
               │
               ▼
       WordPress / WooCommerce
             مشتری
```

### این پروژه چیست؟

یک Backend production-grade که اپ موجود WooGit از طریق آن به سایت‌های WordPress/WooCommerce مشتریان متصل می‌شود و منطق تجاری، احراز هویت، اشتراک، مجوز، امنیت، اعتبارهای سایت و عملیات سمت سرور را کنترل می‌کند.

### این پروژه چیست؟

- پروژه Android نیست.
- محل توسعه UI اپ نیست.
- محل بازطراحی ConnectionScreen یا Dashboard اپ نیست.
- جایگزین اپ WooGit نیست.
- نباید قابلیت‌های اپ را دوباره داخل این مخزن پیاده‌سازی کند.

اپ موجود صرفاً **Client خارجی و مصرف‌کننده API** این پروژه است.

## مرز مسئولیت‌ها

### اپ WooGit — پروژه مستقل

- UI/UX موبایل
- نمایش داده‌ها
- دریافت ورودی کاربر
- مدیریت وضعیت رابط کاربری
- ارسال درخواست به Backend
- نگهداری توکن نشست در سمت کلاینت

### WooGit Backend — این مخزن

- احراز هویت و نشست‌ها
- Site Identity و مالکیت سایت
- ثبت و مدیریت حساب
- Trial و Subscription
- Entitlement و مجوز قابلیت‌ها
- Credential Vault
- Gateway و سیاست دسترسی
- عملیات محصولات، سفارش‌ها، مشتریان و سایر داده‌های WooCommerce
- ارتباط امن با WordPress/WooCommerce
- Bridge integration
- Webhook، Queue و Job
- Idempotency و Retry safety
- Timeout-after-success safety
- Rate limiting و Abuse protection
- Audit و Security
- Analytics/Events در صورت فعال بودن قابلیت
- AI Gateway در صورت فعال بودن قابلیت تجاری

### WordPress/WooCommerce مشتری

مرجع محتوای واقعی فروشگاه و داده‌های WooCommerce است. Backend باید دسترسی به آن را کنترل و استاندارد کند؛ نباید مالک داده‌های اصلی فروشگاه تلقی شود.

### WooGit Bridge

یک جزء سمت WordPress مشتری است که در صورت نیاز برای عملیات و قابلیت‌های کنترل‌شده توسط Backend استفاده می‌شود. Bridge بخشی از اپ اندروید نیست.

## Flow اتصال و حساب

صفحه اول اپ از قبل ساخته و **قفل‌شده** است. Backend باید دقیقاً با قرارداد موجود اپ هماهنگ شود و آن را تغییر ندهد.

ورودی‌های صفحه اول:

- HTTPS/HTTP
- دامنه فروشگاه
- WooCommerce Consumer Key
- WooCommerce Consumer Secret
- WordPress username
- WordPress Application Password

Backend ابتدا اتصال واقعی WordPress/WooCommerce را اعتبارسنجی می‌کند. اگر اتصال شکست بخورد، حساب و Trial ساخته نمی‌شوند.

اگر اتصال موفق باشد:

- Site Identity موجود → ورود به حساب متناظر همان سایت و ایجاد نشست WooGit.
- Site Identity جدید → دریافت email، نام و نام خانوادگی در مرحله بعد، ایجاد حساب و Trial در صورت واجدشرایط بودن.

Trial به Site Identity/دامنه وابسته است، نه صرفاً ایمیل یا Google Account.

اعتبارهای سایت پس از onboarding نباید برای عملیات عادی به اپ برگردند؛ Backend آن‌ها را در Credential Vault امن نگهداری و هنگام نیاز استفاده می‌کند.

## معماری مرجع

```text
Android App (samanramezani1377-hub/woogit)
   │
   │ HTTPS + short-lived access token
   ▼
WooGit API / Gateway
   │
   ├── Authentication / Sessions
   ├── Account / Site Identity
   ├── Subscription / Entitlements
   ├── Credential Vault
   ├── Typed WooCommerce API
   ├── Jobs / Webhooks / Events
   ├── Audit / Rate Limit / Security
   └── Optional AI / Chat / Analytics
   │
   ▼
WordPress / WooCommerce Customer Site
   │
   └── WooGit Bridge (when required)
```

وب‌سایت عمومی یا پنل مدیریتی WooGit می‌تواند یک سامانه جداگانه WordPress باشد و در صورت وجود، از Backend استفاده کند؛ اما **ساخت آن وب‌سایت موضوع اصلی این مخزن نیست**.

## اصول اصلی Backend

1. **Server-side authorization** — اپ مرجع Trial، Subscription یا Entitlement نیست.
2. **No customer secrets in APK** — اعتبارهای سایت در Backend/Secret Vault نگهداری می‌شوند.
3. **Least privilege** — دسترسی‌ها حداقلی و قابل کنترل هستند.
4. **Idempotent mutations** — CREATE mutationها باید قرارداد Idempotency داشته باشند.
5. **Timeout-after-success safety** — Retry بعد از Timeout نباید باعث عملیات تکراری شود.
6. **Typed operations** — به‌جای Proxy دلخواه URL، عملیات مشخص و مجاز ارائه می‌شود.
7. **Secure gateway** — درخواست تجاری اپ از مسیر Backend عبور می‌کند.
8. **Observable and recoverable** — لاگ، متریک، Audit، Queue و Recovery از ابتدا بخشی از طراحی هستند.
9. **Cloud-heavy / WordPress-light** — پردازش‌های سنگین در Backend انجام می‌شوند.
10. **APK is untrusted** — تغییر یا دستکاری کلاینت نباید مجوز سمت سرور را دور بزند.

## اسناد

- [`docs/SCOPE.md`](docs/SCOPE.md) — مرز قطعی پروژه و تفکیک Backend از اپ موجود.
- [`docs/PRODUCT.md`](docs/PRODUCT.md) — محدوده سرویس Backend، مدل تجاری و قابلیت‌های موردنیاز آن.
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — معماری سیستم و جریان درخواست‌ها.
- [`docs/SECURITY.md`](docs/SECURITY.md) — امنیت اعتبارها، حریم خصوصی و احراز هویت.
- [`docs/DATA-MODEL.md`](docs/DATA-MODEL.md) — موجودیت‌های اصلی و روابط آن‌ها.
- [`docs/API-CONTRACT.md`](docs/API-CONTRACT.md) — قرارداد API و الگوهای استاندارد درخواست.
- [`docs/WORDPRESS-BRIDGE.md`](docs/WORDPRESS-BRIDGE.md) — مسئولیت‌ها و پروتکل Bridge.
- [`docs/AI-CHAT-ANALYTICS.md`](docs/AI-CHAT-ANALYTICS.md) — قابلیت‌های اختیاری Backend برای AI، چت و تحلیل.
- [`docs/BILLING.md`](docs/BILLING.md) — Trial، Subscription، اعتبار و Entitlement.
- [`docs/OPERATIONS.md`](docs/OPERATIONS.md) — استقرار، مشاهده‌پذیری، Backup، مقیاس‌پذیری و Incident Management.
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — مراحل پیاده‌سازی Backend و معیارهای پذیرش.
- [`docs/DECISIONS.md`](docs/DECISIONS.md) — تصمیم‌های معماری و مرزهای پروژه.

## پشته پیشنهادی

- API/Gateway: Laravel/PHP، تا حد امکان Stateless
- Database: PostgreSQL
- Cache/Queue/Rate Limit: Redis
- Realtime در صورت نیاز: WebSocket/SSE
- Object Storage: S3-compatible
- Secrets: Encryption + KMS/Secret Manager در محیط عملیاتی
- Customer integration: WordPress/WooCommerce + WooGit Bridge
- Client: اپ Android موجود WooGit

MVP می‌تواند روی یک VPS کوچک شروع شود و با افزایش بار اجزا جدا شوند. این انتخاب به معنی اجباری بودن استقرار همه سرویس‌ها از روز اول نیست.

## وضعیت پروژه

این مخزن نقشه و محل پیاده‌سازی **Backend اپ موجود WooGit** است. هیچ ادعایی مبنی بر کامل بودن implementation پذیرفته نیست؛ هر قابلیت باید مطابق Roadmap، API Contract، معیارهای امنیتی و تست‌های واقعی پیاده‌سازی و اثبات شود.