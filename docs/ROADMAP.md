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

**وضعیت:** پیاده‌سازی پایه انجام شده؛ CI شامل PHP lint و security invariants است.

## مرحله ۱ — Connection Verification و Onboarding

- دریافت چهار Customer Credential از Client؛
- WordPress reachability/authentication؛
- WooCommerce verification؛
- Site Identity؛
- Existing/New Account؛
- Trial eligibility؛
- ایجاد/فعال‌سازی WooGit Session.

**قانون:** Verification read-only است و Credential فقط Request-scoped است.

**وضعیت:** پیاده‌سازی شده. Account با Email resolve نمی‌شود؛ Site verified مرز هویت و ownership است.

## مرحله ۲ — Controlled Forwarding

- Products، Orders، Variations، Attributes/Terms، Media و surfaceهای واقعی Client؛
- Session/Account/Subscription/Entitlement؛
- Site ownership؛
- Version/Security/Rate Limit؛
- controlled destination resolution؛
- minimum transformation.

**وضعیت:** enforcementهای Session/Account/Site/Entitlement، Version Gate، Rate Limit، Body Size و SSRF در runtime اضافه شده‌اند.

## مرحله ۳ — Idempotency و Timeout-after-success

- Idempotency برای CREATE و mutationهای لازم؛
- operation identity؛
- operation status؛
- request fingerprint؛
- Retry policy؛
- reconciliation؛
- integration test برای response-loss بعد از موفقیت مقصد.

**وضعیت:** idempotency state و operation identity پیاده‌سازی شده‌اند. Timeout به `unknown` می‌رود و retry همان key هرگز CREATE را دوباره forward نمی‌کند. Reconciliation واقعی مقصد و response-loss integration test هنوز نیازمند staging/customer-site است و نباید با تست استاتیک جعل شود.

## مرحله ۴ — Billing تجاری

- Trial ۱۵ روزه؛
- Plans؛
- Subscriptions؛
- Payment integration؛
- Payment webhook idempotency؛
- expiration enforcement.

## مرحله ۵ — قابلیت‌های اختیاری

Chat، Analytics، AI، Webhooks و Background Jobs فقط در صورت تصویب محصولی اضافه می‌شوند و نباید مسیر اصلی Proxy را سنگین کنند.

## مرحله ۶ — Gateway Plugin مستقل

`WooGit Gateway Plugin` روی Customer WordPress/WooCommerce پروژه/فاز مستقلی است و در این Repository در V1 پیاده‌سازی یا migrate نمی‌شود.

## مرحله ۷ — Scale / Hardening آینده

PostgreSQL، Redis، Worker مستقل، horizontal scaling، monitoring پیشرفته و disaster recovery فقط با نیاز اثبات‌شده و تصمیم معماری جدید اضافه می‌شوند.

## Commercial V1 Gate

قبل از آمادگی تجاری باید Session authentication، Site isolation، Account/Subscription/Entitlement enforcement، Connection Verification، Idempotency، Timeout-after-success، SSRF protection، Audit، Backup/Restore، monitoring و نبود Secret در repository/log/response **اثبات** شوند.

در این مرحله enforcementهای runtime و invariantهای CI پیاده‌سازی شده‌اند، اما **integration/staging validation برای DNS rebinding، concurrency و timeout-after-success و همچنین عملیات Backup/Restore/monitoring واقعی هنوز خارج از این commit‌هاست**.

**Customer Credential Storage شرط V1 نیست؛ در V1 ممنوع است.**
