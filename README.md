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

## ساختار WordPress Monorepo

دو لایه اصلی این Repository عمداً از هم جدا هستند:

```text
backend-site/
├── plugin/
│   └── woogit-backend/   ← WooGit Main Plugin / Backend
├── theme/
│   └── woogit/           ← WooGit Website / Theme / Presentation
├── docs/
└── .github/
```

### Plugin Documentation

تمام مستندات مربوط به **WooGit Main Plugin / Backend** در بخش Backend/Plugin قرار می‌گیرند و از منطق Backend، API، Authentication/Session، Account، Site Identity، Subscription، Entitlement، Security، Forwarding، Idempotency، Reconciliation، Audit و Operations پیروی می‌کنند.

- کد Plugin: `plugin/woogit-backend/`
- مستندات Backend: `docs/`
- ساختار و مرزبندی Plugin: `docs/WORDPRESS_MONOREPO.md`
- قرارداد API: `docs/API-CONTRACT.md`
- امنیت: `docs/SECURITY.md`
- اتصال و Identity: `docs/IDENTITY_AND_WP_CONNECTION.md`
- Onboarding و Registration: `docs/ONBOARDING_AND_REGISTRATION.md`
- Billing و Entitlement: `docs/BILLING.md`

### Theme Documentation

تمام مستندات مربوط به **WooGit Theme / Website / Presentation Layer** به‌صورت مستقل در سند اختصاصی Theme یعنی `docs/THEME.md` نگهداری می‌شوند و مستقیماً به پوشه کد Theme یعنی `theme/woogit/` ارجاع می‌دهند.

- کد Theme: `theme/woogit/`
- مستندات Theme: `docs/THEME.md`
- Theme فقط Presentation و Website UX/UI را مدیریت می‌کند و نباید Business Logic، Authorization، Entitlement، Customer Credential Storage یا Backend Security را دوباره پیاده‌سازی کند.

**مرزبندی قطعی:** Theme و Plugin دو component مستقل هستند. Theme نباید فایل‌های Plugin را include/copy کند و Plugin نباید برای Presentation به Theme وابسته شود. تعامل Theme با Backend فقط از طریق قراردادهای API پایدار و عمومی انجام می‌شود.

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

## اسناد Backend / Plugin

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

## اسناد Theme

- `docs/THEME.md`
- کد Theme: `theme/woogit/`

برای جزئیات کامل Presentation، صفحات، Login/Register، Pricing، Billing UI، Checkout، Dashboard، Design System، UX States و مرزبندی Theme با Plugin به `docs/THEME.md` مراجعه کنید.
