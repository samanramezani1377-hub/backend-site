# هویت حساب و اتصال WordPress در WooGit

## ۱. وضعیت این سند

این سند **مرجع قفل‌شده** برای Flow اتصال فروشگاه، ایجاد/ورود حساب WooGit و تعریف Credential اتصال WordPress است.

صفحه اول اتصال اپ از نظر طراحی و فیلدها قفل است و نباید برای پیاده‌سازی حساب/احراز هویت تغییر کند.

## ۲. صفحه اول اپ — طراحی قفل‌شده

صفحه اول اپ WooGit این اطلاعات را می‌گیرد:

- پروتکل اتصال: HTTPS یا HTTP؛ پیش‌فرض HTTPS.
- آدرس فروشگاه، مانند `senoobar.ir`.
- WooCommerce Consumer Key.
- WooCommerce Consumer Secret.
- نام کاربری WordPress.
- رمز عبور WordPress.

آدرس فروشگاه قبل از استفاده باید Canonical شود و پیشوندهای `https://` و `http://` از ورودی حذف شوند؛ پروتکل انتخاب‌شده توسط اپ اعمال می‌شود.

## ۳. تعریف دقیق «رمز عبور WordPress» در صفحه اتصال

**رمز عبوری که در صفحه اتصال WooGit وارد می‌شود، رمز عبور معمولی کاربر برای ورود به پنل `wp-admin` نیست.**

این فیلد باید در حالت استاندارد، **WordPress Application Password** باشد؛ یعنی یک Credential اختصاصی برای احراز هویت برنامه‌ای و دسترسی به API، نه رمز عبور تعاملی انسان.

Application Password به یک کاربر WordPress متصل است، اما برای ورود به `wp-admin` با فرم عادی WordPress استفاده نمی‌شود. این Credential برای برنامه‌ها، اسکریپت‌ها، اپ‌های موبایل و Integrationها طراحی شده و به‌صورت مستقل قابل لغو است. citeturn0search0turn0search3

بنابراین کاربر باید:

```text
WordPress Username
        +
Application Password
        ↓
احراز هویت API
```

و **نباید** این کار را انجام دهد:

```text
WordPress Username
        +
رمز اصلی ورود به wp-admin
        ↓
استفاده به‌عنوان Credential اتصال WooGit
```

### ۳.۱ نکته امنیتی

Application Password باید به‌عنوان Secret واقعی رفتار شود. توصیه می‌شود یک Application Password اختصاصی برای WooGit ساخته شود، نه اینکه یک Credential مشترک بین چند Integration استفاده شود. WordPress امکان لغو مستقل Application Password را فراهم می‌کند، بدون اینکه لازم باشد رمز اصلی حساب WordPress تغییر کند. citeturn0search0turn0search6

Application Password معمولاً با HTTP Basic Authentication برای REST API ارسال می‌شود و استفاده از آن باید روی HTTPS باشد. citeturn0search0turn0search3

## ۴. اعتبارسنجی اتصال اولیه

Flow قطعی:

```text
App
  -> اطلاعات صفحه اول
  -> TLS
  -> WooGit Gateway
  -> اعتبارسنجی WordPress/WooCommerce
```

اگر اتصال شکست بخورد:

```text
Connection Failed
  -> بدون ایجاد Account
  -> بدون Trial
  -> بدون Dashboard
```

اگر اتصال موفق باشد، Gateway Site Identity استانداردشده را پیدا یا ایجاد می‌کند.

## ۵. دامنه دارای حساب قبلی

اگر Site Identity قبلاً به یک حساب WooGit متصل باشد:

```text
اتصال موفق WordPress/WooCommerce
        ↓
Site Identity موجود
        ↓
حساب WooGit موجود
        ↓
احراز موفق اتصال به همان سایت
        ↓
احراز حساب متناظر
        ↓
ایجاد نشست WooGit
        ↓
Dashboard
```

در این مدل، **موفقیت اتصال واقعی به همان سایت Credential ورود حساب آن سایت است**. ورود عادی نباید نیازمند ایمیل، Google Account یا یک رمز عبور جداگانه WooGit باشد.

دامنه خام به‌تنهایی Credential نیست؛ مدرک دسترسی، موفقیت اعتبارسنجی واقعی Credentialهای WordPress/WooCommerce است.

## ۶. دامنه بدون حساب قبلی

اگر اتصال موفق باشد اما Site Identity حساب WooGit نداشته باشد:

```text
اتصال موفق
   ↓
Site Identity جدید
   ↓
ایجاد رکورد سایت/حساب
   ↓
صفحه دوم اپ
   ↓
ایمیل + نام + نام خانوادگی
   ↓
تکمیل حساب
   ↓
Trial ۱۵ روزه، در صورت واجدشرایط بودن
   ↓
ایجاد نشست WooGit
   ↓
Dashboard
```

صفحه دوم فقط پس از اتصال موفق نمایش داده می‌شود.

## ۷. قانون Trial

Trial به **Site Identity/دامنه** تعلق دارد، نه به Google Account یا ایمیل به‌تنهایی.

بنابراین:

```text
example.com + email-A -> Trial
example.com + email-B -> بدون Trial دوم
example.com + email-C -> بدون Trial سوم
```

تغییر ایمیل، تغییر Google Account یا ثبت دوباره اطلاعات شخصی نباید امکان دریافت Trial جدید برای همان Site Identity را ایجاد کند.

## ۸. نشست WooGit

پس از احراز موفق اتصال:

```text
WordPress/WooCommerce Credentials
        ↓
Gateway Verification
        ↓
Site Identity / Account Authentication
        ↓
Short-lived Access Token
        +
Rotating Refresh Token
        ↓
App
```

Credential خام سایت نباید برای عملیات عادی از Backend به اپ برگردانده شود.

## ۹. خزانه Credential

Backend در صورت نیاز برای عملیات بعدی باید Credentialهای سایت را در Credential Vault نگهداری کند:

```text
Raw Credential
    ↓ TLS
Backend memory
    ↓
Encryption / KMS
    ↓
Encrypted storage
```

Credential رمزگشایی‌شده فقط برای حداقل زمان لازم جهت درخواست خروجی استفاده شود.

نباید Credential خام یا Application Password متنی در لاگ، Analytics، Crash Report یا پاسخ API ثبت شود.

## ۱۰. اصل امنیتی نهایی

- رمز اصلی ورود `wp-admin` با Application Password یکی نیست.
- Application Password برای API و اتصال ماشینی است.
- WooGit باید Application Password را به‌عنوان Secret مدیریت کند.
- Application Password نباید در پاسخ‌های عادی API به اپ بازگردانده شود.
- Credential سایت باید فقط برای همان Site Identity قابل استفاده باشد.
- حساب WooGit و نشست WooGit از حساب کاربر WordPress مستقل هستند.
- سرور WooGit مرجع نهایی Account، Site Identity، Subscription و Entitlement است.

## ۱۱. منبع رسمی WordPress

مستندات رسمی WordPress تصریح می‌کند که Application Password برای دسترسی برنامه‌ای به API طراحی شده، قابل لغو به‌صورت مستقل است و برای ورود تعاملی به `wp-admin` استفاده نمی‌شود. همچنین استفاده از آن برای REST API روی HTTPS توصیه شده است. citeturn0search0turn0search3
