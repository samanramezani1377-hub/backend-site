# مرزبندی محصول WooGit

این سند مرجع مرز مسئولیت سه بخش اصلی محصول است.

## ۱. Theme

Theme = وب‌سایت رسمی + پرتال مشتری + اشتراک و Billing

مسئولیت‌ها:

- معرفی محصول و قابلیت‌ها؛
- Pricing؛
- Login/Register وب؛
- Account؛
- Subscription؛
- Billing و پرداخت‌ها؛
- سایت‌های متصل؛
- Security/Settings وب؛
- Documentation/Support/Legal.

## ۲. Android App

App = کلاینت عملیاتی فروشگاه مشتری

مسئولیت‌ها:

- اتصال عملیاتی به فروشگاه؛
- Products؛
- Orders؛
- Inventory؛
- Media؛
- Sync؛
- Conflict Resolution؛
- عملیات Commerce.

App نباید Billing یا Account authority را خودش تعیین کند.

## ۳. Main Plugin / Backend

Plugin = مرجع authoritative محصول

مسئولیت‌ها:

- REST API؛
- Account؛
- Site Identity؛
- Authentication؛
- Session؛
- Ownership؛
- Trial/Subscription؛
- Entitlement؛
- Billing؛
- Authorization؛
- Security؛
- Database و Domain Logic.

## ۴. قانون طلایی

```text
Theme → نمایش و تعامل وب
App   → عملیات فروشگاه
Plugin → حقیقت، امنیت و مجوز
```

هیچ بخش نباید مسئولیت بخش دیگر را با منطق موازی دوباره پیاده‌سازی کند.
