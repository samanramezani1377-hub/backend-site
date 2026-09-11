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
├── Milo Subscriptions
├── WooGit Main Plugin
└── WooGit Theme
```

`WooCommerce` مسئول محصولات، سفارش و پرداخت است و `Milo Subscriptions` مرجع چرخه Subscription مانند Trial، Billing Period، Renewal، Cancellation، Switching و وضعیت Subscription است. Milo در حالت WooCommerce با محصولات، Cart، Checkout و Orders خود WooCommerce یکپارچه می‌شود.

`WooGit Main Plugin` با lifecycle و hookهای Milo یکپارچه می‌شود و وضعیت Billing را به مدل داخلی WooGit یعنی `Account`، `Subscription` و `Entitlement` منتقل/تطبیق می‌دهد.

**نکته:** Milo Subscriptions جایگزین WooCommerce Subscriptions در V1 است و جایگزین Authorization داخلی WooGit نیست. برای درخواست‌های محافظت‌شده، WooGit Backend همچنان مرجع نهایی مجوز و Entitlement است.

## ۳. دوره آزمایشی

دوره آزمایشی در سمت سرور و هنگام واجد شرایط شدن حساب ایجاد می‌شود و در Milo به‌صورت Subscription واقعی ثبت می‌شود.

```text
trial_started_at
trial_ends_at
status = trial
```

اپ موبایل می‌تواند زمان باقی‌مانده را نمایش دهد، اما نمی‌تواند آن را تمدید کند.

## ۴. مدت پولی

خرید یک Subscription Product در Milo باید طبق قانون محصول، مدت مجاز دسترسی را ایجاد یا افزایش دهد.

نمونه:

```text
انقضای فعلی: 2026-09-21
خرید ۳۰ روز
انقضای جدید: 2026-10-21
```

اگر حساب از قبل منقضی شده باشد، شروع دوره جدید طبق سیاست Billing تعیین می‌شود.

## ۵. Renewal و Cancellation

چرخه Renewal و Cancellation در V1 توسط `Milo Subscriptions` مدیریت می‌شود. WooGit Main Plugin باید تغییرات معتبر وضعیت Subscription را از hookهای Milo دریافت و مدل داخلی دسترسی را همگام کند.

```text
WooCommerce Order / Payment
        ↓
Milo Subscription lifecycle event
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

رویدادهای پرداخت از مسیرهای رسمی و قابل‌تأیید WooCommerce و رویدادهای Subscription از lifecycle رسمی Milo به Backend منتقل می‌شوند. رویدادهای دریافتی باید Idempotent پردازش شوند.

```text
Payment / Subscription Event
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

محصولات Subscription و قیمت‌های فروش در WooCommerce/Milo مدیریت می‌شوند و WooGit Main Plugin باید mapping مشخصی بین Product/Variation، Milo Subscription و Plan داخلی داشته باشد.

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

## ۱۳. مرز WooCommerce، Milo و WooGit

```text
WooCommerce
    = Product، Order و Payment

Milo Subscriptions
    = Subscription، Trial، Billing Period، Renewal، Cancellation و Subscription Status

WooGit Main Plugin / Backend
    = Account، Site، Subscription داخلی، Entitlement و Authorization

Customer WooCommerce
    = داده واقعی فروشگاه مشتری
```

این WooCommerce مربوط به **فروش خود سرویس WooGit روی WordPress اصلی WooGit** است و با WooCommerce سایت مشتری یکی نیست.

## ۱۴. پیاده‌سازی V1 در اپ

پس از Verification موفق، حتی اگر Trial تمام شده یا Plan فعال وجود نداشته باشد، Backend برای Account + Site یک Session صادر می‌کند. این Session برای ورود به حساب و Billing قابل استفاده است، اما مجوز ارسال درخواست به Customer Site ندارد.

```text
Verify Customer Site
        ↓
Account + Site
        ↓
