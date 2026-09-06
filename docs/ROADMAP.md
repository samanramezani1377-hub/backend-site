# نقشه‌راه Backend WooGit

> مبنا: V1 سبک، هماهنگ با Android Client موجود و تصمیم‌های قفل‌شده معماری.

## مرحله ۰ — قرارداد و زیرساخت پایه

- تثبیت API Contract؛
- Session service برای **WooGit Session**؛
- Account / Site Identity؛
- Subscription / Entitlement؛
- structured errors و Request ID؛
- WordPress DB schema/migrations؛
- CI و تست‌های Backend؛
- بدون PostgreSQL/Redis/Queue مستقل اجباری.

### معیار پذیرش

- محیط تازه طبق مستندات بالا می‌آید؛
- تست‌ها در CI اجرا می‌شوند؛
- Secret در repository وجود ندارد؛
- Access/Refresh Token در contract یا data model وجود ندارد؛
- Session lifecycle قابل ایجاد، اعتبارسنجی، expiration و revoke است.

## مرحله ۱ — Connection Verification و Onboarding

- دریافت چهار Customer Credential از Client؛
- WordPress reachability/authentication؛
- WooCommerce verification؛
- Site Identity؛
- Existing/New Account flow؛
- Trial eligibility؛
- ایجاد/فعال‌سازی WooGit Session.

### معیار پذیرش

- سایت معتبر متصل می‌شود؛
- Credential نامعتبر صحیح رد می‌شود؛
- Verification هیچ mutation آزمایشی ایجاد نمی‌کند؛
- شکست Verification باعث موفق اعلام شدن Account/Trial/Session نمی‌شود؛
- Credential در API/Log ظاهر نمی‌شود.

## مرحله ۲ — Controlled Forwarding

- endpointهای کنترل‌شده برای Products، Orders، Variations، Attributes/Terms، Media و سایر surfaceهای موردنیاز Client؛
- Session validation؛
- Account status؛
- Trial/Subscription؛
- Entitlement؛
- Site ownership؛
- Version/Security/Rate Limit؛
- controlled destination resolution؛
- minimum transformation.

### معیار پذیرش

- Client با WooGit Session معتبر و Customer Credentials می‌تواند operation مجاز را اجرا کند؛
- Account منقضی‌شده یا غیرفعال به Customer Site دسترسی ندارد؛
- `site_id` حدسی یا متعلق به Account دیگر پذیرفته نمی‌شود؛
- URL دلخواه قابل Forward نیست؛
- Backend داده WooCommerce را بی‌دلیل Mirror نمی‌کند.

## مرحله ۳ — Idempotency و Timeout-after-success

- Idempotency برای CREATE و سایر mutationهای لازم؛
- operation identity؛
- operation status API؛
- request fingerprint؛
- Retry policy؛
- reconciliation؛
- تست integration برای response-loss بعد از موفقیت مقصد.

### معیار پذیرش

```text
CREATE → Customer SUCCESS → response lost → retry
                         ↓
                  same operation
                         ↓
                 previous result
```

Retry نباید resource تکراری ایجاد کند و استفاده مجدد از یک Idempotency-Key برای Request متفاوت باید Conflict باشد.

## مرحله ۴ — Optional persistent Credential support

این مرحله فقط در صورت نیاز واقعی به عملیات بدون حضور Client فعال می‌شود.

- Credential Vault رمزنگاری‌شده؛
- key version/rotation؛
- محدودسازی دسترسی؛
- background jobs یا webhooks نیازمند Credential پایدار.

### معیار پذیرش

- مسیر عادی Proxy به Vault وابسته نیست؛
- Credential خام Log نمی‌شود؛
- عملیات background بدون Client فقط با credential storage صریح و امن اجرا می‌شود.

## مرحله ۵ — Billing تجاری

- Trial ۱۵ روزه؛
- Plans؛
- Subscriptions؛
- Payment integration؛
- Payment webhook idempotency؛
- expiration enforcement.

### معیار پذیرش

- Trial توسط Backend کنترل می‌شود؛
- Trial به Site Identity/دامنه وابسته است؛
- Subscription و Entitlement توسط Backend اعمال می‌شوند؛
- Account منقضی‌شده قبل از outbound request مسدود می‌شود.

## مرحله ۶ — قابلیت‌های اختیاری

در صورت تصویب محصولی:

- Chat؛
- Analytics؛
- AI؛
- Webhooks؛
- Background Jobs.

هیچ‌کدام نباید بدون نیاز مشخص، مسیر اصلی Lightweight Proxy را سنگین کنند.

## مرحله ۷ — Gateway Plugin مستقل

`WooGit Gateway Plugin` روی Customer WordPress/WooCommerce یک پروژه/فاز مستقل است.

**این repository در V1 فعلی آن را پیاده‌سازی، refactor یا migrate نمی‌کند.**

## مرحله ۸ — Scale / Hardening آینده

فقط در صورت اثبات نیاز:

- PostgreSQL managed؛
- Redis؛
- Workerهای مستقل؛
- horizontal scaling؛
- advanced monitoring؛
- disaster recovery؛
- penetration testing؛
- privacy/legal hardening.

این موارد بخشی از زیرساخت اجباری V1 نیستند.

## Commercial V1 Gate

قبل از اعلام آمادگی تجاری، حداقل این موارد باید اثبات شوند:

1. WooGit Session authentication؛
2. Site isolation؛
3. Account/Subscription/Entitlement enforcement؛
4. Connection Verification؛
5. Idempotency برای CREATEهای موردنیاز؛
6. اثبات Timeout-after-success؛
7. controlled proxy و SSRF protection؛
8. Audit برای عملیات حساس؛
9. Backup/Restore؛
10. monitoring و operational readiness؛
11. نبود Secretهای حساس در repository، log و response؛
12. contract compatibility با Android Client موجود.

**Credential Vault برای Commercial V1 شرط عمومی و اجباری نیست**؛ فقط قابلیت‌هایی که واقعاً به credential پایدار نیاز دارند باید آن را فعال کنند.
