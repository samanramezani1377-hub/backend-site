# محدوده محصول و مدل تجاری WooGit Backend

## ۰. مرز قطعی این مخزن

این سند درباره **Backend ووگیت برای اپ اندروید موجود** است.

اپ Android WooGit قبلاً ساخته شده و یک پروژه مستقل است. بنابراین این مخزن مسئول ساخت، بازطراحی یا نگهداری UI و منطق داخلی اپ نیست. اپ فقط یک Client مصرف‌کننده APIهای این Backend است.

```text
Android App موجود
      │
      │ HTTPS / API
      ▼
WooGit Backend  ← این مخزن
      │
      ▼
WordPress / WooCommerce مشتری
```

وب‌سایت تجاری/پنل کنترل WooGit، در صورت وجود، یک سامانه جداگانه است. این Backend می‌تواند API و integration لازم برای آن را فراهم کند، اما ساخت خود آن سایت در محدوده این repository نیست.

## ۱. محصول

محصول این مخزن یک سرویس SaaS سمت‌سرور است که اپ موجود WooGit از طریق آن به سایت‌های WordPress/WooCommerce متصل می‌شود. Backend مسئول احراز هویت، Site Identity، اشتراک، مجوز، نگهداری امن اعتبارهای سایت، Gateway و عملیات تجاری است.

اجزای خارجی که Backend با آن‌ها کار می‌کند:

1. **اپ اندروید WooGit** — Client موجود و مستقل.
2. **WordPress/WooCommerce مشتری** — منبع داده فروشگاه.
3. **WooGit Bridge** — Integration component سمت WordPress در قابلیت‌هایی که به آن نیاز دارند.
4. **وب‌سایت/پنل تجاری WooGit** — سامانه جداگانه، در صورت وجود.

### مسئولیت‌های Backend

- Authentication و Session
- Site Identity و مالکیت سایت
- Account lifecycle
- Trial / Subscription
- Entitlement و Feature authorization
- Credential Vault
- API Gateway
- عملیات Typed برای WooCommerce
- WordPress/WooCommerce integration
- Webhook / Event ingestion
- Queue / Job / Retry
- Idempotency
- Timeout-after-success safety
- Rate limiting و Abuse protection
- Audit و Security
- Observability و Operations
- قابلیت‌های اختیاری Chat / AI / Analytics در صورت فعال بودن قرارداد محصول

## ۲. مسیر اتصال اپ موجود

### ۲.۱ صفحه اول اپ — طراحی قفل‌شده

صفحه اول فعلی اپ WooGit **از قبل ساخته شده و قفل است**. Backend نباید برای اجرای این Flow ظاهر، UI یا فیلدهای آن را تغییر دهد.

این صفحه موارد زیر را می‌گیرد:

- **پروتکل اتصال:** HTTPS یا HTTP؛ پیش‌فرض HTTPS.
- **آدرس فروشگاه:** مثلاً `senoobar.ir`.
- **WooCommerce Consumer Key**.
- **WooCommerce Consumer Secret**.
- **نام کاربری WordPress**.
- **WordPress Application Password**.

ورودی آدرس فروشگاه باید استاندارد شود و پیشوندهای `https://` و `http://` از مقدار جدا شوند؛ پروتکل انتخاب‌شده توسط Client اعمال می‌شود.

نکته: فیلد «رمز عبور WordPress» در قرارداد فعلی به معنی رمز عادی ورود `wp-admin` نیست؛ باید **Application Password** باشد، یعنی اعتبار برنامه‌ای مخصوص دسترسی API.

Backend همین اطلاعات را در لحظه اتصال دریافت و اتصال واقعی WordPress/WooCommerce را اعتبارسنجی می‌کند.

### ۲.۲ شکست اتصال

```text
App
  -> اطلاعات اتصال
  -> WooGit Backend
  -> اعتبارسنجی WordPress/WooCommerce
  -> شکست
  -> خطا به App
```

در این حالت:

- Account جدید ساخته نمی‌شود.
- Trial ایجاد نمی‌شود.
- Session تجاری ایجاد نمی‌شود.
- کاربر وارد Dashboard نمی‌شود.

علت خطا باید بدون افشای Secret یا جزئیات حساس به Client برگردد.

### ۲.۳ دامنه دارای حساب قبلی

```text
اطلاعات اتصال صفحه اول
        ↓
اعتبارسنجی موفق WordPress/WooCommerce
        ↓
کشف Site Identity
        ↓
حساب موجود
        ↓
احراز حساب متناظر با همان سایت
        ↓
Session WooGit
        ↓
App Dashboard
```

در این مدل، موفقیت اتصال واقعی به همان سایت نقش Credential ورود آن حساب را دارد. ورود عادی به ایمیل یا Google Account وابسته نیست.

Backend مرجع نهایی احراز هویت، مالکیت سایت، وضعیت حساب، Subscription و Entitlement است.

### ۲.۴ دامنه بدون حساب قبلی

```text
اطلاعات اتصال صفحه اول
        ↓
اعتبارسنجی موفق WordPress/WooCommerce
        ↓
Site Identity جدید
        ↓
مرحله تکمیل اطلاعات داخل App
        ↓
Email + نام + نام خانوادگی
        ↓
ایجاد Account
        ↓
Trial در صورت واجدشرایط بودن
        ↓
Session WooGit
        ↓
Dashboard
```

صفحه دوم فقط بعد از اتصال موفق نمایش داده می‌شود و حداقل این موارد را دریافت می‌کند:

- ایمیل
- نام
- نام خانوادگی

ایمیل برای هویت، ارتباطات و در صورت نیاز Email Verification یا Recovery استفاده می‌شود، اما Login عادی بر اساس ایمیل نیست.