Session
        ├── Billing / Account: مجاز
        └── Customer Gateway: فقط با Entitlement معتبر
```

در نتیجه App می‌تواند کاربر را وارد حساب کند و صفحه پرداخت را نمایش دهد، بدون اینکه در حالت بدون Plan بتواند Product/Order/Customer API را از طریق `/forward` اجرا کند.

### ۱۴.۱ پلن‌ها

`GET /api/v1/billing/plans` پلن‌های Subscription منتشرشده و قابل خرید WooCommerce/Milo را برمی‌گرداند. App قیمت، ارز یا مدت را hard-code نمی‌کند.

### ۱۴.۲ ایجاد پرداخت

`POST /api/v1/billing/checkout` فقط با Session معتبر انجام می‌شود. `account_id` و `site_id` از Session استخراج می‌شوند و Client حق انتخاب حساب دیگری را ندارد. Backend یک Order روی WooCommerce اصلی WooGit ایجاد می‌کند و آن را با Account/Site مرتبط می‌کند.

پاسخ شامل `payment_url` است و App کاربر را به صفحه پرداخت همان Order هدایت می‌کند.

### ۱۴.۳ تأیید پرداخت و فعال‌سازی

پرداخت از سمت App تأیید نمی‌شود. پس از پرداخت موفق، WooCommerce رویدادهای پرداخت خود را اجرا می‌کند و Milo lifecycle مربوط به Subscription را اجرا می‌کند. WooGit Backend از رویدادهای server-side، Account/Site موجود در metadata سفارش را resolve کرده و Entitlement را به `active` تبدیل می‌کند.

```text
App
 ↓
Billing Checkout
 ↓
WooCommerce Order
 ↓
Payment Gateway
 ↓
WooCommerce payment event
 ↓
Milo Subscription lifecycle
 ↓
WooGit Entitlement = active
 ↓
/forward مجاز می‌شود
```

Renewal نیز از مسیر lifecycle Milo به Backend همگام می‌شود. لغو اشتراک دسترسی را زودتر از سیاست انقضای واقعی قطع نمی‌کند؛ پس از پایان entitlement، `/forward` دوباره مسدود می‌شود.

## ۱۵. Billing Anti-Abuse / Rate Limiting

Billing در V1 یک لایه Rate Limit مستقل از `/forward` دارد. هدف این لایه جلوگیری از مصرف بی‌رویه منابع، ایجاد Orderهای متعدد و فشار غیرضروری روی WooCommerce و دیتابیس است؛ جایگزین Authorization یا Entitlement نیست.

Policyها در بازه‌های ۶۰ ثانیه‌ای اعمال می‌شوند:

| Endpoint | Limit | کلیدهای مستقل |
|---|---:|---|
| `GET /billing/plans` | 60/min | IP |
| `GET /billing/status` | 30/min | IP + Account/Site + Session |
| `POST /billing/checkout` | 5/min | IP + Account/Site |
| `POST /billing/activate-session` | 5/min | IP + Account/Site + Session |

برای endpointهای حساس، عبور از یکی از bucketها کافی نیست؛ همه bucketهای مربوط باید مجاز باشند. IP فقط یکی از لایه‌ها است و محدودیت Account/Site یا Session را دور نمی‌زند. کلید Session در Rate Limit به‌صورت SHA-256 مشتق می‌شود و خود توکن خام ذخیره نمی‌شود.

در صورت عبور از حد، API پاسخ `429` با `Retry-After` و قرارداد استاندارد `RATE_LIMITED` برمی‌گرداند. خطای ذخیره‌سازی Rate Limit نیز fail-closed است تا خرابی لایه محدودسازی باعث بازشدن مسیر حساس نشود.

این Rate Limit مستقل از محدودیت شدیدتر `/forward` است و به Authorization موجود Billing اضافه می‌شود: Session معتبر، Account فعال، Site ownership و Entitlement همچنان قبل از عملیات نهایی بررسی می‌شوند.
