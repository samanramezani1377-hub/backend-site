# WooGit Backend

> وضعیت: V1 — Locked

این Repository Backend و WordPress stack ووگیت است. Android App پروژه‌ای مستقل است و Customer `WooGit Gateway Plugin` نیز کامپوننت مستقلی خارج از Scope V1 این Repository است.

## معماری اصلی

```text
WooGit Android
      ↓
WooGit Main Plugin / Backend
      ↓
Customer WordPress / WooCommerce
```

WooGit Main Plugin روی WordPress اصلی WooGit اجرا می‌شود و Account، Session، Site Identity، Subscription، Entitlement، Security و Controlled Forwarding را کنترل می‌کند.

## Customer Credentials — تصمیم قطعی V1

چهار Credential مقصد:

- WordPress Username؛
- WordPress Application Password؛
- WooCommerce Consumer Key؛
- WooCommerce Consumer Secret.

Client در صورت نیاز آن‌ها را همراه همان Request می‌فرستد. Backend فقط برای همان Request مصرف می‌کند.

**Backend V1 هرگز Customer Credential را در DB، Vault، persistent cache، Log، Telemetry، Audit یا Response ذخیره/افشا نمی‌کند.** هیچ `site_credentials` table یا Customer Credential Vault در V1 وجود ندارد.

مرجع این تصمیم: `docs/ADR-014-CUSTOMER-CREDENTIALS-NO-STORAGE-V1.md`.

## Session و Authorization

WooGit Session تنها مکانیزم احراز Client در Backend است. Session منقضی‌شده معتبر نیست و automatic re-login یک Session Creation جدید است که در آن Backend دوباره Account + Site Ownership + Entitlement را بررسی می‌کند.

## V1 responsibilities

- Connection Verification؛
- Account / Site Identity؛
- Trial / Subscription / Entitlement؛
- Controlled Forwarding و SSRF protection؛
- Idempotency و Timeout-after-success؛
- Audit / Security / Operations؛
- WordPress DB persistence.

## اسناد مرجع

- `docs/SCOPE.md`
- `docs/PRODUCT.md`
- `docs/ARCHITECTURE.md`
- `docs/API-CONTRACT.md`
- `docs/CLIENT_CONTRACT.md`
- `docs/DATA-MODEL.md`
- `docs/SECURITY.md`
- `docs/IDENTITY_AND_WP_CONNECTION.md`
- `docs/ONBOARDING_AND_REGISTRATION.md`
- `docs/BILLING.md`
- `docs/ROADMAP.md`
- `docs/WORDPRESS_MONOREPO.md`
- `docs/ADR-014-CUSTOMER-CREDENTIALS-NO-STORAGE-V1.md`
- `docs/ADR-015-CREDENTIAL-FAILURE-SEMTANTICS-V1.md`
- `docs/ADR-016-SESSION-LIFECYCLE-V1.md`
- `docs/ADR-017-CONCURRENT-SESSIONS-V1.md`
- `docs/ADR-018-PLAN-DOWNGRADE-SESSION-BEHAVIOR-V1.md`
- `docs/ADR-019-SESSION-EXPIRATION-PLAN-RENEWAL-V1.md`
- `docs/ADR-020-SESSION-RENEWAL-ON-PLAN-EXTENSION-V1.md`
- `docs/ADR-021-EXPIRED-SESSION-PLAN-STATE-V1.md`
- `docs/ADR-022-EXPIRED-SESSION-AUTO-RELOGIN-V1.md`
