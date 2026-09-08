# مدیریت نمایش و محتوای WooGit Theme

> وضعیت: V1 — Index مجموعه مستندات Theme Management.

Theme Management فقط مسئول **ظاهر، محتوا و Presentation وب‌سایت رسمی WooGit** است. این بخش با منبع داده تجاری WooGit یا Customer WooCommerce یکی نیست.

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

## مرز داده و مسئولیت

سه حوزه باید همیشه از هم جدا بمانند:

```text
Customer WooCommerce
    ↓
Products / Orders / Inventory / Media / Store Operations مشتری

WooCommerce روی woogit.ir
    ↓
WooGit Plans / Products / Orders / Payment / Payment History / Payment State

WooGit Backend
    ↓
Account / Auth / Web Session / Site Ownership / Entitlement / Authorization
```

Theme می‌تواند داده **WooCommerce خود `woogit.ir`** را برای نمایش Pricing، سفارش‌های WooGit، Payment Method و Payment History از طریق adapter/orchestrator مجاز مصرف کند؛ این به معنی دسترسی Theme به Customer WooCommerce نیست.

Theme Management به هیچ‌وجه نباید:

- به Customer WooCommerce متصل شود؛
- credential فروشگاه مشتری را نگهداری کند؛
- Entitlement یا Authorization را محاسبه کند؛
- Payment یا Order state را جعل کند؛
- به Database یا کلاس‌های داخلی Plugin برای دور زدن قرارداد دسترسی داشته باشد.

## Theme Management چیست؟

این بخش برای تنظیمات presentation مانند Logo، Hero، Features، How It Works، Pricing Presentation، FAQ، Footer، اطلاعات تماس، شبکه‌های اجتماعی و اینماد است.

**Pricing Presentation** فقط ظاهر و محتوای قابل مدیریت را کنترل می‌کند؛ قیمت، Currency، Order، Payment و وضعیت اشتراک authoritative از منابع قراردادی خود مصرف می‌شوند.

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
WooGit Theme
    ├── Backend Public REST API → Account / Auth / Session / Entitlement
    └── WooCommerce woogit.ir adapter → WooGit store/payment presentation

Customer WooCommerce
    ↓
فقط مسیر عملیاتی App → Customer Store
```

فایل‌های موضوعی باید کوچک و مستقل باقی بمانند و تغییرات آینده در سند مربوط به همان حوزه ثبت شوند.
