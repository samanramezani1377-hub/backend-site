# مدیریت نمایش و محتوای WooGit Theme

> وضعیت: V1 — این فایل فقط Index مجموعه مستندات Theme Management است.

مستندات Theme Management برای جلوگیری از ایجاد یک فایل بزرگ، به چند سند موضوعی تقسیم شده است.

## ساختار

```text
docs/
├── THEME.md
├── THEME_API_CONTRACT.md
├── THEME_AUTH_FLOW.md
├── THEME_DESIGN_SYSTEM.md
├── THEME_RESPONSIVE_SPEC.md
├── THEME_TESTING.md
├── PRODUCT_BOUNDARIES.md
└── theme-management/
    ├── README.md
    ├── GENERAL.md
    ├── HOME.md
    ├── CONTENT.md
    ├── TRUST-AND-FOOTER.md
    └── SECURITY-AND-BOUNDARIES.md
```

## Theme Management چیست؟

Theme Management فقط برای **ظاهر، محتوا و Presentation وب‌سایت رسمی WooGit** است؛ مانند Logo، Hero، Features، How It Works، Pricing Presentation، FAQ، Footer، اطلاعات تماس، شبکه‌های اجتماعی و اینماد.

این بخش با Account، Authentication، Session، Site Ownership، Entitlement، Billing، WooCommerce، Products، Orders، Sync، Inventory یا Business Logic افزونه کاری ندارد.

## اسناد موضوعی

- [theme-management/README.md](./theme-management/README.md) — نمای کلی و نقشه اسناد
- [theme-management/GENERAL.md](./theme-management/GENERAL.md) — تنظیمات عمومی و Site Identity
- [theme-management/HOME.md](./theme-management/HOME.md) — Hero، Features، How It Works و Sectionهای صفحه اصلی
- [theme-management/CONTENT.md](./theme-management/CONTENT.md) — Pricing Presentation، FAQ و محتوای عمومی
- [theme-management/TRUST-AND-FOOTER.md](./theme-management/TRUST-AND-FOOTER.md) — Footer، تماس، Social Links و اینماد
- [theme-management/SECURITY-AND-BOUNDARIES.md](./theme-management/SECURITY-AND-BOUNDARIES.md) — امنیت، ذخیره تنظیمات و مرزبندی

## اصل جداسازی

```text
Theme Management
    ↓
Website Presentation / Content
    ↓
Public Website

WooGit Main Plugin
    ↓
Auth / Account / Site / Billing / Entitlement / Security / API

Android App
    ↓
WooCommerce Operations
```

فایل‌های موضوعی باید کوچک و مستقل باقی بمانند و تغییرات آینده در سند مربوط به همان حوزه ثبت شوند.
