# نقشه‌راه Backend WooGit

> مبنا: V1 سبک، هماهنگ با Android Client موجود و تصمیم‌های قفل‌شده معماری.

## مرحله ۰ — قرارداد و زیرساخت پایه

- تثبیت API Contract؛
- WooGit Session؛
- Account / Site Identity؛
- Subscription / Entitlement؛
- structured errors و Request ID؛
- WordPress DB schema/migrations؛
- CI و تست‌های Backend؛
- بدون PostgreSQL/Redis/Queue مستقل اجباری.

## مرحله ۱ — Connection Verification و Onboarding

- دریافت چهار Customer Credential از Client؛
- WordPress reachability/authentication؛
- WooCommerce verification؛
- Site Identity؛
- Existing/New Account؛
- Trial eligibility؛
- ایجاد/فعال‌سازی WooGit Session.

**قانون:** Verification read-only است و Credential فقط Request-scoped است.

## مرحله ۲ — Controlled Forwarding

- Products، Orders، Variations، Attributes/Terms، Media و surfaceهای واقعی Client؛
- Session/Account/Subscription/Entitlement؛
- Site ownership؛
- Version/Security/Rate Limit؛
- controlled destination resolution؛
- minimum transformation.

## مرحله ۳ — Idempotency و Timeout-after-success

- Idempotency برای CREATE و mutationهای لازم؛
- operation identity؛
- operation status؛
- request fingerprint؛
- Retry policy؛
- reconciliation؛
- integration test برای response-loss بعد از موفقیت مقصد.

## مرحله ۴ — Billing تجاری

- Trial ۱۵ روزه؛
- Plans؛
- Subscriptions؛
- Payment integration؛
- Payment webhook idempotency؛
- expiration enforcement.

## مرحله ۵ — قابلیت‌های اختیاری

Chat، Analytics، AI، Webhooks و Background Jobs فقط در صورت تصویب محصولی اضافه می‌شوند و نباید مسیر اصلی Proxy را سنگین کنند.

Background Job/Webhook در V1 نباید به Customer Credential ذخیره‌شده متکی باشد.

## مرحله ۶ — Gateway Plugin مستقل

`WooGit Gateway Plugin` روی Customer WordPress/WooCommerce پروژه/فاز مستقلی است و در این Repository در V1 پیاده‌سازی یا migrate نمی‌شود.

## مرحله ۷ — Scale / Hardening آینده

PostgreSQL، Redis، Worker مستقل، horizontal scaling، monitoring پیشرفته و disaster recovery فقط با نیاز اثبات‌شده و تصمیم معماری جدید اضافه می‌شوند.

## Commercial V1 Gate

قبل از آمادگی تجاری باید Session authentication، Site isolation، Account/Subscription/Entitlement enforcement، Connection Verification، Idempotency، Timeout-after-success، SSRF protection، Audit، Backup/Restore، monitoring و نبود Secret در repository/log/response اثبات شوند.

**Customer Credential Storage شرط V1 نیست؛ در V1 ممنوع است.**
