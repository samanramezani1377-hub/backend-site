# امنیت و مرزبندی Theme Management

## امنیت

تمام صفحات مدیریتی Theme باید:

- Capability مناسب را بررسی کنند
- Nonce معتبر داشته باشند
- ورودی‌ها را Sanitize کنند
- خروجی‌ها را Escape کنند
- از ذخیره اطلاعات حساس خودداری کنند

Theme Management نباید Credentialهای WooCommerce، Password، API Secret، Session Token یا اطلاعات امنیتی Backend را ذخیره کند.

## محل ذخیره تنظیمات

از مکانیزم استاندارد WordPress مانند Settings API و Options یا سازوکار Theme-native مناسب استفاده شود. انتخاب نهایی باید پس از بررسی معماری واقعی `theme/woogit/` انجام شود تا دو سیستم موازی مدیریت محتوا ساخته نشود.

تنظیمات Presentation نباید در جداول اختصاصی Backend Plugin یا سرویس‌های Business Logic ذخیره شوند.

## UX مدیریت

پنل مدیریت بهتر است شامل موارد زیر باشد:

- Preview فوری تصویر
- دکمه تغییر/حذف تصویر
- Drag & Drop برای Featureها، Stepها و FAQها
- وضعیت فعال/غیرفعال
- Save واضح
- پیام موفقیت/خطای واضح
- Live Preview در صورت امکان

## مرزبندی

```text
Theme Management
    ↓
Presentation / Website Content

WooGit Main Plugin
    ↓
Auth / Account / Site / Billing / Entitlement / Security / API

Android App
    ↓
WooCommerce Operations
```

Theme Management نباید شامل این موارد شود:

- Account Management
- Authentication / Authorization
- Session / Web Session
- Site Ownership
- Entitlement
- Subscription state
- Billing / Payment
- Products / Orders
- Sync / Inventory / Conflicts
- WooCommerce Credentials
- Backend Database
- API Business Logic

## V1 Checklist

- [ ] Logo و Favicon
- [ ] Hero و CTA
- [ ] Features و How It Works
- [ ] Pricing Presentation
- [ ] FAQ
- [ ] Footer و اطلاعات تماس
- [ ] Social Links
- [ ] اینماد
- [ ] Media Library و Preview
- [ ] Sanitization / Escaping / Nonce / Capability checks
- [ ] عدم ذخیره Credential و Session
- [ ] عدم وابستگی به Business Logic افزونه
