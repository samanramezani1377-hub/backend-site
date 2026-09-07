# قرارداد API تم WooGit

> وضعیت: V1 — قرارداد پیاده‌سازی
>
> این سند قرارداد ارتباط `theme/woogit/` با API عمومی WooGit است. تم نباید به کلاس‌ها، سرویس‌ها یا فایل‌های داخلی افزونه Backend وابسته شود.

## ۱. اصل مرجعیت

تم فقط مصرف‌کننده API است. Account، Site، احراز هویت، نشست، مالکیت، اشتراک، اعتبار دسترسی و Billing در Backend تعیین می‌شوند.

## ۲. آدرس API

مسیر پایه REST در WordPress:

```text
/wp-json/woogit/v1/
```

تم نباید مسیر API را حدس بزند یا endpointهای داخلی افزونه را مستقیماً فراخوانی کند.

## ۳. قراردادهای وب

مسیرهای وب V1 که Backend فعلاً ارائه می‌کند:

```text
GET  /account/requirements
POST /account/setup-web-credentials
POST /web/login
POST /web/logout
GET  /web/me
POST /web/account/contact-email
POST /web/account/password
GET  /web/billing/history
```

هدر نشست وب:

```text
X-WooGit-Web-Session
```

## ۴. Billing

مسیرهای Billing فعلی Backend:

```text
GET  /billing/plans
GET  /billing/status
POST /billing/checkout
POST /billing/activate-session
```

Theme فقط باید قراردادهایی را مصرف کند که Backend برای وب منتشر کرده است. اگر endpointی فقط برای App Session طراحی شده باشد، تم نباید آن را با جعل هدر یا تغییر payload مصرف کند.

## ۵. احراز هویت درخواست

هر endpoint باید طبق قرارداد خود احراز هویت شود. تم نباید `account_id`، `site_id` یا entitlement را از ورودی کاربر به عنوان مرجع دسترسی بپذیرد.

## ۶. نشست وب

نشست وب و نشست App کاملاً جدا هستند:

```text
App     → X-WooGit-Session
Theme   → X-WooGit-Web-Session
```

## ۷. خطاها

تم باید بر اساس `code` و وضعیت HTTP رفتار کند و پیام قابل‌فهم نمایش دهد. SQL، stack trace، Secret و جزئیات داخلی Backend نباید نمایش داده شوند.

کدهای مهم می‌توانند شامل این موارد باشند:

```text
invalid_session
account_inactive
site_not_owned
invalid_web_credentials
rate_limited
validation_error
payment_pending
payment_failed
server_error
```

برای کد ناشناخته باید رفتار امن و عمومی استفاده شود.

## ۸. Idempotency در Checkout

هر Checkout باید طبق قرارداد Backend دارای `Idempotency-Key` معتبر باشد. در retry همان عملیات، همان کلید باید حفظ شود تا یک خرید ناخواسته چندبار ایجاد نشود.

## ۹. بازگشت از پرداخت

بازگشت کاربر از درگاه به معنی موفقیت پرداخت نیست:

```text
بازگشت از درگاه
      ↓
استعلام وضعیت معتبر Billing
      ↓
تأیید پرداخت/اشتراک/Entitlement توسط Backend
      ↓
نمایش نتیجه نهایی
```

## ۱۰. سازگاری

Theme باید با نسخه قرارداد API هماهنگ باشد. تغییر معنای فیلد یا حذف آن بدون قرارداد سازگاری مجاز نیست.

## ۱۱. ممنوعیت‌ها

تم نباید:

- مستقیماً به WooCommerce مشتری وصل شود؛
- Credential مشتری را خارج از جریان قراردادی ثبت‌نام نگه دارد؛
- کلاس PHP افزونه را `include/require` کند؛
- Entitlement را خودش محاسبه کند؛
- موفقیت پرداخت را از URL تشخیص دهد؛
- نشست منقضی‌شده را معتبر اعلام کند.
