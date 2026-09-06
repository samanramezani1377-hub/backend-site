# ساختار WordPress ووگیت: Theme + Backend Plugin

> وضعیت: **V1 — Locked**
>
> این سند ساختار نهایی Repository مربوط به WordPress ووگیت را تعریف می‌کند. Theme و Backend Plugin در **یک Repository واحد** نگهداری می‌شوند، اما از نظر مسئولیت، کد، چرخه تغییر و وابستگی کاملاً از هم جدا هستند.

## ۱. تصمیم اصلی

Repository `backend-site` به‌عنوان Monorepo لایه WordPress ووگیت استفاده می‌شود:

```text
backend-site/
├── theme/
│   └── woogit/
│       ├── style.css
│       ├── theme.json
│       ├── templates/
│       ├── parts/
│       ├── patterns/
│       └── assets/
│
├── plugin/
│   └── woogit-backend/
│       ├── woogit-backend.php
│       ├── src/
│       │   ├── API/
│       │   ├── Auth/
│       │   ├── Sites/
│       │   ├── Subscriptions/
│       │   ├── Entitlements/
│       │   ├── Version/
│       │   ├── Credentials/
│       │   ├── Gateway/
│       │   ├── Idempotency/
│       │   ├── Audit/
│       │   └── Security/
│       ├── admin/
│       ├── tests/
│       └── assets/
│
├── docs/
└── .github/
```

این ساختار به معنی یکی کردن Theme و Plugin نیست. فقط source control و release management آن‌ها مشترک است.

---

## ۲. چرا یک Repository؟

برای V1 این مدل مزیت‌های عملی دارد:

- توسعه و versioning هماهنگ؛
- یک CI مشترک برای کیفیت کل WordPress stack؛
- امکان تست سازگاری Theme و Plugin در کنار هم؛
- یک محل مستندات و تصمیم‌های معماری؛
- deployment ساده‌تر برای محیط V1؛
- جلوگیری از ایجاد چند Repository کوچک با قراردادهای مبهم؛
- امکان تغییر ظاهر سایت بدون شکستن Backend؛
- امکان تغییر Backend بدون وابستگی به Theme.

**Monorepo به معنی coupling نیست.** مرزهای معماری باید حتی در یک Repository نیز حفظ شوند.

---

## ۳. مرز Theme و Plugin

WordPress به‌صورت استاندارد Theme را مسئول presentation و Plugin را مسئول behavior/functionality می‌داند. قابلیت‌هایی که باید مستقل از طراحی سایت باقی بمانند نباید فقط در Theme قرار بگیرند. urlWordPress — What Is a Theme?https://developer.wordpress.org/themes/getting-started/what-is-a-theme/ urlWordPress — Custom Functionalityhttps://developer.wordpress.org/themes/core-concepts/custom-functionality/

### Theme = ظاهر و تجربه وب‌سایت

Theme مسئول موارد زیر است:

- صفحه اصلی WooGit؛
- معرفی محصول و سرویس؛
- Pricing؛
- FAQ؛
- Contact؛
- Header و Footer؛
- صفحات عمومی؛
- طراحی Responsive؛
- Typography؛
- رنگ‌ها و Design System؛
- انیمیشن‌ها و تعاملات ظاهری؛
- Templateها و Patternهای WordPress؛
- نمایش محتوای WordPress؛
- Assetهای CSS/JS مربوط به presentation.

Theme **مسئول منطق تجاری Backend نیست**.

### Plugin = مغز Backend ووگیت

Plugin مسئول موارد زیر است:

- REST API؛
- Authentication و Session؛
- Site Identity؛
- Account lifecycle؛
- Trial؛
- Subscription؛
- Entitlement؛
- App Version Gate؛
- Credential Vault؛
- Gateway/Proxy؛
- کنترل دسترسی؛
- Rate limiting و Abuse protection؛
- Idempotency؛
- Timeout-after-success؛
- Reconciliation؛
- Audit Log؛
- Security controls؛
- Webhook/Event handling؛
- Queue/Job در صورت نیاز؛
- Admin Console مربوط به Backend؛
- جداول اختصاصی Backend؛
- سرویس‌های داخلی و business rules.

این قابلیت‌ها باید با تغییر Theme همچنان فعال بمانند.

---

## ۴. چیزهایی که عمداً نباید داخل Theme قرار بگیرند

موارد زیر نباید به `functions.php` یا سایر فایل‌های Theme منتقل شوند:

- Subscription logic؛
- Trial eligibility؛
- Site ownership؛
- Authentication؛
- Credential encryption/storage؛
- REST API اصلی Backend؛
- Version Gate؛
- Idempotency؛
- Audit؛
- Gateway/Proxy؛
- داده‌های حساس؛
- جداول Backend؛
- business rules؛
- عملیات امنیتی حیاتی.

