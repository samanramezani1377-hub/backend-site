# مشخصات تم WooGit

> وضعیت: V1 — قرارداد طراحی و پیاده‌سازی
>
> این سند مرجع اصلی `theme/woogit/` است و مرز تم با افزونه اصلی WooGit و WooCommerce را مشخص می‌کند.

## ۱. هدف و جایگاه تم

`WooGit Theme` وب‌سایت رسمی محصول WooGit و پرتال مشتری احراز‌شده است. تم دو نقش دارد:

1. معرفی محصول، قابلیت‌ها، روش کار، قیمت‌ها، مستندات عمومی، پشتیبانی و صفحات حقوقی؛
2. ارائه پرتال مشتری برای حساب کاربری، اشتراک، پرداخت‌ها، وضعیت Billing، سایت متصل و تنظیمات امنیتی حساب.

تم **نسخه وب App نیست** و نباید عملیات فروشگاه را پیاده‌سازی کند.

```text
App Android
    ↓
عملیات فروشگاه مشتری
محصولات / سفارش‌ها / همگام‌سازی / تعارض‌ها

Theme
    ↓
وب‌سایت رسمی + پرتال حساب + اشتراک و Billing

Plugin
    ↓
مرجع اصلی API + امنیت + احراز هویت + Business Logic
```

## ۲. مرز مسئولیت‌ها

### تم مسئول است از:

- صفحات عمومی وب‌سایت؛
- معرفی محصول و قابلیت‌ها؛
- Pricing؛
- Login و Register/Onboarding وب طبق قرارداد Backend؛
- پرتال حساب مشتری؛
- Subscription؛
- Billing و سابقه پرداخت؛
- هدایت به Checkout؛
- اطلاعات سایت متصل؛
- تنظیمات حساب و امنیت وب؛
- Documentation، FAQ، Support، Privacy و Terms؛
- UI، Navigation، Accessibility و Responsive behavior.

### تم مسئول نیست از:

- مدیریت Products؛
- مدیریت Orders؛
- Order Detail عملیاتی؛
- Sync؛
- Conflict Resolution؛
- Inventory؛
- Media operations؛
- Store Dashboard عملیاتی؛
- فراخوانی مستقیم WooCommerce مشتری؛
- Forwarding عملیاتی؛
- تصمیم‌گیری درباره Authorization یا Entitlement.

این قابلیت‌ها متعلق به App و Backend هستند.

## ۳. معماری

```text
Browser
  ↓
WooGit Theme
  ↓
REST API عمومی WooGit
  ↓
WooGit Main Plugin
  ├─ Account
  ├─ Site Identity
  ├─ Authentication
  ├─ Web Session
  ├─ Ownership
  ├─ Trial / Subscription
  ├─ Entitlement
  ├─ Billing
  ├─ Security
  └─ Authorization
```

Theme فقط Presentation و تعامل کاربر را ارائه می‌کند. هر تصمیم authoritative باید از Backend بیاید.

تم نباید فایل PHP، کلاس، سرویس، Migration، Database code یا Business Logic افزونه را کپی یا مستقیماً وارد کند.

## ۴. نقشه صفحات

### صفحات عمومی

- خانه / معرفی محصول
- قابلیت‌ها
- روش کار
- قیمت‌گذاری
- پرسش‌های متداول
- مستندات عمومی
- پشتیبانی
- وضعیت سرویس
- حریم خصوصی
- شرایط استفاده

### صفحات احراز هویت

- ورود
- ثبت‌نام / اتصال اولیه سایت
- خروج
- وضعیت اعتبارسنجی اتصال

### پرتال مشتری

- داشبورد حساب
- اشتراک
- Billing
- سابقه پرداخت‌ها
- سایت‌های متصل
- مصرف/Usage در صورت وجود قرارداد Backend
- امنیت و نشست‌ها
- اطلاعات حساب
- تغییر رمز و ایمیل تماس

**هیچ صفحه‌ای برای Products، Orders، Sync یا Conflicts در Theme وجود ندارد.**

## ۵. Login

ورود V1 با Email/Password عمومی جایگزین نمی‌شود. قرارداد فعلی ورود وب:

```text
Site URL
Password
```

Theme این دو مقدار را دریافت و به Backend ارسال می‌کند. Theme اعتبار رمز یا مالکیت سایت را خودش تعیین نمی‌کند.

نمونه UI:

```text
خوش آمدید

آدرس سایت
[ https://example.com ]

رمز عبور WooGit
[ ******** ]

[ ورود ]
```

پیام خطا باید عمومی و مطابق قرارداد Backend باشد و اطلاعات حساس یا جزئیات وجود Account را بیش از حد لازم افشا نکند.

## ۶. Register / اتصال اولیه

جریان ثبت‌نام اولیه و اتصال سایت شامل اطلاعات زیر است:

- آدرس فروشگاه؛
- نام کاربری WordPress؛
- Application Password وردپرس؛
- Consumer Key ووکامرس؛
- Consumer Secret ووکامرس.

```text
اتصال فروشگاه

آدرس فروشگاه
[ https://example.com ]

نام کاربری WordPress
[ ... ]

Application Password وردپرس
[ ... ]

Consumer Key ووکامرس
[ ... ]

Consumer Secret ووکامرس
[ ... ]

[ اعتبارسنجی و ایجاد حساب ]
```

این اطلاعات فقط برای جریان request-scoped اعتبارسنجی اولیه هستند و نباید به Credential دائمی حساب تبدیل شوند.

طبق قرارداد Backend، این Credentialها نباید در Database، Log، Telemetry، Audit، Cache پایدار، HTML، JavaScript bundle یا Browser Storage نگهداری شوند.

### نکته معماری مهم

در قرارداد فعلی Backend، تنظیم Web Password از مسیر `setup-web-credentials` به App Session معتبر وابسته است. بنابراین اگر ثبت‌نام مستقیم از وب قرار است بدون App انجام شود، باید قبل از پیاده‌سازی یک قرارداد Backend مشخص برای آن تعریف شود. Theme نباید این شکاف را با منطق امنیتی خودش پر کند.

## ۷. اعتبارسنجی اتصال

Theme فقط پیشرفت مراحل را نمایش می‌دهد و نتیجه را از Backend می‌گیرد:

```text
شبکه / HTTPS
      ↓
دسترسی و احراز هویت WordPress
      ↓
هویت و دسترسی WordPress
      ↓
دسترسی و احراز هویت WooCommerce
      ↓
Site Identity
      ↓
Account / Trial
      ↓
WooGit Session
```

Verification باید Read-only باشد و نباید برای تست اتصال Product، Order یا Media ایجاد/ویرایش کند.

موفقیت Verification به معنی اثبات کنترل فنی معتبر سایت در این جریان است، نه اثبات مالکیت حقوقی دامنه.

## ۸. Session و دسترسی

Theme مرجع Authorization نیست؛ Backend تنها مرجع معتبر است.

دو نوع Session کاملاً جدا هستند:

```text
App   → X-WooGit-Session
Web   → X-WooGit-Web-Session
```

Theme نباید App Session را برای Web استفاده کند.

Session منقضی‌شده معتبر نیست و نباید locally revive شود. ورود مجدد باید Session جدید بسازد و Backend دوباره Account، Site Ownership و Entitlement لازم را بررسی کند.

## ۹. معماری نگهداری Web Session

Token نشست وب نباید در URL قرار گیرد.

پیاده‌سازی ترجیحی برای مرورگر، Cookie امن با ویژگی‌های مناسب مانند `HttpOnly`، `Secure` و `SameSite` یا یک BFF/Bridge امن است؛ اما انتخاب نهایی باید با قرارداد Backend هماهنگ شود.

اگر Backend فقط Header `X-WooGit-Web-Session` را پشتیبانی کند، ذخیره خام Token در `localStorage` نباید بدون ارزیابی امنیتی و تصمیم معماری صریح انجام شود.

## ۱۰. Pricing

Pricing باید کاملاً داده‌محور باشد. قیمت، مدت و Currency نباید در Theme به‌صورت ثابت نوشته شوند.

Theme باید در صورت وجود این اطلاعات را نمایش دهد:

- نام پلن؛
- قیمت؛
- واحد پول؛
- مدت؛
- قابلیت‌ها؛
- محدودیت سایت؛
- اعتبار AI در صورت وجود؛
- پلن فعلی؛
- اقدام ارتقا یا تمدید.

## ۱۱. Billing و Checkout

جریان کلی:

```text
Pricing
  ↓
انتخاب پلن
  ↓
Backend Billing Checkout
  ↓
WooCommerce Order
  ↓
درگاه پرداخت
  ↓
رویداد سروری پرداخت/اشتراک
  ↓
Entitlement
  ↓
دسترسی عملیاتی در صورت واجد شرایط بودن
```

پرداخت واقعی توسط Backend و WooCommerce انجام می‌شود. Theme فقط رابط انتخاب و هدایت است.

### اصل مهم

بازگشت از درگاه **هرگز به‌تنهایی به معنی موفقیت پرداخت نیست**.

```text
بازگشت از درگاه
      ↓
استعلام دوباره Billing Status
      ↓
تأیید پرداخت و اشتراک توسط Backend
      ↓
نمایش وضعیت نهایی
```

### Idempotency

Checkout باید طبق قرارداد Backend دارای `Idempotency-Key` باشد. Retry همان عملیات باید همان کلید را حفظ کند تا یک خرید چندبار ایجاد نشود.

