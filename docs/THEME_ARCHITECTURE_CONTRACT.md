# قرارداد اجرایی معماری Theme WooGit

> وضعیت: V1 — Architecture Contract Before Implementation

این سند ساختار `theme/woogit/` و مرز لایه‌ها را برای implementation مشخص می‌کند.

## 1. اصل معماری

سه مسیر مستقل وجود دارد:

```text
Android App
  ├──→ Customer WooCommerce
  │     └── Store Operations: Products / Orders / Inventory / Media / Sync
  │
  └──→ WooGit Backend
        └── Account / Auth / Web Session / Ownership / Entitlement / Authorization

WooGit Theme روی woogit.ir
  ├──→ WooGit Backend Public REST API
  └──→ WooCommerce خود woogit.ir، فقط از طریق adapter/orchestrator مجاز
        └── WooGit Plans / Orders / Payment / Payment History / Payment State
```

**Customer WooCommerce و WooCommerce روی `woogit.ir` دو سیستم کاملاً متفاوت‌اند.** Theme هرگز نباید به Customer WooCommerce دسترسی داشته باشد.

## 2. ساختار قطعی V1

```text
theme/woogit/
├── assets/
│   ├── css/
│   │   ├── foundation.css
│   │   ├── components.css
│   │   ├── pages.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── core.js
│   │   ├── navigation.js
│   │   ├── auth.js
│   │   └── portal.js
│   └── images/
├── inc/
│   ├── setup/
│   ├── admin/theme-management/
│   ├── api/
│   ├── auth/
│   ├── portal/
│   └── helpers/
├── templates/
│   ├── public/
│   ├── auth/
│   └── portal/
├── template-parts/
│   ├── header/
│   ├── footer/
│   ├── hero/
│   ├── features/
│   ├── pricing/
│   ├── faq/
│   └── portal/
├── functions.php
└── style.css
```

فایل جدید فقط در صورتی مجاز است که مسئولیت آن در همین قرارداد قرار گیرد یا ابتدا قرارداد به‌روزرسانی شود.

## 3. مرز لایه‌ها

### `inc/api/`
مرز ارتباط Theme با Backend Public REST API و adapterهای قراردادی داده WooCommerce خود `woogit.ir` است.

مجاز:
- HTTP transport؛
- headerهای قراردادی؛
- parse/normalize response؛
- mapping error؛
- فراخوانی adapter/orchestrator داخلی برای WooCommerce خود سایت.

ممنوع:
- Customer WooCommerce API؛
- Customer WooCommerce credentials؛
- Backend Database؛
- Plugin internal classes؛
- محاسبه Entitlement/Authorization؛
- جعل Billing/Payment truth.

### `inc/auth/`
Web Authentication و Web Session orchestration. App Session و Web Session کاملاً جدا هستند.

### `inc/portal/`
Orchestration داده مورد نیاز Overview، Subscription، Billing، Payments، Connected Site و Account/Security.

منابع authoritative باید حفظ شوند:
- Account / Ownership / Entitlement / Authorization / Web Session → Backend؛
- Plans / Orders / Payment / Payment History / Payment State مربوط به خرید WooGit → WooCommerce `woogit.ir`.

### `inc/admin/theme-management/`
فقط presentation/content settings مانند Logo، Hero، Features، FAQ، Footer و Pricing Presentation. این بخش authority برای Billing، Entitlement یا Payment نیست.

### `templates/`
فقط composition. Template می‌تواند View Data آماده‌شده توسط adapter/orchestrator را مصرف کند، اما نباید خودش HTTP، Database یا WooCommerce query اجرا کند.

### `template-parts/`
فقط reusable presentation. API call و Database query مستقیم ممنوع است.

## 4. Asset Contract

`assets/js/` فقط interaction و progressive enhancement است. Business decision برای Account، Ownership، Entitlement، Payment یا Authorization در JS انجام نمی‌شود.

`assets/css/` فقط presentation، layout و responsive behavior است.

## 5. Dependency Contract

```text
Theme Bootstrap
   ↓
inc/setup
   ↓
inc/api / inc/auth / inc/portal / inc/admin
   ↓
View Data
   ↓
templates
   ↓
template-parts
```

قواعد:

- `template-parts` → API مستقیم: **ممنوع**
- `templates` → HTTP/DB/WooCommerce مستقیم: **ممنوع**
- `assets/js` → PHP internals: **ممنوع**
- Theme → Plugin internal classes: **ممنوع**
- Theme → Backend Database: **ممنوع**
- Theme → Customer WooCommerce API: **ممنوع**
- Theme → WooCommerce خود `woogit.ir` از طریق adapter/orchestrator: **مجاز**
- API adapter → Backend Public REST API: **مجاز**
- Portal → API adapters: **مجاز**
- Theme Management → WordPress Settings/Media Library: **مجاز**

## 6. Billing و Payment Contract

Theme نباید Billing truth را از local state بسازد.

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

Payment Return proof پرداخت نیست. Order/payment state باید از WooCommerce خود `woogit.ir` و entitlement از Backend تأیید شود.

`/billing/activate-session` مسیر App-only است و Theme نباید آن را مصرف کند.

## 7. Security

Customer WooCommerce credentials هرگز توسط Theme ذخیره یا مدیریت نمی‌شوند. Web Session نیز نباید با App Session جایگزین یا locally revive شود.

`account_id` و `site_id` ورودی کاربر مرجع Authorization نیستند؛ Backend context معتبر مرجع است.
