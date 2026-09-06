# تصمیم‌های معماری WooGit

## ADR-000 — این مخزن فقط Backend اپ موجود است

**تصمیم:** این repository محل ساخت Backend سرویس WooGit برای اپ اندروید موجود است. اپ Android یک پروژه مستقل و از قبل ساخته‌شده است.

**دلیل:** Backend و Client باید مرز مالکیت، چرخه توسعه و مسئولیت مشخص داشته باشند. Backend باید API و سرویس‌های سمت سرور موردنیاز اپ را فراهم کند، نه اینکه سورس یا UI اپ را دوباره در خود repository قرار دهد.

**نتیجه:**

- Android App = Client
- WooGit Backend = این repository
- WordPress/WooCommerce مشتری = منبع داده فروشگاه
- WooGit Gateway Plugin = پلاگین مستقل روی سایت مشتری، خارج از scope فعلی
- WooGit Main Plugin = پلاگین مستقل سایت اصلی WooGit
- وب‌سایت/پنل تجاری WooGit = سرویس جدا در صورت وجود

**خارج از هدف:** ساخت UI اپ، Compose، Navigation، Dashboard اپ، ConnectionScreen اپ، APK و CI مخصوص Android.

## ADR-001 — WordPress پنل تجاری/کنترل است

**تصمیم:** از WordPress برای وب‌سایت عمومی WooGit و رابط مدیریت داخلی استفاده شود.

**دلیل:** توسعه سریع محتوا و مدیریت، سیستم بالغ کاربر/نقش و تناسب مناسب برای بازاریابی، مستندات و فرایندهای پنل کنترل.

**مرز:** این سامانه و `WooGit Main Plugin` با `WooGit Gateway Plugin` روی سایت مشتری یکی نیستند. Gateway Plugin کامپوننت مستقلی است و فعلاً توسعه آن در scope این پروژه نیست.

## ADR-002 — مرز Backend و Gateway Plugin

**تصمیم:** `WooGit Gateway Plugin` یک پلاگین مستقل است که روی WordPress/WooCommerce سایت مشتری نصب می‌شود. `WooGit Main Plugin` نیز پلاگین مستقلی است که روی سایت اصلی WooGit نصب می‌شود. این دو نباید با یکدیگر قاطی شوند.

**وضعیت:** در فاز فعلی روی `WooGit Gateway Plugin` کار نمی‌کنیم. طراحی جزئیات، پیاده‌سازی، refactor و migration آن به فاز مستقل بعدی موکول است.

**نتیجه:** Backend فعلی نباید برای تکمیل Gateway Plugin متوقف شود و نباید منطق Gateway را در این repository بازسازی کند. هر قراردادی که در Backend به Gateway اشاره دارد صرفاً باید مرز integration را مشخص کند.

## ADR-003 — Backend V1 سبک است

**تصمیم:** Backend V1 باید تا حد امکان سبک و نزدیک به مدل Client → Backend → Customer Site باشد. Backend مسئول Authorization، Account، Subscription، Entitlement، Site Ownership، Security و Lightweight Proxy/controlled integration است.

**دلیل:** هدف فعلی کمینه کردن تغییرات اپ و هزینه پردازش Backend است، بدون ایجاد Mirror دائمی WooCommerce.

## ADR-004 — WordPress Database تنها Persistence اجباری V1 است

**تصمیم:** در V1، WordPress + `WooGit Main Plugin` زیرساخت اصلی Backend هستند و دیتابیس همان WordPress محل نگهداری داده‌های Backend است. استفاده از PostgreSQL، Redis یا زیرساخت دیتابیس/کش جداگانه برای V1 الزامی نیست.

**دلیل:** هدف V1 سبک نگه داشتن Backend، کم کردن اجزای عملیاتی و جلوگیری از ایجاد وابستگی زیرساختی قبل از اثبات نیاز واقعی است.

**جزئیات:**

- تنظیمات کوچک و configuration تا حد امکان با WordPress Options نگهداری می‌شوند.
- داده‌های عملیاتی در حال رشد می‌توانند در جدول‌های اختصاصی Plugin داخل همان WordPress database نگهداری شوند.
- `wpdb` و migration/versioning استاندارد WordPress مبنای دسترسی و تغییر schema هستند.
- Products، Orders، Customers، Categories، Variations و Media سایت مشتری در Backend mirror نمی‌شوند.
- Redis، PostgreSQL، queue service مستقل یا سرویس مشابه فقط در صورت اثبات نیاز عملیاتی و با یک تصمیم معماری جدید به V1/V2 اضافه می‌شوند؛ وجود آن‌ها پیش‌فرض نیست.

