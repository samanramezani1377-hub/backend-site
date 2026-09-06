# تصمیم‌های معماری WooGit

## ADR-000 — این مخزن فقط Backend اپ موجود است

**تصمیم:** این repository محل ساخت Backend سرویس WooGit برای اپ اندروید موجود است. اپ Android یک پروژه مستقل و از قبل ساخته‌شده است.

**دلیل:** Backend و Client باید مرز مالکیت، چرخه توسعه و مسئولیت مشخص داشته باشند. Backend باید API و سرویس‌های سمت سرور موردنیاز اپ را فراهم کند، نه اینکه سورس یا UI اپ را دوباره در خود repository قرار دهد.

**نتیجه:**

- Android App = Client
- WooGit Backend = این repository
- WooGit Main Plugin = پلاگین Backend روی WordPress اصلی خود WooGit
- WordPress/WooCommerce مشتری = منبع داده فروشگاه
- WooGit Gateway Plugin = پلاگین مستقل روی سایت مشتری، خارج از scope فعلی
- وب‌سایت/پنل تجاری WooGit = همان محیط WordPress اصلی و در صورت نیاز قابلیت‌های مستقل روی آن

**خارج از هدف:** ساخت UI اپ، Compose، Navigation، Dashboard اپ، ConnectionScreen اپ، APK و CI مخصوص Android.

## ADR-001 — WordPress + WooGit Main Plugin زیرساخت Backend V1 است

**تصمیم:** Backend V1 روی WordPress اصلی WooGit و `WooGit Main Plugin` اجرا می‌شود. `WooGit Main Plugin` مغز Backend است و API، Account، Session، Trial، Subscription، Entitlement، Site Identity، Security و Lightweight Proxy/controlled integration را فراهم می‌کند.

**دلیل:** این مدل برای V1 سبک است، اجزای عملیاتی کمتری دارد و نیاز به سرویس Backend جداگانه را حذف می‌کند.

**مرز:** `WooGit Main Plugin` روی سایت اصلی WooGit با `WooGit Gateway Plugin` روی سایت مشتری یکی نیستند.

## ADR-002 — تفکیک قطعی Main Plugin و Gateway Plugin

**تصمیم:** دو Plugin کاملاً مستقل هستند:

- `WooGit Main Plugin` روی WordPress اصلی خود WooGit نصب می‌شود و Backend اصلی WooGit است.
- `WooGit Gateway Plugin` روی WordPress/WooCommerce سایت مشتری نصب می‌شود و یک کامپوننت مستقل برای integration سمت مشتری است.

**وضعیت:** در فاز فعلی فقط `WooGit Main Plugin` و Backend آن توسعه داده می‌شود. روی `WooGit Gateway Plugin` هیچ پیاده‌سازی، refactor یا migration انجام نمی‌شود.

**نتیجه:** هرجا در اسناد عبارت «Plugin» مبهم است باید مشخص شود منظور `Main Plugin` است یا `Gateway Plugin`. عبارت `Gateway/Proxy` در Backend به معنی Gateway Plugin مشتری نیست؛ منظور integration/proxy logic داخل Main Plugin است.

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

**تصمیم:** هر integration سمت سایت مشتری که در آینده به عنوان `WooGit Gateway Plugin` ساخته شود، مستقل از `WooGit Main Plugin` خواهد بود و صفحه تنظیمات اجباری در WordPress مشتری نخواهد داشت مگر اینکه نیاز محصولی مشخصی ثابت شود.

**دلیل:** WooGit باید یک تجربه کاربری واحد و کنترل مرکزی اشتراک/قابلیت ارائه دهد.

## ADR-007 — بعد از راه‌اندازی تجاری، ترافیک مستقیم اپ به سایت مشتری وجود ندارد

**تصمیم:** ترافیک تجاری محافظت‌شده از Backend ووگیت عبور کند.

**دلیل:** Backend محل اعمال اشتراک، مجوزها، حسابرسی، مقابله با سوءاستفاده و جداسازی اعتبارها است.

**نکته:** این تصمیم به معنی فعال بودن یا نصب `WooGit Gateway Plugin` روی سایت مشتری نیست. Gateway Plugin یک کامپوننت مستقل و خارج از فاز فعلی است.

## ADR-008 — عملیات کنترل‌شده به‌جای Proxy دلخواه

**تصمیم:** API عمومی باید مسیرها و عملیات کنترل‌شده داشته باشد و Proxy عمومی URL دلخواه مجاز نیست. پیاده‌سازی داخلی در `WooGit Main Plugin` می‌تواند Lightweight Forwarding باشد.

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

## ADR-013 — Credential Vault در V1 اختیاری و غیرالزامی است

**تصمیم:** `Credential Vault` بخشی از قابلیت‌های بالقوه `WooGit Main Plugin` است، اما در V1 برای عملیات عادی اجباری نیست. در مدل فعلی، Client می‌تواند اعتبارهای سایت مشتری را همراه درخواست ارسال کند و Main Plugin پس از احراز Session، Account، Site Ownership، Entitlement و سایر کنترل‌های لازم، درخواست کنترل‌شده را به سایت مشتری Forward کند.

**نتیجه:**

- هیچ Lookup اجباری به Vault برای هر Request عادی وجود ندارد.
- Backend نباید صرفاً برای Proxy سبک، credentialهای Client را در دیتابیس ذخیره کند.
- Credential Vault فقط در صورت نیاز واقعی، مثلاً برای قابلیت‌های آینده‌ای که به اجرای Background Job/Webhook یا دسترسی بدون حضور Client نیاز دارند، می‌تواند فعال شود.
- اضافه شدن Vault اجباری یا تغییر مدل ذخیره‌سازی Credentialها نیازمند تصمیم معماری جدید است.
- Credentialها همچنان Secret محسوب می‌شوند و نباید در Log، Error Response، Audit عمومی یا Telemetry افشا شوند.

**دلیل:** این تصمیم با هدف V1 سبک و کمینه کردن تغییرات Client و هزینه پردازش Backend هم‌راستا است. WordPress نیز Application Password را برای احراز هویت برنامه‌ای و به‌صورت قابل لغو پشتیبانی می‌کند. urlWordPress REST API Authenticationhttps://developer.wordpress.org/rest-api/using-the-rest-api/authentication/

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
