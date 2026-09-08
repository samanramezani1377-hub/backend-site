# قرارداد UX Flow تم WooGit

> وضعیت: V1 — قرارداد UX پیش از پیاده‌سازی
>
> این سند مرجع اصلی UX Flow و ورودی مستندات تفکیک‌شده Theme است. جزئیات صفحات، جریان‌ها، stateها، accessibility و visual direction در اسناد زیر نگهداری می‌شوند.

## 1. هدف و اصل مرجع

UX باید قبل از UI مشخص باشد. Backend مرجع Account، Site Ownership، Authentication، Session، Subscription، Billing و Entitlement است. Theme نسخه وب App نیست و هیچ Flow عملیاتی برای Products، Orders، Sync، Conflicts، Inventory یا Store Dashboard ندارد.

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

### Pages

`docs/theme-ux/PAGES.md`

این سند شامل UX صفحات اصلی است. **Billing** فقط وضعیت اشتراک و وضعیت مالی فعلی را نمایش می‌دهد؛ `Billing History` در آن وجود ندارد. تاریخچه تراکنش‌ها و جزئیات پرداخت در **Payments** نمایش داده می‌شود.

ساختار مفهومی:

```text
Billing
├── Current Plan
├── Next Billing
└── Payment Method

Payments
├── Payment History
├── Transaction Details
└── Payment Status
```

### Core Flows

`docs/theme-ux/FLOWS.md`

شامل Login، Register/Web Bootstrap، Portal Entry، Subscription، Checkout/Billing، Payment Return، Payments/History، Connected Site و Password Change/Logout است.

### States / Responsive / Accessibility

`docs/theme-ux/STATES.md`

شامل Global UX State Model، navigation، responsive/accessibility و Prototype Acceptance Criteria است.

## 4. مرز قرارداد UX

Prototype قرارداد بصری و UX است، نه جایگزین Design System، API Contract یا Backend Contract. Backend authority است و Theme نباید business logic جدیدی ایجاد کند.

پس از تأیید Prototype، مرحله بعدی اجرای Theme طبق `THEME_ARCHITECTURE_CONTRACT.md` است.
