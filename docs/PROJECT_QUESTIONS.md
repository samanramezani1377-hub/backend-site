# 🟨 باکس سؤالات تعیین‌تکلیف پروژه WooGit Backend

> **وضعیت:** بازطراحی‌شده بر اساس معماری فعلی V1 و تصمیمات ثبت‌شده تا این مرحله.
>
> این باکس فقط سؤال‌هایی را نگه می‌دارد که پاسخ آن‌ها واقعاً یک تصمیم معماری، محصولی، امنیتی یا عملیاتی باز ایجاد می‌کند. موضوعاتی که قبلاً در اسناد پروژه قطعی شده‌اند نباید دوباره به‌عنوان سؤال مطرح شوند.
>
> **روش کار:** سؤال‌ها به‌ترتیب بررسی می‌شوند. بعد از هر پاسخ، تصمیم دقیق در همین سند ثبت می‌شود و سؤال بعدی فعال می‌شود.
>
> **قانون مهم:** تا وقتی کاربر درباره یک موضوع تصمیم نگرفته، هیچ گزینه‌ای به‌عنوان تصمیم قطعی ثبت نمی‌شود.

## وضعیت کلی

- پاسخ داده‌شده: **۱ / ۲۰**
- باقی‌مانده: **۱۹ / ۲۰**
- سؤال فعلی: **۲**

---

## سؤال 01 — مدل احراز هویت App ↔ Backend و مدل Proxy

**وضعیت:** ✅ پاسخ داده شد

**تصمیم نهایی:**

برای کمترین تغییر در Android App و کمترین محاسبات غیرضروری در Backend، مسیر عادی عملیات WooCommerce باید یک **Proxy سبک** باشد. Backend نباید برای هر درخواست داده‌های WooCommerce را بازسازی، Mirror یا بی‌دلیل تبدیل کند؛ باید تا حد ممکن همان درخواست App را به سایت مشتری Forward کند و Response سایت مشتری را تقریباً همان‌طور به App برگرداند.

App در درخواست خود دو دسته اطلاعات می‌فرستد:

### ۱. اعتبار اتصال به سایت مشتری

- `WordPress Username`
- `WordPress Application Password`
- `WooCommerce Consumer Key`
- `WooCommerce Consumer Secret`

این چهار مقدار برای اتصال Backend به WordPress/WooCommerce سایت مقصد استفاده می‌شوند.

### ۲. اعتبار مصرف Backend

- `WooGit Session`

این Session برای شناسایی و اعتبارسنجی کاربر/حساب در خود WooGit Backend استفاده می‌شود و با Credentialهای سایت مشتری تفاوت دارد.

مدل مفهومی:

```text
WooGit Session
    = هویت و دسترسی مصرف‌کننده در Backend

WP Username + WP Application Password
+ WC Consumer Key + WC Consumer Secret
    = اعتبار Backend برای دسترسی به سایت مشتری
```

### مدل درخواست عادی

```text
WooGit Android App
        │
        │ WooGit Session
        │ site_id / destination
        │ WP Username
        │ WP Application Password
        │ WC Consumer Key
        │ WC Consumer Secret
        │ + path/query/body همان عملیات
        ▼
WooGit Backend
        │
        ├─ بررسی WooGit Session
        ├─ بررسی وضعیت Account
        ├─ بررسی بسته/غیرفعال نبودن Account
        ├─ بررسی اعتبار زمانی Trial / Subscription
        ├─ بررسی Subscription / Entitlement
        ├─ بررسی دسترسی Account به Site
        ├─ Version / Rate Limit / Security checks
        │
        │ سپس حداقل پردازش لازم:
        │ Forward همان Request
        ▼
Customer WordPress / WooCommerce
        │
        │ Response
        ▼
WooGit Backend
        │
        │ حداقل تغییر ممکن
        ▼
WooGit Android App
```

### مسئولیت‌های اجباری Backend قبل از Forward