## ۱۲. Subscription Dashboard

داشبورد اشتراک باید وضعیت authoritative را نمایش دهد:

- Trial؛
- Active؛
- Expired؛
- Cancelled؛
- پلن فعلی؛
- تاریخ شروع؛
- تاریخ پایان/تمدید؛
- سابقه پرداخت؛
- وضعیت Billing؛
- وضعیت Entitlement؛
- اقدام تمدید یا ارتقا.

Theme نباید با تغییر State محلی، Premium یا Active را جعل کند.

## ۱۳. داشبورد حساب مشتری

داشبورد Theme یک **Customer Account Dashboard** است، نه Store Dashboard.

ساختار پیشنهادی:

```text
داشبورد حساب
├── نمای کلی
├── اشتراک
├── Billing
├── پرداخت‌ها
├── سایت متصل
├── مصرف در صورت وجود
├── امنیت و نشست‌ها
└── حساب کاربری
```

نمای کلی می‌تواند شامل کارت‌های Current Plan، Access Status، Expiration، Connected Site و Usage باشد.

## ۱۴. Connected Sites

Theme فقط سایت‌هایی را نمایش می‌دهد که Backend متعلق بودن آن‌ها به Account را تأیید کرده است.

Theme نباید Ownership را از URL، فرم یا اطلاعات محلی حدس بزند.

Credential سایت مشتری نباید در Dashboard، HTML، Browser Storage، Source Code یا UI نمایش داده شود.

## ۱۵. Account و Security

Theme باید امکان مدیریت مواردی را که Backend برای وب منتشر می‌کند ارائه دهد:

- اطلاعات Account؛
- Contact Email؛
- تغییر Web Password؛
- خروج از حساب؛
- نمایش وضعیت نشست؛
- نمایش اطلاعات امن سایت متصل.

تغییر رمز و عملیات حساس باید به Backend سپرده شوند. پس از تغییر رمز، اگر Backend نشست‌های قبلی را revoke کند، Theme باید ورود مجدد را درخواست کند.

## ۱۶. Security Boundary

Theme نباید:

- Credential مشتری را دائمی ذخیره کند؛
- Session را locally معتبر اعلام کند؛
- Entitlement را محاسبه یا جعل کند؛
- موفقیت پرداخت را از Callback/Redirect کلاینت قبول کند؛
- مقصد دلخواه و ناامن برای Gateway بسازد؛
- WooCommerce مشتری را مستقیماً از Browser فراخوانی کند؛
- Secret را در HTML، JavaScript، Analytics یا Log قرار دهد؛
- منطق امنیتی یا Business Logic Plugin را تکرار کند.

## ۱۷. جداسازی Theme و Plugin

```text
THEME
  = Presentation
  = Templates
  = CSS
  = UI JavaScript
  = Navigation
  = Accessibility

PLUGIN
  = REST API
  = Domain Logic
  = Authentication
  = Sessions
  = Accounts
  = Sites
  = Billing
  = Entitlements
  = Security
  = Database
  = Admin
```

Theme نباید با `include` یا `require` به فایل داخلی Plugin وابسته شود. ارتباط فقط از طریق API عمومی و قرارداد مستند انجام می‌شود.

فعال یا غیرفعال شدن Theme نباید داده Backend را حذف یا تغییر دهد. در صورت در دسترس نبودن Backend، Theme باید وضعیت خطای قابل‌بازیابی و قابل‌فهم نمایش دهد.

## ۱۸. Performance

Theme باید سبک باشد:

- بدون Page Builder اجباری؛
- بدون Dependency سنگین غیرضروری؛
- بارگذاری CSS/JS فقط در صفحات لازم؛
- تصاویر بهینه و Responsive؛
- Lazy Loading در موارد مناسب؛
- HTML معنایی؛
- حداقل Scriptهای شخص ثالث؛
- Core Web Vitals مناسب؛
- جلوگیری از JavaScript سنگین برای کارهای قابل انجام با CSS/HTML.

## ۱۹. سیستم طراحی

Theme باید از یک Design System مشترک و حرفه‌ای استفاده کند که با زبان بصری App هماهنگ باشد، اما UI عملیاتی App را کپی نکند.

جهت بصری:

- Liquid Glass کنترل‌شده؛
- پایه روشن نرم نزدیک به `#EFF1F7`؛
- لکه‌های محیطی Mint، Peach، Lavender و Sky؛
- سطوح شیشه‌ای نیمه‌شفاف؛
- Blur/Haze ظریف؛
- Border و Shadow نرم؛
- تأکید اصلی با گرادیان بنفش به صورتی؛
- وضعیت زنده با سبز؛
- وضعیت فوری با نارنجی؛
- Typography خوانا و دارای سلسله‌مراتب؛
- Responsive کامل؛
- RTL-first.

