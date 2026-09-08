# مشخصات Theme WooGit

> وضعیت: V1 — Documentation Before Implementation

Theme WooGit دو نقش دارد: وب‌سایت رسمی محصول و Customer Portal. Theme وب‌اپ عملیاتی WooGit نیست.

## 1. معماری و مالکیت داده

```text
Android App
  ├──→ Customer WooCommerce
  │     └── Products / Orders / Sync / Inventory / Media / Store Operations
  │
  └──→ WooGit Backend
        └── Account / Auth / Web Session / Site Ownership / Entitlement / Authorization

WooGit Theme روی woogit.ir
  ├──→ WooGit Backend Public REST API
  └──→ WooCommerce خود woogit.ir، از طریق adapter/orchestrator مجاز
        └── WooGit Plans / Products / Orders / Payment / Payment History / Payment State
```

**Customer WooCommerce و WooCommerce روی `woogit.ir` جدا و مستقل هستند.**

Backend مرجع authoritative برای Account، Authentication، Web Session، Site Ownership، Entitlement و Authorization است.

WooCommerce خود `woogit.ir` مرجع authoritative برای داده تجاری خرید WooGit است: Plans/Products، Orders، Payment Method، Payment History و Order/Payment State.

Theme می‌تواند داده WooCommerce خود `woogit.ir` را برای presentation از طریق adapter/orchestrator مصرف کند، اما هرگز نباید به Customer WooCommerce متصل شود.

## 2. مرزهای Theme

Theme نباید پیاده‌سازی کند:

- Products، Orders و Order Detail عملیاتی مشتری؛
- Sync، Conflict Resolution و Inventory؛
- Media operations و Customer Store Forwarding؛
- Store Dashboard عملیاتی؛
- اتصال مستقیم به Customer WooCommerce؛
- تصمیم‌گیری Authorization یا Entitlement؛
- دسترسی مستقیم به Backend Database؛
- استفاده مستقیم از کلاس‌ها، سرویس‌ها، Migration یا Business Logic داخلی Plugin؛
- نگهداری Customer WooCommerce credentials.

Theme فقط Presentation، interaction و orchestration لازم برای داده قراردادی را انجام می‌دهد.

## 3. صفحات

```text
Public Website
├── Home
├── Features
├── How It Works
├── Pricing
├── FAQ
├── Documentation
├── Support
├── Service Status
├── Privacy
└── Terms

Customer Portal
├── Overview
├── Subscription
├── Billing
├── Payments
├── Connected Site
└── Account / Security
```

هیچ صفحه عملیاتی مدیریت فروشگاه مشتری در Theme وجود ندارد.

## 4. Authentication و Session

Login و Web Bootstrap از قرارداد Web استفاده می‌کنند. Backend Account، Site Ownership و اعتبار Credential را بررسی و Web Session صادر می‌کند.

دو Session کاملاً جدا هستند:

```text
Android App → X-WooGit-Session
Theme       → X-WooGit-Web-Session
```

Theme نباید App Session را برای Web reuse یا جعل کند و Session منقضی‌شده را locally revive کند.

Customer WordPress/WooCommerce credentials در Web Bootstrap فقط request-scoped هستند و نباید در DB، session پایدار، cache، cookie نامناسب، log، telemetry، HTML یا JavaScript ذخیره شوند.

## 5. Pricing، Subscription و Billing

Pricing data-driven است. Theme نباید price، currency، duration یا entitlement را hard-code یا محاسبه کند.

```text
Pricing / Checkout
      ↓
Backend eligibility + orchestration
      ↓
WooCommerce woogit.ir
      ↓
Order / Payment Gateway / Payment State
      ↓
Backend entitlement synchronization
      ↓
Theme presentation
```

Subscription/Entitlement از Backend خوانده می‌شود. Payment Method، Payment History و Order/Payment details خرید WooGit از WooCommerce خود `woogit.ir` می‌آید.

Timeout-after-success باید به‌صورت Unknown مدیریت شود؛ retry همان logical operation باید همان `Idempotency-Key` را حفظ کند و Checkout دوم با key جدید برای همان operation مجاز نیست.

Payment Return proof پرداخت نیست. query string مانند `success=1` trusted نیست؛ Order/Payment state از WooCommerce `woogit.ir` و Entitlement از Backend تأیید می‌شود.

`/billing/activate-session` برای Operational App Session **App-only** است و Theme نباید آن را مصرف کند.

## 6. API Contract خلاصه

Base API:

```text
/wp-json/woogit/v1/
```

Web Auth:

```text
GET  /account/requirements
POST /account/web-bootstrap
POST /web/login
POST /web/logout
GET  /web/me
POST /web/account/contact-email
POST /web/account/password
```

Billing:

```text
GET  /billing/plans
GET  /billing/status
POST /billing/checkout
POST /billing/activate-session   # App-only
```

Web Billing باید با Web Session کار کند؛ App Billing Session نباید در Theme مصرف شود.

Headerهای Web:

```text
X-WooGit-Client: web
X-WooGit-Client-Version: 1.0.0
X-WooGit-Web-Session: <web session>
```

Errorها با HTTP status و canonical `code` مصرف می‌شوند، نه متن آزاد.

## 7. UX State و Security

تمام API interactionهای مهم باید Loading، Success، Empty، Pending و Errorهای قابل recovery را پوشش دهند. mutation در Loading نباید duplicate شود.

`account_id` و `site_id` ارسالی کاربر مرجع Authorization نیستند؛ Backend context معتبر مرجع است.

Theme نباید business truth را با local state جعل کند و نباید برای دور زدن Backend یا WooCommerce خود سایت از internal Plugin APIs استفاده کند.

## 8. اسناد مرجع

- `THEME_ARCHITECTURE_CONTRACT.md` — ساختار و dependency boundaries
- `THEME_UX_FLOW.md` — UX flows و state contract
- `THEME_API_CONTRACT.md` — REST/API contract
- `THEME_AUTH_FLOW.md` — Web Auth/Session
- `theme/PAGES.md` — Page Inventory
- `theme/PORTAL.md` — Portal IA
- `theme/DESIGN-SYSTEM.md` — Design System
- `THEME_TESTING.md` — Testing/CI
- `THEME_MANAGEMENT.md` + `theme-management/*` — Theme content management