Proxy سبک به معنی Proxy بدون کنترل نیست. Backend باید قبل از مصرف منابع سایت مشتری حداقل این کنترل‌ها را انجام دهد:

1. اعتبار `WooGit Session` را بررسی کند.
2. بررسی کند حساب کاربر **بسته یا غیرفعال** نشده باشد.
3. بررسی کند زمان دسترسی حساب، Trial یا Subscription **تمام نشده** باشد.
4. وضعیت Subscription و Entitlement لازم برای عملیات را بررسی کند.
5. بررسی کند `site_id` متعلق به همان Account باشد.
6. Version Gate، Rate Limit و کنترل‌های امنیتی لازم را اعمال کند.
7. فقط در صورت عبور از این کنترل‌ها درخواست را به WordPress/WooCommerce مقصد Forward کند.

بنابراین یکی از وظایف اصلی Backend این است که **وضعیت حیات حساب و اعتبار زمانی دسترسی کاربر را برای هر درخواست کنترل کند**. اگر حساب بسته/غیرفعال باشد یا Trial/Subscription/زمان مجاز دسترسی تمام شده باشد، درخواست نباید به سایت مشتری ارسال شود.

### هدف معماری

این تصمیم عمداً Backend را سبک نگه می‌دارد:

- کمترین تغییر ممکن در Android App؛
- حفظ تا حد امکان مدل فعلی ارسال/دریافت داده در App؛
- کمترین محاسبات و پردازش غیرضروری در Backend؛
- عدم Mirror کردن دائمی Product/Order/Customer و سایر داده‌های WooCommerce؛
- عدم تبدیل بی‌دلیل Request/Response؛
- تمرکز Backend روی Authentication، Account validity، Authorization، Subscription/Entitlement، Security و Proxy کردن درخواست.

### Credential Security

Credentialهای سایت مشتری:

- باید فقط روی HTTPS منتقل شوند؛
- نباید در Log ثبت شوند؛
- نباید در Error Response یا Response عادی افشا شوند؛
- نباید توسط Backend به App برگردانده شوند مگر در یک Flow صریح و امن که بعداً برای آن تصمیم‌گیری شود.

### محدوده این تصمیم

این مدل برای **درخواست‌های عادی بعد از وجود WooGit Session معتبر** است. Flow اولین اتصال/Onboarding که ممکن است قبل از ایجاد Session کامل انجام شود، در سؤال‌های مربوط به Connection Verification و Account Lifecycle تعیین تکلیف خواهد شد.

---