دلیل: اگر Theme عوض یا غیرفعال شود، قابلیت‌های حیاتی سرویس نباید از بین بروند. WordPress نیز همین separation را به‌عنوان best practice توصیه می‌کند. urlWordPress Theme Functionalityhttps://developer.wordpress.org/themes/classic-themes/functionality/

`functions.php` فقط برای functionality محدود و ذاتاً وابسته به همان Theme قابل استفاده است؛ functionality مستقل از طراحی باید در Plugin باشد. urlWordPress Custom Functionality — functions.phphttps://developer.wordpress.org/themes/core-concepts/custom-functionality/

---

## ۵. Theme پیشنهادی WooGit

Theme اختصاصی WooGit برای **وب‌سایت عمومی محصول** ساخته می‌شود، نه برای اجرای منطق Backend.

ساختار پیشنهادی:

```text
 theme/woogit/
 ├── style.css
 ├── theme.json
 ├── functions.php
 ├── templates/
 ├── parts/
 ├── patterns/
 ├── assets/
 │   ├── css/
 │   ├── js/
 │   └── images/
 └── README.md
```

در صورت انتخاب Block Theme، `theme.json` و Site Editor بخش اصلی Design System و تنظیمات ظاهری خواهند بود. WordPress برای Block Themeها ساختار مبتنی بر template و `theme.json` را پشتیبانی می‌کند. urlWordPress Theme Handbookhttps://developer.wordpress.org/themes/

### صفحات عمومی موردنظر

حداقل ساختار طراحی:

```text
/
├── pricing/
├── features/
├── faq/
├── contact/
├── documentation/
├── login/
└── account/        ← فقط در صورت نیاز محصول
```

Theme می‌تواند صفحات بیشتری داشته باشد، اما اضافه شدن صفحه نباید باعث انتقال business logic از Plugin به Theme شود.

---

## ۶. Backend Plugin پیشنهادی

ساختار پیشنهادی Plugin:

```text
plugin/woogit-backend/
├── woogit-backend.php
├── src/
│   ├── API/
│   ├── Auth/
│   ├── Sites/
│   ├── Accounts/
│   ├── Subscriptions/
│   ├── Entitlements/
│   ├── Version/
│   ├── Credentials/
│   ├── Gateway/
│   ├── Operations/
│   ├── Idempotency/
│   ├── Reconciliation/
│   ├── Webhooks/
│   ├── Jobs/
│   ├── Audit/
│   └── Security/
├── admin/
│   ├── pages/
│   ├── assets/
│   └── views/
├── database/
├── tests/
├── assets/
└── README.md
```

نام‌گذاری نهایی کلاس‌ها و namespaceها باید با استاندارد PHP/WordPress پروژه تعیین شود، اما اصل جداسازی لایه‌ها ثابت است.

---

## ۷. Admin Console داخل WordPress

برای V1 نیازی به ساخت یک پنل مدیریتی مستقل با Laravel/React/Node نداریم.

Plugin یک **WooGit Admin Console** داخل WordPress ارائه می‌کند.

ساختار منطقی منو:

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

### App Versions

این بخش باید Version Policy را مدیریت کند:

```text
Latest Version        5.0.0
Recommended Version   5.0.0
Minimum Supported     3.0.0

Deprecated:
- 4.0.0
- 4.1.0
- 4.2.0
```

یک نسخه می‌تواند مستقل از minimum supported نسخه‌های دیگر deprecated شود. بنابراین Version Gate فقط یک `minimum_version` ساده نیست و deny-list نسخه‌های مشخص را نیز پشتیبانی می‌کند.

---

## ۸. مدیریت Siteها

Admin Console باید بتواند برای هر Site Identity حداقل این موارد را نمایش دهد:

```text
Site Identity
Domain
Owner / Account
Plan
Subscription status
Trial status
Connection status
Credential status
Last operation
Security status
Created at
Updated at
```

اطلاعات Products/Orders فروشگاه نباید به‌صورت Mirror دائمی در این بخش ذخیره شود مگر اینکه یک قابلیت مشخص و مستند به چنین داده‌ای نیاز داشته باشد.

---

## ۹. Plans و Entitlements

Plugin مرجع Plan و Entitlement سمت Backend خواهد بود.

نمونه Planها:

```text
Free
Pro
Business
Enterprise
```

هر Plan می‌تواند شامل موارد زیر باشد:

- feature flags؛
- request quota؛
- تعداد Site مجاز؛
- محدودیت عملیات؛
- محدودیت AI در صورت فعال بودن؛
- محدودیت‌های آینده.

اپ حق ندارد این محدودیت‌ها را تعیین کند؛ Client فقط مصرف‌کننده policy سمت سرور است.

---

## ۱۰. داده و Database

WordPress database برای داده‌های Backend استفاده می‌شود، اما نباید WooCommerce customer site را mirror کند.

