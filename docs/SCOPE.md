# مرز قطعی پروژه WooGit Backend

## ۱. هدف

این مخزن برای **ساخت Backend اپ موجود WooGit** است.

اپ اندروید WooGit قبلاً ساخته شده و پروژه مستقل خودش را دارد. این مخزن نباید اپ را دوباره بسازد، معماری داخلی آن را بازطراحی کند یا مسئولیت‌های UI/UX آن را بر عهده بگیرد.

```text
┌──────────────────────┐
│  WooGit Android App  │  ← پروژه مستقل، از قبل ساخته شده
└──────────┬───────────┘
           │ HTTPS / API
           ▼
┌──────────────────────┐
│   WooGit Backend     │  ← این پروژه
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Customer WordPress   │
│ / WooCommerce        │
└──────────────────────┘
```

## ۲. مسئولیت این مخزن

Backend باید سرویس‌های زیر را برای اپ موجود فراهم کند:

- Authentication و Session
- Site Identity و مالکیت سایت
- Account lifecycle
- Subscription / Trial
- Entitlement و Feature authorization
- Credential Vault
- API Gateway
- عملیات Typed برای WooCommerce
- WordPress/WooCommerce integration
- WooGit Bridge integration
- Webhook / Event ingestion
- Queue / Job / Retry
- Idempotency
- Timeout-after-success safety
- Rate limiting و Abuse protection
- Audit و Security
- Observability و Operations
- قابلیت‌های اختیاری Chat / AI / Analytics در صورت قرار گرفتن در قرارداد محصول

## ۳. چیزهایی که خارج از مسئولیت این مخزن هستند

- ساخت یا بازطراحی اپ Android
- UI/UX اپ
- Compose، Navigation یا State management اپ
- پیاده‌سازی ConnectionScreen اپ
- پیاده‌سازی Dashboard اپ
- منطق محلی اپ به‌عنوان مرجع مجوز
- بسته‌بندی APK
- CI مخصوص Android، مگر برای تست قرارداد API Backend

## ۴. قرارداد با اپ موجود

Backend باید با رفتار فعلی اپ به‌عنوان یک Client واقعی سازگار شود.

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

## ۵. وابستگی‌های خارجی

این پروژه می‌تواند به اجزای دیگری وابسته باشد، اما آن‌ها را مالک نمی‌شود:

- Android App موجود WooGit: Client
- WordPress/WooCommerce مشتری: منبع داده فروشگاه
- WooGit Bridge: Integration component
- WooGit commercial website/control panel: سامانه جداگانه در صورت وجود

وجود این اجزا در معماری به معنی قرار گرفتن سورس آن‌ها در این repository نیست.

## ۶. قانون جلوگیری از Scope Drift

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

اگر قابلیت متعلق به سایت تجاری یا پنل مدیریت باشد، باید به‌عنوان یک سرویس/پروژه جدا در نظر گرفته شود و Backend فقط API یا integration موردنیاز را فراهم کند.

## ۷. اصل نهایی

**WooGit Backend یک سرویس برای اپ موجود است، نه پروژه‌ای برای ساخت خود اپ.**

هر تصمیم معماری، دیتامدل، API، امنیت، Queue، Subscription و Integration باید با این مرز سنجیده شود.