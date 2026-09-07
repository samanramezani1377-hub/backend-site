# معماری صفحات Theme WooGit

## Public Website

```text
Home
├── Hero
├── Why WooGit
├── Features
├── How It Works
├── Pricing
├── FAQ
├── CTA
└── Footer
```

صفحات عمومی تکمیلی:

- Features
- Pricing
- Documentation
- Support
- Service Status
- Privacy
- Terms

## Header عمومی

Navigation پیشنهادی:

```text
WooGit
محصول | قابلیت‌ها | نحوه کار | قیمت | مستندات | پشتیبانی

[ورود] [شروع کنید]
```

در موبایل Navigation به منوی فشرده و accessible تبدیل می‌شود، بدون تغییر در semantics یا مقصد صفحات.

## Auth

Auth شامل Login و جریان onboarding/registration وب طبق قرارداد Backend است. فرم‌ها باید validation، loading، error و success state داشته باشند.

Login V1 از `Site URL + Web Password` استفاده می‌کند. Credentialهای WordPress/WooCommerce فقط در جریان اولیه‌ای که Backend تعریف کرده مصرف می‌شوند و Theme آن‌ها را نگهداری نمی‌کند.

## Pricing

Pricing باید از داده Backend تغذیه شود. Theme فقط price، currency، duration، features، limits و وضعیت پلن را نمایش می‌دهد. Checkout از مسیر قراردادی Backend انجام می‌شود.

## صفحات نتیجه و خطا

برای خطاهای عمومی، عدم دسترسی، session expiry، rate limit و server error باید UI قابل فهم و امن وجود داشته باشد. هیچ stack trace، SQL، secret یا جزئیات داخلی نمایش داده نشود.

## مرزهای صریح

این صفحات در Theme V1 وجود ندارند:

- Products
- Orders
- Order Detail عملیاتی
- Inventory
- Sync
- Conflicts
- Media operations
- Store Dashboard عملیاتی

این موارد متعلق به Android App و Backend هستند.
