# ساختار WordPress ووگیت: Theme + WooGit Main Plugin

> وضعیت: V1 — Locked

## ۱. Repository

```text
backend-site/
├── theme/woogit/          ← وب‌سایت عمومی / Presentation
├── plugin/woogit-backend/ ← WooGit Main Plugin / Backend
├── docs/
└── .github/
```

این Repository شامل Android App یا Customer `WooGit Gateway Plugin` نیست.

## ۲. WooGit Main Plugin

روی WordPress اصلی WooGit نصب می‌شود و مسئول REST API، WooGit Session، Account، Site Identity، Trial/Subscription، Entitlement، Version Gate، Security، Controlled Forwarding، Idempotency، Reconciliation، Audit و Admin Console است.

## ۳. WooGit Gateway Plugin

روی WordPress/WooCommerce مشتری یک کامپوننت مستقل است و در V1 این Repository ساخته، refactor یا migrate نمی‌شود.

## ۴. Authentication

Backend فقط یک مدل احراز Client دارد: WooGit Session.

Customer Site با چهار Credential فعلی Client احراز می‌شود. این Credentialها **Request-scoped هستند و در V1 هرگز ذخیره نمی‌شوند**.

## ۵. Lightweight Forwarding

```text
Android
  ↓
WooGit Session + site_id + Customer Credentials
  ↓
WooGit Main Plugin
  ├─ Session / Account
  ├─ Subscription / Trial / Entitlement
  ├─ Site ownership
  ├─ Version / Security / Rate Limit
  └─ Idempotency
  ↓
Controlled Forwarding
  ↓
Customer WordPress/WooCommerce
```

URL دلخواه Client مجاز نیست.

## ۶. WordPress Database

V1 فقط به WordPress Database به‌عنوان Persistence اجباری نیاز دارد. داده‌های Account، Session، Site، Subscription، Entitlement، Idempotency و Audit در همین DB نگهداری می‌شوند.

Customer Credential در این DB ذخیره نمی‌شود.

## ۷. Admin Console

Admin داخل Main Plugin است. نمایش `Credential verification status` فقط وضعیت آخرین Verification است و هرگز به معنی ذخیره Credential نیست.

## ۸. مرزهای غیرقابل نقض

- Access/Refresh Token به قرارداد اضافه نشود.
- Customer Credential در DB/Vault/persistent cache ذخیره نشود.
- Customer Credential Log/Audit/Telemetry نشود.
- URL دلخواه به Proxy تبدیل نشود.
- Customer WooCommerce بدون نیاز Mirror نشود.
- PostgreSQL/Redis/Queue مستقل بدون تصمیم معماری جدید اجباری نشود.