## ADR-005 — Customer Site منبع حقیقت داده فروشگاه است

**تصمیم:** Products، Orders، Customers، Categories، Variations و Media در Customer WordPress/WooCommerce منبع اصلی هستند و Backend نباید دیتابیس دوم WooCommerce بسازد.

## ADR-006 — Bridge به‌صورت Headless

**تصمیم:** WooGit Bridge صفحه تنظیمات اجباری در WordPress نداشته باشد.

**دلیل:** WooGit باید یک تجربه کاربری واحد و کنترل مرکزی اشتراک/قابلیت ارائه دهد.

## ADR-007 — بعد از راه‌اندازی تجاری، ترافیک مستقیم اپ به سایت مشتری وجود ندارد

**تصمیم:** ترافیک تجاری محافظت‌شده از Backend ووگیت عبور کند.

**دلیل:** Backend محل اعمال اشتراک، مجوزها، حسابرسی، مقابله با سوءاستفاده و جداسازی اعتبارها است.

**نکته:** این تصمیم به معنی پیاده‌سازی فعلی `WooGit Gateway Plugin` نیست. Gateway Plugin یک کامپوننت مستقل و خارج از فاز فعلی است.

## ADR-008 — عملیات کنترل‌شده به‌جای Proxy دلخواه

**تصمیم:** API عمومی باید مسیرها و عملیات کنترل‌شده داشته باشد و Proxy عمومی URL دلخواه مجاز نیست. پیاده‌سازی داخلی می‌تواند Lightweight Forwarding باشد.

**دلیل:** Proxy دلخواه ریسک SSRF، خطای مجوز و سوءاستفاده را افزایش می‌دهد، در حالی که Forwarding کنترل‌شده هزینه معماری را پایین نگه می‌دارد.

## ADR-009 — Application Password برای احراز هویت اولیه WordPress

**تصمیم:** برای دسترسی برنامه‌ای راه دور، Application Passwordهای WordPress ترجیح داده شوند.

**دلیل:** WordPress آن‌ها را به‌عنوان اعتبارهای قابل لغو و اختصاصی برای هر برنامه و استفاده API مستند کرده است.

## ADR-010 — AI پشت WooGit باقی می‌ماند

**تصمیم:** کلید ارائه‌دهندگان AI و مسیریابی مدل در زیرساخت WooGit قرار داشته باشد.

**دلیل:** جلوگیری از رسیدن اسرار ارائه‌دهنده به سایت/مرورگر مشتری و امکان تغییر ارائه‌دهنده بدون به‌روزرسانی Bridge.

## ADR-011 — کلاینت مرجع مجوز نیست

**تصمیم:** دوره آزمایشی، اشتراک، مجوزها، اعتبارها و مالکیت سایت در سمت سرور مرجع نهایی باشند.

**دلیل:** APK قابل تغییر است و SaaS تجاری به اعمال مجوز سمت سرور نیاز دارد.

## ADR-012 — Idempotency یک قابلیت پایه پلتفرم است

**تصمیم:** هر Mutation از نوع CREATE دارای قرارداد Idempotency باشد.

**دلیل:** Timeout شبکه می‌تواند بعد از موفقیت مقصد رخ دهد. پیش از عرضه تجاری باید رفتار Retry امن اثبات شود.

## موارد خارج از هدف

- ساخت یا بازطراحی Android App در این repository.
- قرار دادن UI اپ یا ConnectionScreen در Backend.
- ساخت APK یا CI مخصوص Android در این repository.
- **پیاده‌سازی، refactor یا migration `WooGit Gateway Plugin` روی سایت مشتری در فاز فعلی.**
- ادغام `WooGit Gateway Plugin` با `WooGit Main Plugin`.
- الزام PostgreSQL، Redis یا هر زیرساخت جداگانه برای V1 بدون نیاز اثبات‌شده و تصمیم معماری جدید.
- ساخت پلتفرم توزیع‌شده اختصاصی پیش از اثبات نیاز واقعی.
- تبدیل Bridge به افزونه اجرای عمومی کد راه دور.
- اجازه دادن به LLM برای ارسال درخواست HTTP دلخواه.