برای تنظیمات کوچک و configuration از WordPress Options استفاده می‌شود.

برای داده‌های عملیاتی در حال رشد، جداول اختصاصی Plugin قابل استفاده هستند؛ WordPress رسماً ساخت جدول اختصاصی Plugin و migration/versioning آن را پشتیبانی می‌کند. citeturn0search0

نمونه حوزه‌های مناسب برای جدول اختصاصی:

```text
wp_woogit_sites
wp_woogit_subscriptions
wp_woogit_sessions
wp_woogit_operations
wp_woogit_idempotency
wp_woogit_audit_logs
```

نام نهایی جداول بعد از نهایی شدن Data Model تعیین می‌شود و باید از `$wpdb->prefix` استفاده کند.

---

## ۱۱. API و Admin باید از یک Service Layer استفاده کنند

یکی از قواعد مهم معماری:

```text
Android App
    │
    ▼
REST API
    │
    ▼
WooGit Services
    ▲
    │
Admin Console
```

یعنی business logic نباید در Controllerهای REST یا صفحات Admin تکرار شود.

مثلاً Subscription Check باید یک Service واحد داشته باشد که هم API و هم Admin از آن استفاده کنند.

این کار از divergence بین رفتار API و پنل مدیریت جلوگیری می‌کند.

---

## ۱۲. Theme و Plugin چگونه با هم ارتباط دارند؟

ارتباط مستقیم باید حداقلی باشد.

```text
Theme
  │
  │ presentation
  ▼
WordPress
  ▲
  │ hooks / APIs / rendering contracts
Plugin
```

Theme می‌تواند از داده‌های عمومی یا UI hooks ارائه‌شده توسط Plugin استفاده کند، اما نباید مستقیماً به جداول داخلی Plugin یا کلاس‌های داخلی آن وابسته شود.

در صورت نیاز به integration، یک interface/hook عمومی و مستند در Plugin تعریف می‌شود.

---

## ۱۳. تغییر Theme نباید Backend را خراب کند

این سناریو باید همیشه معتبر باشد:

```text
Theme A + Plugin
      ↓
Theme B + Plugin
      ↓
Backend همچنان فعال است
```

و همچنین:

```text
Plugin update
      ↓
Theme unchanged
      ↓
Website presentation unchanged
```

Theme نباید dependency سختی به implementation داخلی Plugin داشته باشد.

---

## ۱۴. تغییر Backend نباید طراحی سایت را مجبور به تغییر کند

مثلاً تغییر:

```text
Subscription implementation
Credential storage
Database schema
Gateway internals
```

نباید نیازمند تغییر Theme باشد، مگر اینکه قرارداد public UI عمداً تغییر کرده باشد.

---

## ۱۵. Security Boundary

مرز امنیتی اصلی داخل Plugin است:

```text
Public Website
     │
     ├── Public content
     │
     └── Public forms

Authenticated API
     │
     ▼
WooGit Backend Plugin
     │
     ├── Authentication
     ├── Authorization
     ├── Site ownership
     ├── Subscription
     ├── Entitlement
     ├── Version Gate
     ├── Rate Limit
     ├── Idempotency
     └── Gateway
```

Theme نباید بتواند با یک تغییر ظاهری یا template override، security policy Backend را دور بزند.

---

## ۱۶. CI و تست

چون Theme و Plugin در یک Repository هستند، CI مشترک خواهد بود اما تست‌ها باید تفکیک شوند:

```text
CI
├── PHP / Plugin tests
├── REST/API tests
├── Security tests
├── Database migration tests
├── Idempotency tests
├── Version Gate tests
├── Theme validation
├── PHP lint
├── JS/CSS lint (در صورت وجود)
└── Integration test: Theme + Plugin
```

هیچ قابلیت production-ready صرفاً به‌دلیل وجود فایل یا scaffold کامل تلقی نمی‌شود؛ باید با تست واقعی اثبات شود.

---

## ۱۷. Release و Deployment

Repository واحد است، ولی release artifactها می‌توانند جدا باشند:

```text
WooGit WordPress Repository
        │
        ├── Theme artifact
        │     └── woogit-theme.zip
        │
        └── Plugin artifact
              └── woogit-backend.zip
```

در V1 در صورت نیاز می‌توان هر دو را همراه یک Deployment بسته‌بندی کرد، اما Plugin نباید به فعال بودن Theme وابسته باشد.

---

## ۱۸. قانون طلایی پروژه

> **یک Repository، دو Component مستقل.**
>
> Theme ظاهر WooGit است.
>
> Plugin مغز Backend WooGit است.
>
> اشتراک Repository فقط برای مدیریت توسعه، تست و release است؛ نه برای مخلوط کردن مسئولیت‌ها.

این مرز از زمان توسعه اولیه تا Production باید حفظ شود.
