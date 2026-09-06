# تصمیم‌های معماری WooGit

> این فایل Index تصمیم‌های فعال V1 است. در صورت تعارض، ADR قفل‌شده جدیدتر مرجع است.

## تصمیم‌های پایه

- Backend V1 روی WordPress اصلی WooGit و `WooGit Main Plugin` اجرا می‌شود.
- Android App یک Client مستقل است.
- Customer WordPress/WooCommerce منبع حقیقت داده فروشگاه است.
- `WooGit Gateway Plugin` سایت مشتری در V1 این Repository توسعه داده نمی‌شود.
- WordPress Database تنها Persistence اجباری V1 است.
- Proxy عمومی URL دلخواه مجاز نیست؛ Forwarding کنترل‌شده و Typed است.
- CREATE mutationها باید Idempotent باشند و Timeout-after-success باید قابل reconciliation باشد.

## ADR-014 — عدم ذخیره‌سازی Customer Credentials در V1

**وضعیت:** Accepted / Locked

Customer Credentialها در V1 هرگز در DB، Vault، persistent cache یا هر storage دائمی دیگری نگهداری نمی‌شوند. Client آن‌ها را در Request لازم ارسال می‌کند و Backend فقط همان Request را با آن‌ها پردازش می‌کند.

این تصمیم شامل عدم ثبت Credential در Log، Audit، Telemetry، Analytics، Crash Report و Response نیز هست.

## Session Lifecycle

- WooGit Session تنها مدل احراز Client در Backend است.
- Access Token + Refresh Token جزو V1 نیست.
- Session expiration authoritative است.
- Session منقضی‌شده locally revive یا extend نمی‌شود.
- Automatic re-login یک authentication/session-creation جدید است.
- در ایجاد Session جدید، Backend باید دوباره Account + Site Ownership + Entitlement را بررسی کند.

## موارد خارج از V1

- Customer Credential Vault یا persistent credential storage؛
- PostgreSQL/Redis/Queue مستقل بدون تصمیم معماری جدید؛
- Customer `WooGit Gateway Plugin`؛
- Mirror دائمی WooCommerce؛
- بازسازی Android App.

ADRهای تخصصی Session و Credential failure در فایل‌های `ADR-015` تا `ADR-022` مرجع جزئیات هستند.
