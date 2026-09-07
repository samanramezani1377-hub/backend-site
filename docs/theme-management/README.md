# Theme Management

مستندات مدیریت نمایش و محتوای `WooGit Theme`.

این مجموعه فقط Presentation و محتوای وب‌سایت رسمی WooGit را پوشش می‌دهد و با Backend Plugin، Billing، Account، WooCommerce و عملیات فروشگاه قاطی نمی‌شود.

## ساختار

```text
docs/theme-management/
├── README.md
├── GENERAL.md
├── HOME.md
├── CONTENT.md
├── TRUST-AND-FOOTER.md
└── SECURITY-AND-BOUNDARIES.md
```

## اسناد

- [GENERAL.md](./GENERAL.md) — تنظیمات عمومی، Site Identity و محل مدیریت
- [HOME.md](./HOME.md) — Hero، Features، How It Works و سایر بخش‌های صفحه اصلی
- [CONTENT.md](./CONTENT.md) — Pricing Presentation، FAQ و محتوای عمومی
- [TRUST-AND-FOOTER.md](./TRUST-AND-FOOTER.md) — Footer، اطلاعات تماس، شبکه‌های اجتماعی و نماد اعتماد الکترونیکی (اینماد)
- [SECURITY-AND-BOUNDARIES.md](./SECURITY-AND-BOUNDARIES.md) — امنیت، محل ذخیره تنظیمات و مرزبندی با Backend/App

## اصل معماری

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

Theme Management نباید به پنل مدیریت Backend تبدیل شود.