## سؤال 02 — مدل اتصال و Verification سایت

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** Flow نهایی اتصال یک سایت جدید را تأیید می‌کنیم که همیشه با **Verification خواندنی** شروع شود: ابتدا WordPress reachability/authentication و سپس WooCommerce verification، و فقط بعد از موفقیت کامل Site Identity ثبت شود؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 03 — هویت Canonical سایت

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** Canonical Site Identity را دقیقاً بر چه مبنایی تعریف کنیم؟ آیا یک سایت بر اساس canonical origin/domain + یک شناسه اثبات‌شده از WordPress/WooCommerce شناسایی شود تا تغییرات URL یا Store ID محلی باعث ساخت Site Identity تکراری نشود؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 04 — مالکیت و چندکاربره بودن یک Site

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** در V1 هر Site فقط یک Account مالک داشته باشد، یا از ابتدا چند کاربر برای یک Site با Role/Permissionهای متفاوت لازم است؟ اگر چندکاربره است، آیا دعوت کاربر و انتقال مالکیت هم در V1 لازم است؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 05 — رفتار اتصال به Site موجود

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** وقتی Verification یک Site Identity موجود موفق شد، آیا همین اثبات دسترسی به سایت برای ورود/ادامه Session کافی است، یا برای Account موجود باید عامل احراز هویت مستقل دیگری هم داشته باشیم؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 06 — Trial و لحظه شروع آن

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** Trial دقیقاً از چه رویدادی شروع شود: Verification موفق سایت، ساخت Account، پایان onboarding، یا اولین عملیات تجاری؟ مدت Trial و رفتار آن در صورت نیمه‌کاره ماندن onboarding نیز دقیقاً چیست؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 07 — Subscription و چرخه پرداخت

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** بعد از Trial، Subscription چگونه خرید و تمدید می‌شود؟ درگاه پرداخت، تمدید خودکار، Invoice، کد تخفیف و لغو اشتراک را در V1 چگونه می‌خواهیم و مرجع نهایی وضعیت پرداخت کدام سیستم است؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 08 — Entitlement و Quota

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** Plan/Subscription دقیقاً چه محدودیت‌هایی ایجاد کند؟ Feature access، تعداد Site، تعداد request/operation، حجم Media، AI/Chat credit یا موارد دیگر؟ کدام محدودیت‌ها باید hard quota باشند و کدام صرفاً permission؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 09 — Credential Vault و چرخه Credential

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** Credentialهای WordPress/WooCommerce در Backend دقیقاً چگونه نگهداری و مدیریت شوند؟ آیا در V1 باید rotation، revoke، re-connect، چند Credential برای یک Site و تشخیص Credential منقضی/باطل‌شده را پشتیبانی کنیم؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 10 — API Contract و Versioning

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** API رسمی Backend را REST/JSON نسخه‌بندی‌شده روی مسیر `/api/v1/...` قطعی می‌کنیم؟ آیا OpenAPI باید Source of Truth قرارداد API و مبنای Contract Test باشد؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 11 — Authorization در سطح Account و Site

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** آیا این قانون را به‌صورت غیرقابل‌مذاکره ثبت کنیم که هر درخواست ابتدا Session/Account را بررسی کند و سپس ثابت کند `site_id` متعلق به همان Account است و Plan/Entitlement اجازه آن operation را می‌دهد؟ همچنین در صورت حدس‌زدن `site_id` کاربر دیگر باید دسترسی رد شود.

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 12 — دامنه عملیات WooCommerce در V1

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** دقیقاً کدام عملیات را در V1 پشتیبانی کنیم؟ حداقل Products، Orders، Customers، Categories، Variations و Media مشخص شده‌اند؛ آیا Settings، Coupons، Shipping، Payments، Reports یا عملیات دیگری هم باید وارد V1 شوند؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 13 — Media Upload و Storage

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** در V1 تصویر از App مستقیماً به Backend برسد و Backend آن را به WordPress Media منتقل کند، یا Object Storage واسط لازم است؟ حداکثر حجم، MIME typeهای مجاز، timeout و رفتار retry/duplicate برای Upload چه باشد؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 14 — Idempotency و Operation Identity

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** برای mutationها `idempotency_key` و `operation_id` را یک شناسه می‌خواهیم یا دو مفهوم مستقل؟ TTL رکورد idempotency، رفتار retry، conflict روی payload متفاوت و scope آن در سطح Account/Site/Operation دقیقاً چیست؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 15 — Timeout-after-success و Reconciliation

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** اگر Backend روی WooCommerce عملیات را موفق انجام دهد ولی پاسخ به Client نرسد، Client از چه APIای نتیجه را پیدا کند؟ آیا برای V1 یک Operation Ledger پایدار لازم است و تا چه مدت وضعیت operation نگهداری شود؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 16 — Sync، Queue و عملیات Asynchronous

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** کدام عملیات باید synchronous باشند و کدام باید وارد Queue/Worker شوند؟ برای عملیات async سیاست retry، exponential backoff، timeout، concurrency و failure نهایی چه باشد؟ آیا V1 به Dead Letter Queue نیاز دارد؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 17 — Webhook و Event Reconciliation

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** آیا Backend در V1 باید از WooCommerce/WordPress Webhook دریافت کند؟ اگر بله، دقیقاً کدام eventها مهم‌اند و برای duplicate، out-of-order، missed webhook و replay چه مدل reconciliation داشته باشیم؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 18 — WooGit Bridge

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** WooGit Bridge دقیقاً چه مسئولیتی دارد؟ کدام قابلیت‌ها باید از Bridge عبور کنند و کدام عملیات مستقیماً از Backend به WordPress/WooCommerce انجام شوند؟ آیا Bridge در V1 الزامی است یا فقط برای قابلیت‌های خاص؟

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 19 — Security / Rate Limit / Audit / Data Retention

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** سیاست امنیت عملیاتی V1 دقیقاً چه باشد؟ Rate limit بر اساس Account/Site/Device/IP، محدودیت جداگانه Mutation، Audit eventهای حساس، retention داده‌ها و سیاست حذف Account/Site را مشخص کنیم. Credential و داده حساس نباید وارد log شوند.

