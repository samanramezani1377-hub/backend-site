# صورتحساب و مجوزهای WooGit

> وضعیت: V1 — Locked

## ۱. مدل تجاری

- دوره آزمایشی رایگان ۱۵ روزه؛
- پلن‌های پولی زمان‌محور؛
- بسته‌های اختیاری اعتبار AI؛
- محدودیت اختیاری تعداد سایت یا قابلیت.

## ۲. زیرساخت فروش اشتراک

برای V1 فروش مدت استفاده و اشتراک WooGit روی WordPress اصلی WooGit با این ترکیب انجام می‌شود:

```text
WordPress اصلی WooGit
├── WooCommerce
├── WooCommerce Subscriptions
├── WooGit Main Plugin
└── WooGit Theme
```

`WooCommerce` مسئول فروش و پرداخت سفارش‌ها است و `WooCommerce Subscriptions` مرجع چرخه اشتراک تجاری مانند دوره، Renewal، Cancellation و وضعیت اشتراک است.

`WooGit Main Plugin` با WooCommerce Subscriptions یکپارچه می‌شود و وضعیت Billing را به مدل داخلی WooGit یعنی `Account`، `Subscription` و `Entitlement` منتقل/تطبیق می‌دهد.

**نکته:** WooCommerce Subscriptions جایگزین Authorization داخلی WooGit نیست. برای درخواست‌های محافظت‌شده، WooGit Main Plugin همچنان مرجع نهایی مجوز و Entitlement است.

## ۳. دوره آزمایشی

دوره آزمایشی در سمت سرور و هنگام واجد شرایط شدن حساب ایجاد می‌شود.

```text
trial_started_at
trial_ends_at
status = trial
```

اپ موبایل می‌تواند زمان باقی‌مانده را نمایش دهد، اما نمی‌تواند آن را تمدید کند.

## ۴. مدت پولی

خرید یک Subscription Product باید طبق قانون محصول، مدت مجاز دسترسی را ایجاد یا افزایش دهد.

نمونه:

```text
انقضای فعلی: 2026-09-21
خرید ۳۰ روز
انقضای جدید: 2026-10-21
```

اگر حساب از قبل منقضی شده باشد، شروع دوره جدید طبق سیاست Billing تعیین می‌شود.

## ۵. Renewal و Cancellation

چرخه Renewal و Cancellation در V1 توسط `WooCommerce Subscriptions` مدیریت می‌شود. WooGit Main Plugin باید تغییرات معتبر وضعیت Subscription را دریافت و مدل داخلی دسترسی را همگام کند.

```text
WooCommerce Subscription
        ↓
Billing event / verified state
        ↓
WooGit Main Plugin
        ↓
Subscription + Entitlement
        ↓
API Authorization
```

اپ مرجع وضعیت Billing نیست و نباید بتواند با Callback یا Timestamp محلی وضعیت اشتراک را جعل کند.

## ۶. ارزیابی مجوز

سیاست مفهومی:

```text
isAllowed(account, site, capability):
    account.status == active
    AND subscription.status in {trial, active}
    AND now < subscription.expires_at
    AND site belongs to account
    AND capability is included
    AND usage limits are not exceeded
```

نتیجه برای هر درخواست محافظت‌شده در سمت Backend محاسبه می‌شود.

## ۷. مهلت ارفاقی

اگر درگاه پرداخت استفاده شود، می‌توان یک مهلت ارفاقی قابل تنظیم داشت. این مهلت باید صریح و سمت‌سروری باشد؛ موفقیت پرداخت صرفاً از Callback کلاینت پذیرفته نمی‌شود.

## ۸. اعتبار AI

اعتبار AI در صورت نیاز تجاری از مدت اشتراک جدا باشد.

نمونه Ledger:

```text
+1,000,000 خرید
-12,400 مصرف استنتاج
-8,000 مصرف استنتاج
+500 اعتبار تبلیغاتی
```

هرگز اجازه ندهید کلاینت «موجودی باقی‌مانده» را به سرور اعلام کند.

## ۹. مرز درگاه پرداخت

رویدادهای پرداخت و Renewal باید از مسیرهای رسمی و قابل‌تأیید WooCommerce/WooCommerce Subscriptions به Backend منتقل شوند. رویدادهای دریافتی باید Idempotent پردازش شوند.

```text
Payment / Renewal Event
 -> بررسی اصالت و وضعیت
 -> ثبت تراکنش به‌صورت Idempotent
 -> به‌روزرسانی Subscription / Entitlement
 -> Audit
```

## ۱۰. پیکربندی پلن

پلن‌ها باید داده‌محور باشند. قیمت و مدت را در Android یا Customer Gateway به‌صورت Hard-code قرار ندهید.

هر پلن می‌تواند این موارد را تعریف کند:

- نام؛
- مدت؛
- قیمت؛
- واحد پول؛
- محدودیت سایت؛
- مجموعه قابلیت‌ها؛
- اعتبار AI؛
- مدت نگهداری تحلیل؛
- محدودیت چت.

محصولات Subscription و قیمت‌های فروش در WooCommerce مدیریت می‌شوند و WooGit Main Plugin باید mapping مشخصی بین محصول/Subscription و Plan داخلی داشته باشد.

## ۱۱. رفتار انقضا

در زمان انقضا:

- درخواست‌های API محافظت‌شده رد می‌شوند؛
- تماس خروجی با سایت مشتری مسدود می‌شود؛
- در صورت اجازه سیاست، داده UI کش‌شده قابل نمایش است؛
- Jobهای نیازمند مجوز متوقف یا Paused می‌شوند؛
- داده مشتری طبق سیاست نگهداری حذف یا نگهداری می‌شود.

## ۱۲. اصل امنیتی

کاربر نباید بتواند سرویس را با تغییر این موارد برگرداند:

- پرچم‌های APK؛
- Timestampهای محلی اشتراک؛
- داده کش‌شده پلن؛
- وضعیت محلی «Premium».

فقط WooGit Backend می‌تواند مجوز دسترسی صادر کند.

## ۱۳. مرز WooCommerce و WooGit

```text
WooCommerce / Subscriptions
    = فروش، سفارش پرداخت، Renewal و Cancellation

WooGit Main Plugin
    = Account، Site، Subscription داخلی، Entitlement و Authorization

Customer WooCommerce
    = داده واقعی فروشگاه مشتری
```

این WooCommerce مربوط به **فروش خود سرویس WooGit روی WordPress اصلی WooGit** است و با WooCommerce سایت مشتری یکی نیست.
