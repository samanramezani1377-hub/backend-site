# قرارداد API ووگیت

این سند مرزهای عمومی موردنظر را تعریف می‌کند. قرارداد عمداً مستقل از فریم‌ورک است تا بتوان آن را در Laravel پیاده‌سازی کرد، بدون اینکه کلاینت اندروید به کلاس‌های داخلی وابسته شود.

## ۱. گروه‌های API

```text
/api/v1/auth
/api/v1/account
/api/v1/sites
/api/v1/subscription
/api/v1/gateway
/api/v1/bridge
/api/v1/chat
/api/v1/analytics
/api/v1/ai
```

## ۲. احراز هویت

### POST `/api/v1/auth/login`

حساب WooGit را احراز هویت می‌کند.

### POST `/api/v1/auth/refresh`

نشست را تازه‌سازی/چرخش می‌دهد. Refresh Token خام هرگز در لاگ ثبت نمی‌شود.

### POST `/api/v1/auth/logout`

نشست فعلی را لغو می‌کند.

## ۳. اتصال سایت

### POST `/api/v1/sites`

یک عملیات اتصال سایت ایجاد می‌کند.

نمونه مفهومی درخواست:

```json
{
  "url": "https://example.com",
  "wordpress_username": "admin",
  "wordpress_application_password": "..."
}
```

الزامات:

- ارتباط با WooGit از طریق HTTPS.
- هرگز بدنه درخواست را لاگ نکنید.
- URL را استاندارد و اعتبارسنجی کنید.
- از Idempotency Key استفاده کنید.
- پیش از ذخیره نهایی اعتبارسنجی کنید.
- فقط فراداده امن را برگردانید.

نمونه پاسخ:

```json
{
  "site_id": "opaque-site-id",
  "status": "connected",
  "display_name": "Example Store",
  "bridge": {
    "installed": false,
    "required": false
  }
}
```

خود اعتبار هرگز نباید در پاسخ ظاهر شود.

## ۴. فهرست سایت‌ها

### GET `/api/v1/sites`

سایت‌های متعلق به حساب احراز‌شده را برمی‌گرداند.

### GET `/api/v1/sites/{site_id}`

فراداده امن اتصال و مجوز سایت را برمی‌گرداند.

### DELETE `/api/v1/sites/{site_id}`

سایت را قطع اتصال می‌کند. این عملیات باید اعتبار ذخیره‌شده را لغو/حذف و طبق سیاست قطع اتصال، اعتبارهای Bridge را نیز نامعتبر کند.

## ۵. Gateway

نقطه پایانی عمومی Gateway نباید به یک Proxy بدون محدودیت تبدیل شود. عملیات Typed ترجیح دارند.

بد:

```text
POST /gateway?url=https://customer-site/...
```

خوب:

```text
POST /api/v1/gateway/sites/{site_id}/products/list
POST /api/v1/gateway/sites/{site_id}/orders/get
POST /api/v1/gateway/sites/{site_id}/media/upload
```

عملیات Typed امکان اعمال صریح مجوز، حسابرسی، محدودسازی نرخ و Idempotency را فراهم می‌کنند.

برای مهاجرت اولیه از اپ فعلی WooGit می‌توان از یک Proxy داخلی محدود استفاده کرد، اما باید فهرست مجاز مسیرهای WooCommerce/WordPress و متدهای HTTP را اعمال کند. ارسال URL دلخواه به مقصدهای خارجی ممنوع است.

## ۶. قرارداد تغییرات

هر عملیات CREATE/فعال‌سازی/Provisioning این هدر را می‌پذیرد:

```http
Idempotency-Key: 9c2c...
```

پاسخ باید شامل این ساختار باشد:

```json
{
  "operation_id": "opaque-operation-id",
  "status": "completed"
}
```

برای عملیات غیرهمزمان:

```json
{
  "operation_id": "opaque-operation-id",
  "status": "pending"
}
```

Retry کلاینت با همان کلید باید همان وضعیت عملیات قبلی را برگرداند.

## ۷. نقاط پایانی Bridge

Namespace پیشنهادی در سایت مشتری:

```text
/wp-json/woogit/v1/discovery
/wp-json/woogit/v1/health
/wp-json/woogit/v1/command
/wp-json/woogit/v1/events
/wp-json/woogit/v1/chat/config
```

فهرست نهایی باید به حداقل موردنیاز قابلیت‌های فعال کاهش یابد.

## ۸. چت

### POST `/api/v1/sites/{site_id}/chat/conversations`

یک مکالمه را با توجه به مجوز حساب ایجاد می‌کند.

### POST `/api/v1/chat/conversations/{conversation_id}/messages`

یک پیام اضافه می‌کند.

### GET `/api/v1/chat/conversations/{conversation_id}`

داده مکالمه مجاز را برمی‌گرداند.

برای Streaming از SSE/WebSocket با یک توکن نشست چت کوتاه‌عمر استفاده کنید. اطلاعات ورود حساس سایت نباید در اختیار مرورگر قرار گیرد.

## ۹. دریافت رویدادهای تحلیل

### POST `/api/v1/sites/{site_id}/events`

یک Batch محدود از رویدادهای Typed را می‌پذیرد.

الزامات:

- Schema سخت‌گیرانه؛
- حداکثر اندازه Batch؛
- حداکثر اندازه رویداد؛
- Rate Limit؛
- Deduplication/Event ID؛
- فیلتر حریم خصوصی؛
- پردازش غیرهمزمان.

## ۱۰. هوش مصنوعی

### POST `/api/v1/ai/chat`

درخواست AI مجاز را از طریق انتزاع ارائه‌دهندگان WooGit مسیریابی می‌کند.

درخواست باید یک مدل منطقی را مشخص کند، نه URL دلخواه یک ارائه‌دهنده.

```json
{
  "model": "balanced",
  "messages": [],
  "site_context": {
    "site_id": "opaque-site-id"
  }
}
```

بک‌اند پیکربندی واقعی ارائه‌دهنده و مدل را در سمت سرور تعیین می‌کند.

## ۱۱. قرارداد خطا

از یک ساختار پایدار و قابل پردازش توسط ماشین استفاده کنید:

```json
{
  "error": {
    "code": "subscription_expired",
    "message": "WooGit subscription has expired.",
    "request_id": "..."
  }
}
```

در پاسخ خطای محیط عملیاتی هرگز اعتبار، SQL، Stack Trace یا اسرار ارائه‌دهنده قرار ندهید.

## ۱۲. شناسه درخواست

هر درخواست یک Request ID تولیدشده توسط سرور دریافت می‌کند. در صورت امن بودن، این شناسه به لاگ داخلی منتقل و برای عیب‌یابی به کلاینت برگردانده می‌شود.

## ۱۳. نقطه تطبیق Idempotency

نقطه پیشنهادی برای اپ:

`GET /api/v1/operations/{operation_id}`

این نقطه به کلاینت اجازه می‌دهد بعد از Timeout وضعیت عملیات را بازیابی کند، بدون اینکه Mutation اصلی را دوباره اجرا کند.
