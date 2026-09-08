# WooGit Theme UX — Pages

> قرارداد UX صفحات اصلی Theme. Source of Truth داده‌ها طبق `docs/THEME_DATA_OWNERSHIP.md` است.

## Page Map

### Home

- Header
- Minimal Hero
- Primary CTA: «دریافت WooGit»
- WooGit App Preview: Products / Orders
- Features
- How It Works
- Pricing
- FAQ
- Final CTA
- Footer

### Pricing

قیمت، Currency، مدت و eligibility باید از منبع معتبر Pricing بیایند و hard-code نشوند. برای پلن‌های فروشی WooGit، داده محصول/پلن از WooCommerce خود `woogit.ir` می‌آید؛ entitlement و eligibility نهایی از Backend authority می‌آید.

### Login

```text
Welcome back
[ Site URL ]
[ Password ]
[ ورود ]
فراموشی رمز عبور؟
```

V1 بر Site URL + Web Password است.

### Register

Wizard چهارمرحله‌ای فروشگاه → اتصال → حساب → تأیید. Secretهای WordPress/WooCommerce مشتری فقط در جریان request و طبق قرارداد Backend مصرف می‌شوند و persistent نمی‌شوند.

### Portal Overview

Dashboard شامل account context، Subscription summary، Connected Site، Current Plan، Recent Billing و Quick Actions است.

Portal نباید Store Dashboard عملیاتی مشتری شود.

### Subscription

Current Plan، Status، Start/End/Renewal، Trial و actionهای مجاز از Backend authoritative نمایش داده می‌شوند.

### Billing

```text
Billing

Current Plan       ← Backend
Subscription state ← Backend
Payment Method     ← WooCommerce خود woogit.ir
```

Billing صفحه‌ای ترکیبی است؛ Payment Method نباید از یک فیلد ساختگی Backend خوانده شود.

### Payments

صفحه مستقل برای transaction/payment detail و history خریدهای WooGit. منبع داده WooCommerce خود `woogit.ir` است، از طریق adapter مناسب Theme. Template مستقیماً query نمی‌زند.

### Payment Result

نتیجه بازگشت Gateway ابتدا Checking/Pending است. query string به‌تنهایی proof پرداخت نیست. نتیجه Entitlement از Backend و payment/order detail از WooCommerce خود `woogit.ir` تأیید می‌شود.

### Connected Site

Account/Site information از Backend می‌آید. Theme مستقیماً به WooCommerce فروشگاه مشتری متصل نمی‌شود.

### Account / Security

Site URL، Contact Email، Password و Web Session security طبق قرارداد Backend مدیریت می‌شوند. Credentialهای WooCommerce مشتری در Portal نگهداری نمی‌شوند.

## Terminology rule

هرجا «WooCommerce» در این سند به کار می‌رود باید مشخص باشد منظور `WooCommerce خود woogit.ir` است یا `Customer WooCommerce`. این دو منبع داده کاملاً جدا هستند.

## Page-specific Glass Intensity

| صفحه | شدت Glass |
|---|---|
| Home | محسوس‌تر، اما کنترل‌شده |
| Pricing | Glass Cards |
| Login | محدود و تمیز |
| Register | محدود و تمیز |
| Portal Overview | ملایم و information-first |
| Subscription | ملایم و کاربردی |
| Billing | کنترل‌شده |
| Payments | کنترل‌شده |
| Connected Site | ملایم و status-focused |
| Account/Security | ملایم و کاربردی |
