# بک‌اند WooGit

> این مخزن **Backend و WordPress stack ووگیت** است. اپ اندروید WooGit قبلاً ساخته شده و یک پروژه مستقل است؛ این مخزن قرار نیست اپ را بسازد، بازطراحی کند یا جایگزین آن شود.
>
> **وضعیت فعلی:** اپ WooGit در حال حاضر مستقل از Backend است و مستقیماً با سایت مشتری کار می‌کند. Backend جدید به‌عنوان سرویس سمت سرور برای اتصال آینده اپ به سایت‌های مشتری ساخته می‌شود.

## مخزن مرجع اپ موجود

برای قرارداد واقعی Backend، رفتار Client و بررسی سازگاری API، مخزن رسمی اپ WooGit این است:

**https://github.com/samanramezani1377-hub/woogit**

این repository مرجع Backend است و `woogit` مرجع Client اندروید. هرجا قرارداد Backend باید با رفتار واقعی اپ تطبیق داده شود، ابتدا implementation و قراردادهای موجود در repository اپ بررسی می‌شوند.

## تصمیم جدید: یک Repository برای Theme + Backend Plugin

برای لایه WordPress ووگیت، همین Repository به‌صورت **Monorepo** دو Component مستقل را نگهداری می‌کند:

```text
backend-site/
├── theme/
│   └── woogit/              ← ظاهر وب‌سایت WooGit
│
├── plugin/
│   └── woogit-backend/      ← مغز Backend WooGit
│
├── docs/
└── .github/
```

این دو Component از نظر source control در یک Repository هستند، اما از نظر مسئولیت، کد، تست و release مستقل باقی می‌مانند.

جزئیات کامل این تصمیم در [`docs/WORDPRESS_MONOREPO.md`](docs/WORDPRESS_MONOREPO.md) ثبت شده است.

### Theme چیست؟

Theme فقط لایه Presentation وب‌سایت WooGit است:

- صفحه اصلی؛
- معرفی محصول؛
- Pricing؛
- FAQ؛
- Contact؛
- Header/Footer؛
- Responsive UI؛
- Typography و Design System؛
- Template و Pattern؛
- CSS/JS مربوط به ظاهر.

Theme نباید محل منطق تجاری، امنیت یا داده حساس Backend باشد.

### Backend Plugin چیست؟

`woogit-backend` مغز سرویس WooGit در WordPress است:

- REST API؛
- Authentication / Sessions؛
- Site Identity؛
- Account lifecycle؛
- Trial / Subscription؛
- Entitlements؛
- App Version Gate؛
- Credential Vault؛
- Gateway / Controlled Proxy؛
- Idempotency؛
- Timeout-after-success؛
- Reconciliation؛
- Audit / Security؛
- Webhooks / Jobs؛
- Admin Console؛
- داده‌ها و جداول عملیاتی Backend.

این قابلیت‌ها نباید به فعال بودن Theme وابسته باشند.

## اصل معماری WordPress

```text
                    WordPress
                       │
          ┌────────────┴────────────┐
          │                         │
       Theme                 WooGit Backend Plugin
          │                         │
     Presentation          Backend / Business Logic
          │                         │
          └────────────┬────────────┘
                       │
                 WooGit Website
```

**یک Repository، دو Component مستقل.**

Theme ظاهر سایت را کنترل می‌کند؛ Plugin رفتار و قابلیت‌های Backend را کنترل می‌کند.

## محدوده قطعی پروژه

مسئولیت این مخزن ساخت سرویس سمت سرور موردنیاز اپ موجود WooGit و WordPress stack عمومی/مدیریتی آن است:

