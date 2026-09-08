# قرارداد UX Flow تم WooGit

> وضعیت: V1 — قرارداد UX پیش از پیاده‌سازی
>
> **Visual baseline:** Home HTML prototype ارائه‌شده در این مرحله، مبنای بصری concrete برای hierarchy، navigation، responsive intent و component usage است. این baseline هیچ business truth یا API authority جدیدی تعریف نمی‌کند.

## 1. هدف و اصل مرجع

Theme نسخه وب App نیست و هیچ Flow عملیاتی برای Products، Orders، Sync، Conflicts، Inventory یا Store Dashboard ندارد.

مرز authoritative داده‌ها:

```text
Customer WooCommerce
  → عملیات فروشگاه مشتری

WooCommerce روی woogit.ir
  → Plans / Products / Orders / Payment / Payment History / Payment State مربوط به WooGit

WooGit Backend
  → Account / Site Ownership / Authentication / Web Session / Entitlement / Authorization
```

Theme نباید هیچ‌کدام از این authorityها را بازسازی یا جعل کند.

## 2. نقشه اصلی تجربه کاربر

```text
Visitor → Landing / Public Website
        → Pricing / Features / How It Works / FAQ
        → Login / Register
        → Web Authentication / Bootstrap
        → Web Session
        → Customer Portal
             ├── Overview
             ├── Subscription
             ├── Billing
             ├── Payments
             ├── Connected Site
             └── Account / Security
```

Public navigation و Portal navigation جدا هستند، ولی Design System مشترک دارند.

## 3. قراردادهای UX تفکیک‌شده

### Visual / High-Fidelity Direction

`docs/theme-ux/DESIGN.md`

Home در این قرارداد با یک ساختار concrete شامل Sticky Glass Header، Hero typography-first، Why/Features، How It Works، Product/App Preview، Pricing، FAQ، Final CTA و Footer تعریف شده است. Visual previewها presentation-only هستند و نباید به operational Store Dashboard تبدیل شوند.

در desktop، placement بصری Header برابر Logo چپ + Navigation مرکز + Login/Primary CTA راست است و RTL بودن متن نباید این placement را ناخواسته معکوس کند. در mobile، همان navigation به Hamburger + Menu Sheet منتقل می‌شود و Bottom Navigation وجود ندارد.

### Pages

`docs/theme-ux/PAGES.md`

Billing وضعیت اشتراک و وضعیت مالی فعلی را نمایش می‌دهد. Payment Method برای WooGit از WooCommerce روی `woogit.ir` می‌آید. تاریخچه تراکنش‌ها و جزئیات پرداخت نیز از WooCommerce روی `woogit.ir` از طریق adapter مجاز مصرف می‌شوند.

```text
Billing
├── Current Plan / Subscription  ← Backend
├── Entitlement / Next Billing   ← Backend
└── Payment Method               ← WooCommerce woogit.ir

Payments
├── Payment History              ← WooCommerce woogit.ir
├── Transaction Details          ← WooCommerce woogit.ir
└── Payment Status               ← WooCommerce woogit.ir
```

### Core Flows

`docs/theme-ux/FLOWS.md`

شامل Login، Register/Web Bootstrap، Portal Entry، Subscription، Checkout/Billing، Payment Return، Payments/History، Connected Site و Password Change/Logout است.

Checkout از Backend برای eligibility/orchestration استفاده می‌کند، اما Order و Payment در WooCommerce خود `woogit.ir` ایجاد/ثبت می‌شوند و پس از رویداد معتبر پرداخت، Entitlement در Backend به‌روزرسانی می‌شود.

### States / Responsive / Accessibility

`docs/theme-ux/STATES.md`

شامل Global UX State Model، Home prototype states، navigation، responsive/accessibility و Prototype Acceptance Criteria است.

## 4. مرز قرارداد UX

Prototype قرارداد بصری و UX است، نه جایگزین Design System، API Contract یا Backend Contract. Theme فقط adapter/orchestrator قراردادی را مصرف می‌کند و business logic Backend را تکرار نمی‌کند.

Theme می‌تواند به WooCommerce **خود `woogit.ir`** برای داده‌های تجاری WooGit دسترسی غیرمستقیم و قراردادی داشته باشد؛ اتصال مستقیم Theme به **Customer WooCommerce** ممنوع است.

Prototype HTML نباید به‌عنوان source of truth برای price، entitlement، authentication، authorization، payment state یا هر business rule دیگری تفسیر شود.

پس از تأیید Prototype، مرحله بعدی اجرای Theme طبق `THEME_ARCHITECTURE_CONTRACT.md` است.
