# ساختار WordPress ووگیت: Theme + WooGit Main Plugin

> وضعیت: **V1 — Locked**

## ۱. هدف Repository

`backend-site` محل WordPress اصلی WooGit و Backend آن است.

```text
backend-site/
├── theme/woogit/          ← وب‌سایت عمومی / Presentation
├── plugin/woogit-backend/ ← WooGit Main Plugin / Backend
├── docs/
└── .github/
```

این Repository شامل Android App یا `WooGit Gateway Plugin` سایت مشتری نیست.

## ۲. دو Plugin کاملاً مستقل

### WooGit Main Plugin

روی **WordPress اصلی WooGit** نصب می‌شود و Backend فعلی این پروژه است.

مسئولیت‌های اصلی:

- REST API؛
- WooGit Session؛
- Account lifecycle؛
- Site Identity؛
- Trial / Subscription؛
- Entitlement؛
- Version Gate؛
- Security / Rate Limit؛
- Controlled Forwarding به Customer Site؛
- Idempotency؛
- Timeout-after-success؛
- Reconciliation؛
- Audit؛
- Admin Console؛
- داده‌های عملیاتی Backend.

### WooGit Gateway Plugin

روی **WordPress/WooCommerce سایت مشتری** نصب می‌شود.

این Plugin با Main Plugin یکی نیست و **در فاز فعلی این Repository ساخته، refactor یا migrate نمی‌شود**.

هر عبارت `Gateway/Proxy` در Backend که به integration اشاره دارد، به logic داخل `WooGit Main Plugin` مربوط است؛ نباید آن را با Customer `WooGit Gateway Plugin` اشتباه گرفت.

## ۳. Theme

Theme فقط مسئول Presentation وب‌سایت اصلی WooGit است:

- Home؛
- Pricing؛
- Features؛
- FAQ؛
- Contact؛
- Documentation؛
- Header/Footer؛
- Responsive design؛
- Typography؛
- CSS/JS presentation.

Business logic، Authentication، Session، Subscription، Entitlement، Security و API نباید به Theme وابسته باشند.

## ۴. Backend Plugin

ساختار پیشنهادی:

```text
plugin/woogit-backend/
├── woogit-backend.php
├── src/
│   ├── API/
│   ├── Auth/              ← WooGit Session
│   ├── Accounts/
│   ├── Sites/
│   ├── Subscriptions/
│   ├── Entitlements/
│   ├── Version/
│   ├── CustomerSite/      ← controlled integration/forwarding
│   ├── Operations/
│   ├── Idempotency/
│   ├── Reconciliation/
│   ├── Audit/
│   └── Security/
├── admin/
├── database/
├── tests/
└── README.md
```

`CustomerSite/` نام پیشنهادی برای جلوگیری از ابهام با `WooGit Gateway Plugin` است. نام دقیق namespace بعداً با کد نهایی تعیین می‌شود.

## ۵. مدل Authentication

در Backend فقط یک مدل احراز هویت Client داریم:

```text
WooGit Session
    → Authentication / Authorization در Backend
```

Access Token + Refresh Token بخشی از معماری WooGit نیست.

برای Customer Site، Client چهار Credential فعلی خود را دارد:

```text
WP Username
WP Application Password
WC Consumer Key
WC Consumer Secret
```

این‌ها Credential مقصد هستند و برای احراز Backend نزد Customer WordPress/WooCommerce استفاده می‌شوند.

## ۶. Lightweight Forwarding

مسیر عادی:

```text
Android
  ↓
WooGit Session + site_id
+ Customer Credentials
+ operation/path/query/body
  ↓
WooGit Main Plugin
  ├─ Session
  ├─ Account
  ├─ Subscription / Trial
  ├─ Entitlement
  ├─ Site ownership
  ├─ Version / Security / Rate Limit
  └─ Idempotency where required
  ↓
CustomerSite / Controlled Forwarding
  ↓
Customer WordPress/WooCommerce
```

Backend نباید URL دلخواه Client را Forward کند و نباید برای عملیات عادی دیتابیس دوم WooCommerce بسازد.

## ۷. Credential Vault

Credential Vault **اختیاری** است.

در Proxy عادی:

```text
Client → Customer Credentials → Backend → Customer Site
```

Vault lookup برای هر Request اجباری نیست.

Vault فقط وقتی لازم است که قابلیت مشخصی به Credential پایدار بدون حضور Client نیاز داشته باشد، مثلاً برخی Background Job/Webhookها. چنین نیازی باید جداگانه تصویب و پیاده‌سازی شود.

Credential خام نباید در Log، Audit Metadata یا Error Response ذخیره شود.

## ۸. WordPress Database

V1 فقط به WordPress Database به‌عنوان Persistence اجباری نیاز دارد.

برای داده‌های کوچک از WordPress Options و برای داده‌های عملیاتی در حال رشد از custom tables با `$wpdb->prefix` استفاده می‌شود.

نمونه:

```text
wp_woogit_sites
wp_woogit_sessions
wp_woogit_subscriptions
wp_woogit_operations
wp_woogit_idempotency
wp_woogit_audit_logs
```

PostgreSQL، Redis یا Queue مستقل برای V1 اجباری نیستند و فقط با نیاز اثبات‌شده و تصمیم معماری جدید اضافه می‌شوند.

## ۹. Admin Console

Admin داخل WordPress Main Plugin قرار می‌گیرد:

```text
WooGit
├── Dashboard
├── Sites
├── Accounts
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

### Site View

حداقل:

```text
Site Identity
Domain
Owner / Account
Plan
Subscription status
Trial status
Connection status
Credential verification status
Last operation
Security status
Created at
Updated at
```

`Credential verification status` به معنی «Credential در Vault ذخیره شده» نیست. می‌تواند وضعیت آخرین Verification مقصد را نشان دهد، مانند:

```text
verified
invalid
not_verified
unknown
```

## ۱۰. Client Compatibility

Android App موجود یک Client خارجی است و Backend باید با surface واقعی آن هماهنگ شود.

Client فعلی حوزه‌های زیر را دارد:

- Connection؛
- Products؛
- Orders؛
- Product Categories؛
- Variations؛
- Attributes؛
- Terms؛
- Media؛
- Sync؛
- Conflicts؛
- Product/Order mutations.

Client فعلی در Presentation از page size برابر 30 برای Products/Orders/Media استفاده می‌کند و Backend Contract باید امکان تطبیق با این الگو را داشته باشد. fileciteturn226file0L1-L2

در Connection، Client چهار Customer Credential را در Secure Credential Store نگهداری می‌کند؛ Backend نباید برای V1 طراحی‌ای تحمیل کند که این Flow را مجبور به حذف یا جایگزینی بنیادی کند. fileciteturn229file1L23-L29

## ۱۱. مرزهای غیرقابل نقض

- Theme نباید Backend business logic را مالک شود.
- Android App نباید در این Repository بازسازی شود.
- `WooGit Gateway Plugin` سایت مشتری نباید در این Repository پیاده‌سازی شود.
- `WooGit Main Plugin` و `WooGit Gateway Plugin` نباید یکی فرض شوند.
- Access/Refresh Token نباید به قرارداد اضافه شود.
- Vault نباید برای Proxy عادی اجباری شود.
- Customer Credentials نباید Log شوند.
- Customer Site URL نباید به Proxy مقصد دلخواه تبدیل شود.
- WooCommerce Customer data نباید بدون نیاز Mirror شود.
- PostgreSQL/Redis/Queue مستقل نباید بدون تصمیم معماری جدید اجباری شوند.