```text
WooGit Android App
       │
       │ آینده: HTTPS / WooGit API
       ▼
┌──────────────────────────────┐
│        WooGit Backend        │
│      WordPress + Plugin      │
│                              │
│ Auth / Sessions              │
│ Site Identity                │
│ Subscription / Entitlements  │
│ Credential Vault             │
│ API Gateway / Proxy          │
│ WooCommerce Operations       │
│ Webhooks / Jobs              │
│ Idempotency / Reliability    │
│ Audit / Security             │
└──────────────┬───────────────┘
               │
               ▼
       Customer WordPress
          + WooCommerce

        ┌────────────────┐
        │ WooGit Theme   │
        │ Public Website │
        └────────────────┘
```

### این پروژه چیست؟

یک Backend production-grade که پس از آماده شدن و اتصال اپ موجود WooGit، مسیر کنترل‌شده اتصال اپ به سایت‌های WordPress/WooCommerce مشتریان را فراهم می‌کند و منطق تجاری، احراز هویت، اشتراک، مجوز، امنیت، اعتبارهای سایت و عملیات سمت سرور را کنترل می‌کند.

همچنین Theme اختصاصی WooGit در همین Repository برای طراحی وب‌سایت عمومی استفاده خواهد شد؛ اما Theme و Plugin از نظر مسئولیت مستقل هستند.

### این پروژه چیست؟

- پروژه Android نیست.
- محل توسعه UI اپ نیست.
- محل بازطراحی ConnectionScreen یا Dashboard اپ نیست.
- جایگزین اپ WooGit نیست.
- نباید قابلیت‌های اپ را دوباره داخل این مخزن پیاده‌سازی کند.
- Theme نباید Backend logic را در خود نگه دارد.
- Plugin نباید به یک Theme خاص برای اجرای قابلیت‌های حیاتی وابسته باشد.

## مرز مسئولیت‌ها

### اپ WooGit — پروژه مستقل

- UI/UX موبایل
- نمایش داده‌ها
- دریافت ورودی کاربر
- مدیریت وضعیت رابط کاربری
- ارسال درخواست به Backend پس از آماده شدن و اتصال Backend
- نگهداری توکن نشست در سمت کلاینت

### WooGit Backend Plugin — این مخزن

- احراز هویت و نشست‌ها
- Site Identity و مالکیت سایت
- ثبت و مدیریت حساب
- Trial و Subscription
- Entitlement و مجوز قابلیت‌ها
- Credential Vault
- Gateway و سیاست دسترسی
- عملیات محصولات، سفارش‌ها، مشتریان و سایر داده‌های WooCommerce
- ارتباط امن با WordPress/WooCommerce
- Webhook، Queue و Job
- Idempotency و Retry safety
- Timeout-after-success safety
- Rate limiting و Abuse protection
- Audit و Security
- Version Gate
- Admin Console
- داده و جداول عملیاتی Backend

### WooGit Theme — این مخزن

- طراحی و ظاهر وب‌سایت عمومی WooGit
- صفحات Marketing
- Pricing / FAQ / Contact
- Header / Footer
- Responsive UI
- Design System
- Template / Pattern
- CSS / JS presentation

### WordPress/WooCommerce مشتری

مرجع محتوای واقعی فروشگاه و داده‌های WooCommerce است. Backend باید دسترسی به آن را کنترل و استاندارد کند؛ نباید مالک داده‌های اصلی فروشگاه تلقی شود.

### WooGit Bridge

یک جزء سمت WordPress مشتری است که در صورت نیاز برای عملیات و قابلیت‌های کنترل‌شده توسط Backend استفاده می‌شود. Bridge بخشی از اپ اندروید نیست.

## Admin Console

پنل مدیریتی Backend در V1 داخل همان WordPress و از طریق `woogit-backend` ساخته می‌شود و نیاز به یک Admin Panel مستقل ندارد.

ساختار منطقی:

```text
WooGit
├── Dashboard
├── Sites
├── Customers / Accounts
├── Subscriptions
├── Plans
├── Trials
├── App Versions
├── Entitlements
├── Operations
├── Audit Logs
├── Security
└── Settings
```

API و Admin باید از Service Layer مشترک استفاده کنند و business logic نباید بین آن‌ها duplicate شود.

