# مرز قطعی پروژه WooGit Backend

## ۱. هدف

این مخزن برای **ساخت Backend اپ موجود WooGit** است.

اپ اندروید WooGit قبلاً ساخته شده و پروژه مستقل خودش را دارد. **در وضعیت فعلی، اپ مستقل از Backend است و بدون وابستگی به این مخزن اجرا می‌شود. پس از آماده و عملیاتی شدن Backend، اپ به این Backend متصل خواهد شد و از آن به‌عنوان سرویس سمت سرور استفاده می‌کند.**

این مخزن نباید اپ را دوباره بسازد، معماری داخلی آن را بازطراحی کند یا مسئولیت‌های UI/UX آن را بر عهده بگیرد.

## ۲. تفکیک کامپوننت‌های WooGit

دو پلاگین با نام WooGit در معماری وجود دارند و **کاملاً مستقل هستند**:

### WooGit Gateway Plugin

پلاگینی است که روی **WordPress/WooCommerce سایت مشتری** نصب می‌شود.

```text
Customer WordPress/WooCommerce
        └── WooGit Gateway Plugin
```

این کامپوننت در این مرحله **موضوع توسعه نیست**. فعلاً فقط در معماری به‌عنوان یک کامپوننت مستقل شناخته می‌شود و طراحی/پیاده‌سازی/Refactor آن به فاز جداگانه موکول است.

### WooGit Main Plugin

پلاگین مربوط به **سایت اصلی WooGit/WordPress ووگیت** است.

```text
WooGit Main Website / WordPress
        └── WooGit Main Plugin
```

این پلاگین با `WooGit Gateway Plugin` سایت مشتری یکی نیست و نباید در مستندات یا پیاده‌سازی با آن ادغام شود.

## ۳. مسئولیت این مخزن

Backend باید سرویس‌های زیر را برای اپ موجود فراهم کند:

- Authentication و Session
- Site Identity و مالکیت سایت
- Account lifecycle
- Subscription / Trial
- Entitlement و Feature authorization
- Secure credential handling
- API Gateway / controlled proxy
- عملیات کنترل‌شده برای WooCommerce
- WordPress/WooCommerce integration
- Idempotency
- Timeout-after-success safety
- Rate limiting و Abuse protection
- Audit و Security
- Observability و Operations
- قابلیت‌های اختیاری Chat / AI / Analytics در صورت قرار گرفتن در قرارداد محصول

**`WooGit Gateway Plugin` جزو کار فعلی این مخزن نیست.** Backend نباید برای تکمیل آن منتظر بماند و نباید سورس یا منطق آن را در این repository بازسازی کند.

## ۴. چیزهایی که خارج از مسئولیت این مخزن هستند

- ساخت یا بازطراحی اپ Android
- UI/UX اپ
- Compose، Navigation یا State management اپ
- پیاده‌سازی ConnectionScreen اپ
- پیاده‌سازی Dashboard اپ
- منطق محلی اپ به‌عنوان مرجع مجوز
- بسته‌بندی APK
- CI مخصوص Android، مگر برای تست قرارداد API Backend
- **پیاده‌سازی یا بازطراحی WooGit Gateway Plugin روی سایت مشتری در فاز فعلی**
- ادغام WooGit Gateway Plugin با WooGit Main Plugin

## ۵. قرارداد با اپ موجود

Backend باید با رفتار فعلی اپ به‌عنوان یک Client واقعی سازگار شود.

**در مرحله فعلی، اپ و Backend دو پروژه مستقل هستند. استقلال فعلی اپ به معنی حذف یا لغو اتصال آینده نیست؛ هدف این پروژه آماده‌سازی Backend و API Contract لازم است تا پس از آماده شدن Backend، اپ موجود به آن متصل شود.**

صفحه اول اپ قفل است و این فیلدها را ارسال می‌کند:

1. پروتکل HTTPS/HTTP
2. دامنه فروشگاه
3. WooCommerce Consumer Key
4. WooCommerce Consumer Secret
5. WordPress username
6. WordPress Application Password

Backend ابتدا اتصال واقعی WordPress/WooCommerce را بررسی می‌کند.

- شکست اتصال → خطا، بدون ایجاد Account و Trial.
- اتصال موفق + Site Identity موجود → احراز حساب همان سایت و ایجاد Session.
- اتصال موفق + Site Identity جدید → دریافت email/نام/نام خانوادگی، ایجاد Account و Trial در صورت واجدشرایط بودن.

در تمام حالت‌ها Backend مرجع نهایی مجوز و وضعیت تجاری است.

## ۶. وابستگی‌های خارجی

این پروژه می‌تواند به اجزای دیگری وابسته باشد، اما آن‌ها را مالک نمی‌شود:

- Android App موجود WooGit: Client فعلی مستقل و Client آینده Backend
- WordPress/WooCommerce مشتری: منبع داده فروشگاه
- **WooGit Gateway Plugin: کامپوننت مستقل روی سایت مشتری، خارج از scope فعلی**
- WooGit Main Plugin: پلاگین مستقل سایت اصلی WooGit
- WooGit commercial website/control panel: سامانه جداگانه در صورت وجود

وجود این اجزا در معماری به معنی قرار گرفتن سورس آن‌ها در این repository نیست.

## ۷. قانون جلوگیری از Scope Drift

هر قابلیت جدید باید ابتدا مشخص کند:

```text
آیا این قابلیت برای اجرای Backend لازم است؟
        │
   ┌────┴────┐
   │         │
  بله       خیر
   │         │
Backend    پروژه/سرویس مربوطه
```

اگر قابلیت متعلق به Android App باشد، نباید در این repository پیاده‌سازی شود.

اگر قابلیت متعلق به `WooGit Gateway Plugin` روی سایت مشتری باشد، در فاز فعلی نباید در این repository پیاده‌سازی یا بازطراحی شود و باید به پروژه/فاز مستقل Gateway منتقل شود.

اگر قابلیت متعلق به `WooGit Main Plugin` یا سایت تجاری/پنل مدیریت باشد، باید با همان کامپوننت مستقل خودش مدیریت شود و Backend فقط API یا integration موردنیاز را فراهم کند.

## ۸. اصل نهایی

**WooGit Backend یک سرویس برای اپ موجود است. `WooGit Gateway Plugin` یک پلاگین مستقل برای سایت مشتری است و فعلاً روی آن کار نمی‌کنیم. `WooGit Main Plugin` نیز پلاگین مستقل سایت اصلی WooGit است. این سه کامپوننت نباید با یکدیگر قاطی شوند.**

هر تصمیم معماری، دیتامدل، API، امنیت، Queue، Subscription و Integration باید با این مرز سنجیده شود.
