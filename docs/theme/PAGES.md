# معماری صفحات Theme WooGit

## Core UX Page Inventory — V1

این فهرست مرجع صفحات اصلی UX در V1 است. صفحات عمومی اطلاعاتی/حقوقی پایین‌تر از این فهرست جدا شده‌اند و جزو ۱۰ صفحه اصلی Prototype محسوب نمی‌شوند.

1. Home
2. Pricing
3. Login
4. Register / Web Bootstrap
5. Customer Portal — Overview
6. Subscription
7. Billing
8. Payments
9. Connected Site
10. Account / Security

`Payment Result` در صورت نیاز می‌تواند به‌صورت route/template مربوط به Payment Return وجود داشته باشد، اما صفحه اصلی مستقل در این فهرست نیست.

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

این صفحات informational/legal هستند و با ۱۰ صفحه اصلی UX بالا اشتباه گرفته نمی‌شوند.

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

## Customer Portal

```text
Customer Portal
├── Overview
├── Subscription
├── Billing
├── Payments
├── Connected Site
└── Account / Security
```

`Account / Security` یک تجربه واحد در V1 است؛ Active Sessions در V1 صفحه یا بخش نمایشی مستقل ندارد.

Portal navigation از Public navigation جداست؛ فقط visual language و component system مشترک هستند.

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
