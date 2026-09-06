# ساختار پیشنهادی مخزن

با شروع پیاده‌سازی، مخزن باید به‌تدریج به ساختار زیر نزدیک شود:

```text
backend-site/
├── app/
│   ├── Domain/
│   │   ├── Accounts/
│   │   ├── Sites/
│   │   ├── Subscriptions/
│   │   ├── Gateway/
│   │   ├── Bridge/
│   │   ├── Chat/
│   │   ├── Analytics/
│   │   └── AI/
│   ├── Application/
│   ├── Infrastructure/
│   └── Http/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   ├── Integration/
│   └── Security/
├── wordpress-control-plane/
│   └── woogit-admin/
├── docker/
├── docs/
└── .github/
    └── workflows/
```

## مرزهای دامنه

### Accounts

هویت، نشست‌ها، چرخه عمر حساب و مدیریت دستگاه/نشست.

### Sites

مالکیت سایت، وضعیت اتصال، سلامت و مراجع اعتبارها.

### Subscriptions

پلن‌ها، اشتراک‌ها، مجوزها و انقضا.

### Gateway

زنجیره مجوز و عملیات خروجی Typed.

### Bridge

Provisioning، مذاکره قابلیت‌ها و پروتکل Bridge.

### Chat

مکالمه‌ها، پیام‌ها، تخصیص اپراتور و نشست‌های بلادرنگ.

### Analytics

اعتبارسنجی رویداد، دریافت و تجمیع.

### AI

انتزاع ارائه‌دهنده، اندازه‌گیری مصرف، اعتبارها و ابزارها.

## لایه‌های تست

### Unit

منطق خالص مجوز، Entitlement، Idempotency و منطق دامنه.

### Feature

قراردادهای درخواست/پاسخ API و احراز هویت.

### Integration

PostgreSQL/Redis واقعی و یک نمونه کنترل‌شده WordPress برای تست.

### Security

- تلاش برای دسترسی بین حساب‌ها؛
- دور زدن اشتراک منقضی‌شده؛
- Site ID جعلی؛
- Replay درخواست‌های Bridge؛
- Payload بیش از حد بزرگ؛
- تلاش Proxy دلخواه/SSRF؛
- بررسی نشت اعتبارها.

### Reliability

تست‌های صریح برای:

- Timeout بعد از موفقیت مقصد؛
- درخواست تکراری؛
- پاسخ گمشده Webhook؛
- Retry پردازشگر؛
- از دسترس خارج شدن WordPress مشتری؛
- Restart شدن Redis؛
- Restart شدن فرایند API.

## قانون پیاده‌سازی

صرفاً برای کامل به‌نظر رسیدن درخت پروژه، ماژول‌های Placeholder خالی نسازید. یک پوشه زمانی اضافه شود که اولین پیاده‌سازی واقعی یا تست واقعی آن وجود داشته باشد.