## Flow اتصال و حساب

صفحه اول اپ از قبل ساخته و **قفل‌شده** است. Backend باید دقیقاً با قرارداد موجود اپ هماهنگ شود و آن را تغییر ندهد.

در وضعیت فعلی، این Flow هنوز به Backend متصل نیست. این Backend برای مرحله‌ای ساخته می‌شود که پس از آماده شدن آن، اپ موجود به این Flow متصل شود.

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

## اصول اصلی Backend

1. **Server-side authorization** — اپ مرجع Trial، Subscription یا Entitlement نیست.
2. **No customer secrets in APK** — اعتبارهای سایت در Backend/Secret Vault نگهداری می‌شوند.
3. **Least privilege** — دسترسی‌ها حداقلی و قابل کنترل هستند.
4. **Idempotent mutations** — CREATE mutationها باید قرارداد Idempotency داشته باشند.
5. **Timeout-after-success safety** — Retry بعد از Timeout نباید باعث عملیات تکراری شود.
6. **Typed operations** — به‌جای Proxy دلخواه URL، عملیات مشخص و مجاز ارائه می‌شود.
7. **Secure gateway** — درخواست تجاری اپ از مسیر Backend عبور می‌کند.
8. **Observable and recoverable** — لاگ، متریک، Audit، Queue و Recovery از ابتدا بخشی از طراحی هستند.
9. **WordPress-light backend** — V1 باید تا حد ممکن سبک بماند و Customer WooCommerce را Source of Truth نگه دارد.
10. **APK is untrusted** — تغییر یا دستکاری کلاینت نباید مجوز سمت سرور را دور بزند.

## اسناد

- [`docs/SCOPE.md`](docs/SCOPE.md) — مرز قطعی پروژه و تفکیک Backend از اپ موجود.
- [`docs/PRODUCT.md`](docs/PRODUCT.md) — محدوده سرویس Backend، مدل تجاری و قابلیت‌های موردنیاز آن.
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — معماری سیستم و جریان درخواست‌ها.
- [`docs/WORDPRESS_MONOREPO.md`](docs/WORDPRESS_MONOREPO.md) — ساختار Monorepo، مرز Theme/Plugin، Admin Console، Database و Release.
- [`docs/SECURITY.md`](docs/SECURITY.md) — امنیت اعتبارها، حریم خصوصی و احراز هویت.
- [`docs/DATA-MODEL.md`](docs/DATA-MODEL.md) — موجودیت‌های اصلی و روابط آن‌ها.
- [`docs/API-CONTRACT.md`](docs/API-CONTRACT.md) — قرارداد API و الگوهای استاندارد درخواست.
- [`docs/WORDPRESS-BRIDGE.md`](docs/WORDPRESS-BRIDGE.md) — مسئولیت‌ها و پروتکل Bridge.
- [`docs/AI-CHAT-ANALYTICS.md`](docs/AI-CHAT-ANALYTICS.md) — قابلیت‌های اختیاری Backend برای AI، چت و تحلیل.
- [`docs/BILLING.md`](docs/BILLING.md) — Trial، Subscription، اعتبار و Entitlement.
- [`docs/OPERATIONS.md`](docs/OPERATIONS.md) — استقرار، مشاهده‌پذیری، Backup، مقیاس‌پذیری و Incident Management.
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — مراحل پیاده‌سازی Backend و معیارهای پذیرش.
- [`docs/DECISIONS.md`](docs/DECISIONS.md) — تصمیم‌های معماری و مرزهای پروژه.

## وضعیت پروژه

این مخزن نقشه و محل پیاده‌سازی **Backend + WordPress Theme/Plugin stack ووگیت** است. هیچ ادعایی مبنی بر کامل بودن implementation پذیرفته نیست؛ هر قابلیت باید مطابق Roadmap، API Contract، معیارهای امنیتی و تست‌های واقعی پیاده‌سازی و اثبات شود.
