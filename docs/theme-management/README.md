# Theme Management

مستندات مدیریت نمایش و محتوای `WooGit Theme`.

این مجموعه فقط Presentation و محتوای وب‌سایت رسمی WooGit را پوشش می‌دهد و با Backend Plugin، Billing، Account، WooCommerce و عملیات فروشگاه قاطی نمی‌شود.

## رابط مدیریت V1

رابط مدیریت اختصاصی در `WordPress Admin → Appearance → WooGit Theme` قرار دارد و فقط برای کاربران دارای `manage_options` در دسترس است.

تب‌های مدیریتی فعلی:

- **تنظیمات عمومی** — نام برند، tagline، ایمیل پشتیبانی، Logo اصلی/جایگزین، Favicon و Open Graph image
- **صفحه اصلی** — Hero، تصاویر Tablet/Mobile، CTAها، متن معرفی بخش‌ها و فعال/غیرفعال کردن Sectionهای Home
- **قابلیت‌ها** — افزودن، حذف، فعال/غیرفعال کردن و مرتب‌سازی Featureها
- **روش کار** — افزودن، حذف، فعال/غیرفعال کردن و مرتب‌سازی Stepها
- **Pricing** — فقط Presentation/intro؛ قیمت و entitlement از منبع authoritative می‌آیند و دستی مدیریت نمی‌شوند
- **FAQ** — مدیریت سؤال و پاسخ به‌صورت ساختاریافته
- **Footer** — متن، تلفن، آدرس، ساعات پاسخ‌گویی، Copyright و تنظیمات اینماد
- **شبکه‌های اجتماعی** — نوع/نام، URL، فعال/غیرفعال و ترتیب

## تصاویر

تصاویر با WordPress Media Library انتخاب می‌شوند و فقط Media ID در تنظیمات ذخیره می‌شود. مدیر برای تصاویر مهم Preview، جایگزینی و حذف دارد. Base64 یا فایل تصویری داخل Theme ذخیره نمی‌شود.

## اصل معماری

```text
Theme Management
    ↓
Website Presentation / Content
    ↓
Public Website

WooGit Main Plugin
    ↓
Auth / Account / Site / Billing / Entitlement / Security / API

Android App
    ↓
WooCommerce Operations
```

Theme Management نباید به پنل مدیریت Backend تبدیل شود و نباید قیمت، پرداخت، entitlement یا credential فروشگاه مشتری را منبع حقیقت خود قرار دهد.

## اسناد

- [GENERAL.md](./GENERAL.md) — تنظیمات عمومی، Site Identity و محل مدیریت
- [HOME.md](./HOME.md) — Hero، Features، How It Works و سایر بخش‌های صفحه اصلی
- [CONTENT.md](./CONTENT.md) — Pricing Presentation، FAQ و محتوای عمومی
- [TRUST-AND-FOOTER.md](./TRUST-AND-FOOTER.md) — Footer، اطلاعات تماس، شبکه‌های اجتماعی و نماد اعتماد الکترونیکی (اینماد)
- [SECURITY-AND-BOUNDARIES.md](./SECURITY-AND-BOUNDARIES.md) — امنیت، محل ذخیره تنظیمات و مرزبندی با Backend/App
