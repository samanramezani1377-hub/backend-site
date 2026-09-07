# مدیریت نمایش و محتوای WooGit Theme

> وضعیت: V1 — مشخصات مدیریت Presentation و Website Content
>
> این سند مکمل `docs/THEME.md` است و فقط قابلیت‌های مدیریتی مربوط به **ظاهر، محتوا و Presentation وب‌سایت رسمی WooGit** را تعریف می‌کند.

## ۱. هدف

`Theme Management` برای مدیریت محتوایی است که در وب‌سایت رسمی WooGit به بازدیدکننده نمایش داده می‌شود.

این بخش **مدیریت افزونه WooGit، Backend، حساب مشتری، Billing یا WooCommerce نیست**.

مدیر سایت باید بتواند بدون ویرایش کد Theme، محتوای اصلی صفحات عمومی و عناصر بصری سایت را تغییر دهد.

## ۲. مرز مسئولیت

### Theme Management مسئول است از:

- لوگو و Favicon؛
- تصاویر Hero؛
- عنوان و توضیحات Hero؛
- متن و لینک دکمه‌های Hero؛
- محتوای بخش قابلیت‌ها؛
- آیکن، تصویر، عنوان و توضیح قابلیت‌ها؛
- ترتیب نمایش قابلیت‌ها؛
- محتوای بخش How It Works؛
- مراحل و تصاویر راهنما؛
- محتوای نمایشی صفحه Pricing؛
- FAQ؛
- اطلاعات تماس؛
- متن‌های Footer؛
- Copyright؛
- لینک شبکه‌های اجتماعی؛
- متن‌ها و تصاویر سایر بخش‌های عمومی سایت؛
- Metadata و Site Identity در حد Presentation سایت.

### Theme Management مسئول نیست از:

- Account Management؛
- Authentication و Authorization؛
- Session و Web Session؛
- Site Ownership؛
- Entitlement؛
- Subscription state؛
- Billing و Payment؛
- مدیریت Product یا Order؛
- Sync؛
- Inventory؛
- Conflict Resolution؛
- WooCommerce Credentials؛
- Backend Database؛
- API Business Logic افزونه.

این موارد متعلق به Backend Plugin یا App هستند و نباید با Theme Management ترکیب شوند.

## ۳. محل مدیریت

یک رابط مدیریتی اختصاصی در WordPress Admin برای Theme در نظر گرفته شود؛ برای مثال:

```text
WordPress Admin
└── WooGit Theme
    ├── تنظیمات عمومی
    ├── صفحه اصلی
    ├── قابلیت‌ها
    ├── روش کار
    ├── Pricing
    ├── FAQ
    ├── Footer
    └── شبکه‌های اجتماعی
```

نام و محل دقیق منو می‌تواند با ساختار فعلی Theme هماهنگ شود، اما این رابط نباید به Admin Console افزونه WooGit یا پنل مدیریت Backend تبدیل شود.

## ۴. تنظیمات عمومی

مدیریت تنظیمات عمومی شامل موارد زیر باشد:

- Logo؛
- Logo جایگزین در صورت نیاز؛
- Favicon؛
- عنوان سایت؛
- توضیح کوتاه سایت؛
- اطلاعات پایه برند در صورت نیاز؛
- تصویر پیش‌فرض Open Graph در صورت استفاده؛
- تنظیمات Presentation عمومی که در چند صفحه استفاده می‌شوند.

برای تصاویر باید امکان انتخاب، تغییر و حذف از WordPress Media Library وجود داشته باشد.

## ۵. Hero Section

مدیر باید بتواند محتوای Hero صفحه اصلی را بدون تغییر کد کنترل کند.

### فیلدها

- Hero Image/Desktop Image؛
- Mobile Hero Image در صورت نیاز؛
- عنوان اصلی؛
- توضیح؛
- متن دکمه اصلی؛
- لینک دکمه اصلی؛
- متن دکمه دوم در صورت وجود؛
- لینک دکمه دوم؛
- فعال/غیرفعال بودن عناصر اختیاری.

نمونه:

```text
Hero
├── تصویر دسکتاپ
├── تصویر موبایل
├── عنوان
├── توضیح
├── دکمه اصلی
│   ├── متن
│   └── لینک
└── دکمه دوم
    ├── متن
    └── لینک
```

## ۶. Features

بخش قابلیت‌ها باید قابل مدیریت و مرتب‌سازی باشد.

هر Feature حداقل شامل این موارد باشد:

- عنوان؛
- توضیح؛
- Icon یا Image؛
- وضعیت فعال/غیرفعال؛
- ترتیب نمایش.

مدیر باید بتواند:

- Feature جدید اضافه کند؛
- Feature را ویرایش کند؛
- Feature را حذف یا غیرفعال کند؛
- ترتیب Featureها را تغییر دهد.

ساختار پیشنهادی:

```text
Feature #1
  عنوان
  توضیح
  آیکن/تصویر
  ترتیب

Feature #2
  عنوان
  توضیح
  آیکن/تصویر
  ترتیب
```

## ۷. How It Works

محتوای بخش «روش کار» باید قابل ویرایش باشد.

هر Step می‌تواند شامل:

- شماره یا ترتیب؛
- عنوان؛
- توضیح؛
- تصویر یا Illustration؛
- لینک اختیاری.

مدیر باید بتواند Stepها را اضافه، حذف، ویرایش و جابه‌جا کند.

## ۸. Pricing Presentation

Theme Management فقط **Presentation** مربوط به Pricing را مدیریت می‌کند.

مواردی مانند:

- عنوان صفحه؛
- Subtitle؛
- متن توضیحی؛
- Badge یا Labelهای نمایشی؛
- متن CTA؛
- توضیحات عمومی؛
- FAQ یا توضیحات کنار Pricing.

قیمت واقعی، Currency، وضعیت Subscription، Entitlement، Checkout و Billing نباید در این بخش به‌صورت دستی مدیریت شوند اگر منبع authoritative آن‌ها Backend است.

به عبارت دیگر:

```text
Theme Management
      ↓
Presentation Pricing

Backend
      ↓
Price / Plan / Subscription / Entitlement / Billing
```

## ۹. FAQ

FAQ باید به‌صورت لیست قابل مدیریت باشد.

هر مورد شامل:

- سؤال؛
- پاسخ؛
- وضعیت فعال/غیرفعال؛
- ترتیب نمایش.

مدیر باید بتواند FAQ جدید اضافه کند، آن را ویرایش یا حذف کند و ترتیب آن را تغییر دهد.

## ۱۰. Footer

اطلاعات Footer باید بدون تغییر کد قابل ویرایش باشد.

### اطلاعات تماس

- شماره تماس؛
- Email؛
- آدرس؛
- ساعات پاسخ‌گویی در صورت نیاز.

### متن‌ها

- متن معرفی کوتاه؛
- Copyright؛
- متن‌های قانونی یا اطلاع‌رسانی کوتاه؛
- متن CTA در صورت وجود.

### لینک‌ها

- Privacy؛
- Terms؛
- Documentation؛
- Support؛
- سایر لینک‌های عمومی سایت.

## ۱۱. شبکه‌های اجتماعی

مدیر باید بتواند لینک شبکه‌های اجتماعی رسمی WooGit را مدیریت کند.

برای هر شبکه حداقل:

- نوع شبکه؛
- URL؛
- وضعیت فعال/غیرفعال؛
- ترتیب نمایش.

در صورت نیاز می‌توان Icon را بر اساس نوع شبکه به‌صورت خودکار نمایش داد و از ذخیره Icon HTML خام جلوگیری کرد.

## ۱۲. سایر بخش‌های قابل مدیریت صفحه اصلی

هر بخش عمومی تکرارشونده یا محتوایی که در طراحی نهایی وجود دارد، در صورت نیاز باید به‌صورت قابل مدیریت تعریف شود؛ از جمله:

- عنوان Section؛
- Subtitle؛
- متن معرفی؛
- Image/Illustration؛
- CTA؛
- ترتیب Section؛
- وضعیت نمایش Section.

هدف این است که محتوای واقعی سایت در قالب داده قابل ویرایش باشد و برای تغییرات محتوایی نیازی به تغییر PHP/HTML Theme نباشد.

