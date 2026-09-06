# ساختار پیشنهادی مخزن

با شروع پیاده‌سازی، ساختار واقعی باید با معماری WordPress V1 هماهنگ باشد و Placeholder صرفاً برای کامل شدن درخت ایجاد نشود.

```text
backend-site/
├── plugin/woogit-backend/
│   ├── src/
│   │   ├── API/
│   │   ├── Auth/
│   │   ├── Accounts/
│   │   ├── Sites/
│   │   ├── Subscriptions/
│   │   ├── Entitlements/
│   │   ├── CustomerSite/
│   │   ├── Operations/
│   │   ├── Idempotency/
│   │   ├── Reconciliation/
│   │   ├── Audit/
│   │   └── Security/
│   ├── admin/
│   ├── database/
│   └── tests/
├── theme/woogit/
├── docs/
└── .github/workflows/
```

## مرز دامنه

`Sites` مالکیت و Connection Metadata غیرحساس را مدیریت می‌کند؛ Customer Credentials در Domain Model یا Persistence نگهداری نمی‌شوند و فقط در Request مصرف می‌شوند.

## Persistence و تست

V1 از WordPress Database استفاده می‌کند. PostgreSQL/Redis/Queue مستقل بخشی از پیش‌فرض V1 نیستند.

تست‌ها باید شامل Unit، Feature، Integration و Security باشند؛ از جمله Account/Site isolation، Session expiration، Credential non-storage، SSRF، Idempotency و Timeout-after-success.

## Reliability

سناریوهای response-loss، retry، reconciliation و از دسترس خارج شدن Customer WordPress باید تست شوند. هیچ تست V1 نباید فرض کند Redis یا Credential Vault وجود دارد.