جزئیات کامل در `docs/THEME_DESIGN_SYSTEM.md` تعریف می‌شود.

## ۲۰. RTL و Responsive

Theme باید از ابتدا RTL-first باشد و برای محتوای فارسی و LTR مانند URL، Email و مقادیر فنی رفتار صحیح داشته باشد.

باید روی موبایل، تبلت، لپ‌تاپ و دسکتاپ عریض بدون Overflow و شکستن layout کار کند.

جزئیات در `docs/THEME_RESPONSIVE_SPEC.md` آمده است.

## ۲۱. Error / Loading States

Theme برای تعاملات API باید حداقل این وضعیت‌ها را پوشش دهد:

```text
idle
loading
success
validation_error
auth_error
verification_error
rate_limited
payment_pending
payment_failed
session_expired
server_error
network_error
```

برای `429` باید پیام مناسب و در صورت وجود `Retry-After` رفتار منطقی retry نمایش داده شود.

متن خطا نباید SQL، Stack Trace، Secret یا جزئیات داخلی Backend را نشان دهد.

## ۲۲. Session Expiration UX

اگر Backend نشست را منقضی اعلام کرد:

```text
Session expired
   ↓
پاک‌سازی State موقت
   ↓
نمایش Login
   ↓
ایجاد Web Session جدید
   ↓
بررسی دوباره Account + Site Ownership + Entitlement
```

Theme نباید نشست منقضی‌شده را تمدید محلی کند.

## ۲۳. Documentation / Support / Status

وب‌سایت باید مستندات عمومی، راهنمای شروع، اتصال سایت، Authentication، Billing، Session، Error، Security و Rate Limit را در اختیار کاربر قرار دهد.

صفحه Status باید فقط وضعیت سرویس‌ها را به شکل امن نمایش دهد و اطلاعات داخلی زیرساخت را افشا نکند.

## ۲۴. SEO و محتوای عمومی

صفحات عمومی باید:

- عنوان و توضیحات مناسب داشته باشند؛
- ساختار Heading صحیح داشته باشند؛
- Semantic HTML استفاده کنند؛
- Open Graph و metadata لازم را داشته باشند؛
- برای موتورهای جست‌وجو قابل crawl باشند؛
- محتوای اصلی را وابسته به JavaScript سنگین نکنند.

صفحات خصوصی پرتال نباید داده خصوصی مشتری را در Search Engine Index قرار دهند.

## ۲۵. دسترس‌پذیری

حداقل الزامات:

- Keyboard Navigation؛
- Focus قابل مشاهده؛
- Label واقعی برای ورودی‌ها؛
- پیام خطای مرتبط با فیلد؛
- کنتراست مناسب؛
- Reduced Motion؛
- Zoom و افزایش اندازه متن؛
- Touch Target مناسب در موبایل؛
- Semantic HTML و ARIA فقط در جایی که لازم است.

## ۲۶. Compatibility

Theme باید با WordPress استاندارد و API عمومی WooGit سازگار باشد. نباید به جزئیات پیاده‌سازی داخلی Plugin وابسته شود.

## ۲۷. تست و CI

تست‌های Theme باید از تست‌های Plugin/Backend جدا باشند:

```text
tests/plugin/  → Backend / Plugin
 tests/theme/  → Theme
```

Theme CI باید در آینده حداقل Lint، Syntax، Unit، Integration، Security، Accessibility و Build قابل نصب را بررسی کند.

در تست‌های مستقل، شکست یک تست نباید مانع اجرای تست‌های مستقل دیگر شود؛ اما در پایان، شکست هر تست الزامی باید نتیجه CI را قرمز کند.

جزئیات در `docs/THEME_TESTING.md` آمده است.

## ۲۸. اسناد تکمیلی

- `docs/THEME_API_CONTRACT.md` — قرارداد API و ارتباط با Backend
- `docs/THEME_AUTH_FLOW.md` — احراز هویت و نشست وب
- `docs/THEME_DESIGN_SYSTEM.md` — سیستم طراحی
- `docs/THEME_RESPONSIVE_SPEC.md` — Responsive و RTL
- `docs/THEME_TESTING.md` — تست و CI

این اسناد باید همگی فارسی و هماهنگ با این سند باشند.

## ۲۹. غیرهدف‌های V1

در V1، Theme نباید به وب‌اپلیکیشن عملیاتی فروشگاه تبدیل شود. موارد زیر خارج از Scope هستند:

- Products؛
- Orders؛
- Inventory؛
- Media Management؛
- Sync؛
- Conflict Resolution؛
- Store Operations؛
- اجرای مستقیم عملیات WooCommerce مشتری.

این مرز برای جلوگیری از تداخل مسئولیت Theme، App و Backend الزامی است.