**پاسخ:** _هنوز ثبت نشده_

---

## سؤال 20 — Production Ready و مرز نهایی V1

**وضعیت:** ⬜ بی‌پاسخ

**سؤال:** دقیقاً چه معیارهایی باید پاس شوند تا Backend V1 را Production Ready بدانیم؟ حداقل باید درباره API contract، Connection Verification، authorization isolation، Credential security، idempotency، timeout-after-success، migration از Direct Connection، تست‌های failure، observability و rollback/cutover تصمیم نهایی بگیریم.

**پاسخ:** _هنوز ثبت نشده_

---

## چرا بعضی سؤال‌های قبلی تغییر کردند؟

چند سؤال قبلی بیش از حد وارد جزئیات پیاده‌سازی می‌شدند یا موضوعی را که هنوز تصمیم محصولی آن مشخص نشده بود با یک راه‌حل فنی خاص قاطی می‌کردند.

تغییرهای اصلی:

- سؤال مستقل درباره **Master Key/KMS** حذف و به تصمیم کلی Credential Lifecycle تبدیل شد؛ انتخاب ابزار رمزنگاری بهتر است بعد از تعیین نیاز امنیتی انجام شود.
- سؤال مستقل درباره **Observability** در Definition of Production Ready و سیاست عملیاتی ادغام شد تا باکس سؤال‌ها بیش از حد زیرساختی نشود.
- سؤال **API Gateway** به API Contract/Versioning تبدیل شد چون معماری Gateway و مسیر `/api/v1/...` قبلاً جهت‌گیری مشخص دارند و سؤال باید روی موارد واقعاً باز تمرکز کند.
- سؤال **Timeout-after-success** حفظ شد چون یک ریسک correctness واقعی است و صرفاً جزئیات فنی نیست.
- سؤال جدید و صریح درباره **Connection Verification** اضافه شد، چون اکنون مشخص شده اولین outbound request به سایت مشتری باید Verification read-only باشد.
- سؤال **Authorization در سطح Account/Site** جدا شد چون isolation بین کاربران یک الزام امنیتی بنیادی است و نباید داخل سؤال کلی API گم شود.
- Queue و Webhook جدا نگه داشته شدند چون انتخاب آن‌ها مستقیماً روی معماری عملیات و consistency اثر می‌گذارد.

## قوانین ثبت پاسخ

بعد از هر پاسخ کاربر:

1. متن تصمیم کاربر بدون تغییر معنایی در بخش `پاسخ` همان سؤال ثبت شود.
2. وضعیت همان سؤال به `✅ پاسخ داده شد` تغییر کند.
3. اگر پاسخ باعث تغییر یک ADR یا سند دیگر شد، آن سند نیز باید به‌صورت سازگار به‌روزرسانی شود.
4. سؤال بعدی به‌عنوان `سؤال فعلی` مشخص شود.
5. تعداد پاسخ‌ها و باقی‌مانده در بخش وضعیت کلی به‌روزرسانی شود.
6. هیچ تصمیمی که کاربر نگرفته، به‌عنوان پاسخ قطعی ثبت نشود.
7. اگر یک سؤال در اثر تصمیمات بعدی بی‌معنا شد، حذف یا با سؤال مهم‌تر جایگزین شود؛ اما تاریخچه تصمیم قبلی نباید جعل شود.