## ۱۳. مدیریت تصاویر

تمام تصاویر مدیریتی باید از WordPress Media Library استفاده کنند.

مدیر باید بتواند:

- تصویر انتخاب کند؛
- تصویر را جایگزین کند؛
- تصویر را حذف کند؛
- Preview تصویر را ببیند.

برای Hero و تصاویر مهم، امکان تعیین تصویر Desktop و Mobile در صورت نیاز وجود داشته باشد.

تصویر انتخاب‌شده نباید به‌صورت Base64 یا فایل داخل Theme ذخیره شود؛ Theme باید ID یا reference مناسب WordPress Media را نگهداری کند.

## ۱۴. ویرایش محتوا

برای متن ساده از فیلدهای متنی استاندارد استفاده شود.

برای محتوای چندخطی از textarea و فقط در موارد لازم از Rich Text Editor استفاده شود.

HTML خام نباید بدون نیاز وارد تنظیمات شود. خروجی تمام داده‌ها باید هنگام Render با escaping مناسب WordPress تولید شود.

## ۱۵. امنیت مدیریت Theme

تمام صفحات مدیریتی Theme باید:

- Capability مناسب را بررسی کنند؛
- Nonce معتبر داشته باشند؛
- ورودی‌ها را Sanitize کنند؛
- خروجی‌ها را Escape کنند؛
- از ذخیره اطلاعات حساس خودداری کنند.

این بخش نباید Credentialهای WooCommerce، Password، API Secret، Session Token یا اطلاعات امنیتی Backend را ذخیره کند.

## ۱۶. محل ذخیره تنظیمات

تنظیمات Presentation باید از مکانیزم استاندارد WordPress استفاده کنند؛ مانند Settings API و WordPress Options یا سازوکار Theme-native مناسب.

انتخاب نهایی بین:

- WordPress Settings API؛
- Theme Options؛
- Customizer؛
- Block/Content configuration؛

باید بر اساس ساختار واقعی `theme/woogit/` انجام شود و نباید بدون بررسی معماری موجود باعث ایجاد دو سیستم موازی مدیریت محتوا شود.

تنظیمات Theme نباید در جداول اختصاصی Backend Plugin یا سرویس‌های Business Logic ذخیره شوند.

## ۱۷. Preview و UX مدیریت

رابط مدیریت باید برای مدیر سایت ساده و قابل فهم باشد.

برای بخش‌های بصری بهتر است موارد زیر وجود داشته باشد:

- Preview تصویر بلافاصله بعد از انتخاب؛
- دکمه تغییر/حذف تصویر؛
- Drag & Drop برای ترتیب Featureها، Stepها و FAQها؛
- وضعیت فعال/غیرفعال؛
- Save واضح؛
- پیام موفقیت یا خطای واضح؛
- در صورت امکان Live Preview برای بخش‌های مهم.

## ۱۸. اصل مهم جداسازی

ساختار نهایی باید این جداسازی را حفظ کند:

```text
WooGit Theme Management
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

Theme Management نباید به‌مرور تبدیل به پنل مدیریت Backend شود.

## ۱۹. V1 Checklist

- [ ] Logo قابل تغییر
- [ ] Favicon قابل تغییر
- [ ] Hero Image قابل تغییر
- [ ] Hero متن قابل تغییر
- [ ] Hero CTA قابل تغییر
- [ ] Features قابل افزودن/ویرایش/حذف/مرتب‌سازی
- [ ] How It Works قابل مدیریت
- [ ] Pricing Presentation قابل مدیریت
- [ ] FAQ قابل افزودن/ویرایش/حذف/مرتب‌سازی
- [ ] Footer phone/email/address قابل تغییر
- [ ] Footer text و copyright قابل تغییر
- [ ] Social Links قابل مدیریت
- [ ] تصاویر از Media Library
- [ ] Preview تصاویر
- [ ] Sanitization / Escaping / Nonce / Capability checks
- [ ] عدم ذخیره Credential و Session در Theme Management
- [ ] عدم وابستگی به Business Logic افزونه
- [ ] عدم ورود Products / Orders / Sync / Inventory به Theme Management