### ۲.۵ قانون اصلی Trial

**۱۵ روز Trial به Site Identity/دامنه تعلق دارد، نه به Google Account یا صرفاً ایمیل.**

مثال ممنوع:

```text
example.com + gmail-A -> 15 روز
example.com + gmail-B -> 15 روز دیگر
example.com + gmail-C -> 15 روز دیگر
```

بک‌اند باید Trial history را برای Site Identity نگهداری کند و قبل از ایجاد Trial جدید بررسی کند که قبلاً مصرف نشده باشد.

### ۲.۶ اصل مالکیت سایت

دامنه خام Secret نیست و به‌تنهایی نباید برای دسترسی کافی باشد. اثبات دسترسی از موفقیت اعتبارسنجی واقعی WordPress/WooCommerce به‌دست می‌آید.

Site Identity باید Canonical شود تا تغییرات ظاهری مانند `http/https` یا slash انتهایی باعث ایجاد هویت دوم برای یک سایت نشود.

### ۲.۷ Credential Vault

اعتبارهای دریافت‌شده از صفحه اول باید فقط برای اتصال امن و عملیات مجاز Backend استفاده شوند.

پس از onboarding:

- Credential خام سایت نباید برای عملیات عادی به اپ برگردد.
- Backend در صورت نیاز آن را در Credential Vault رمزنگاری‌شده نگهداری می‌کند.
- Gateway در زمان اجرای عملیات اعتبار لازم را بازیابی می‌کند.
- دسترسی به Credential باید Audit و Least Privilege داشته باشد.

## ۳. Subscription و Entitlement

Trial و Subscription فقط در Backend معتبر هستند.

پیشنهاد تجاری اولیه:

- Trial: ۱۵ روز
- بسته‌های زمان‌محور: ۳۰، ۹۰، ۱۸۰ و ۳۶۵ روز

قیمت، محدودیت‌ها و واحد پول باید تنظیمات Backend باشند و نباید به‌صورت منطق ثابت در Client قرار بگیرند.

هر Plan می‌تواند شامل موارد زیر باشد:

```text
Plan
- مدت
- قیمت
- واحد پول
- محدودیت سایت
- Feature flags
- Rate/request limits
- مدت نگهداری تحلیل
- محدودیت چت
- اعتبار AI
```

نمونه قابلیت‌ها:

- محصولات
- سفارش‌ها
- مشتریان
- رسانه
- مدیریت افزونه‌ها
- مدیریت Bridge
- تحلیل
- رهگیری کاربران
- چت
- چت هوش مصنوعی
- عامل هوش مصنوعی
- خودکارسازی
- خروجی گرفتن

## ۴. راهبرد افزونه Bridge

Bridge یک Integration component سمت WordPress است و در این پروژه به‌عنوان بخشی از Backend contract تعریف می‌شود، نه بخشی از اپ.

Bridge عمداً باید Headless باشد و نباید برای استفاده عادی از WooGit به صفحه تنظیمات اجباری در `wp-admin` نیاز داشته باشد.

مسئولیت‌های Bridge می‌تواند شامل این موارد باشد:

- نقاط پایانی API احراز‌شده
- اعلام قابلیت‌ها
- ارتباط خروجی امن
- تزریق اختیاری دارایی‌های سمت کاربر
- Hookهای WordPress/WooCommerce
- اجرای فرمان‌های مجاز Backend
- گزارش سلامت و نسخه

## ۵. هوش مصنوعی تجاری — قابلیت اختیاری Backend

AI جزء هسته اجباری Backend برای اتصال پایه اپ نیست، اما اگر در محصول فعال شود باید پشت WooGit Backend قرار بگیرد.

### اعتبارهای مدیریت‌شده توسط WooGit

```text
مشتری -> پرداخت به WooGit
       -> اعتبار AI
       -> AI Gateway
       -> ارائه‌دهنده
```

### BYOK

```text
کلید ارائه‌دهنده مشتری
        -> Credential Vault
        -> AI Gateway
        -> ارائه‌دهنده
```

کلیدهای AI نباید به App یا WordPress مشتری واگذار شوند.

## ۶. چت — قابلیت اختیاری Backend

در صورت فعال شدن Chat، پیام‌ها باید از مسیر Backend عبور کنند:

```text
Client / Bridge
    -> WooGit Chat API
    -> انسان یا AI
    -> Backend
    -> Client / Bridge
```

این معماری امکان اعمال Subscription، Rate Limit، تاریخچه، Routing و Audit را فراهم می‌کند.

## ۷. تحلیل و رهگیری — قابلیت اختیاری Backend

در صورت فعال بودن Analytics، رویدادها به Backend ارسال و در Queue/Storage مناسب پردازش می‌شوند. WordPress مشتری نباید انبار اصلی Analytics باشد.

نمونه رویدادها:

- `page_view`
- `product_view`
- `search`
- `add_to_cart`
- `checkout_started`
- `order_completed`
- `chat_started`
- `chat_message`
- `user_login`

قوانین حریم خصوصی، نگهداری داده و رضایت باید متناسب با حوزه قضایی و حالت محصول تنظیم شوند.

## ۸. موارد خارج از هدف این مخزن

- ساخت یا بازطراحی Android App
- UI/UX اپ
- Compose / Navigation / State management اپ
- ConnectionScreen یا Dashboard اپ
- ساخت APK و CI مخصوص Android
- ذخیره رمزهای خام WordPress در اپ
- اجرای مدل بزرگ AI روی هاست WordPress مشتری
- تبدیل WordPress `wp-admin` به رابط اصلی اپ
- اعمال Subscription فقط در Client
- ساخت زیرساخت بیش از حد توزیع‌شده پیش از نیاز واقعی
