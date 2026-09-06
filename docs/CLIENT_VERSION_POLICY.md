# سیاست نسخه Client ووگیت

> وضعیت: **V1 Contract — Locked**
>
> این سند سیاست کنترل نسخه اپ Android WooGit در Backend را تعریف می‌کند. اپ در repository مستقل `samanramezani1377-hub/woogit` قرار دارد و Backend فقط قرارداد و enforcement سمت سرور را تعریف می‌کند.

## ۱. هدف

Backend باید بتواند نسخه Client را برای **هر درخواست** بررسی کند و در صورت منسوخ بودن نسخه، درخواست را قبل از رسیدن به Customer WordPress/WooCommerce متوقف کند.

این قابلیت فقط به «قدیمی‌تر یا جدیدتر بودن» نسخه محدود نیست.

Backend باید بتواند یک نسخه مشخص را به‌صورت مستقل منسوخ کند، حتی اگر نسخه‌های قبل یا بعد از آن هنوز مجاز باشند.

مثال الزامی:

```text
3.0.0  -> مجاز
4.0.0  -> منسوخ
5.0.0  -> مجاز
```

بنابراین `minimum_supported_version` به‌تنهایی برای این نیاز کافی نیست.

## ۲. مدل Version Policy

سیاست نسخه حداقل شامل این موارد است:

```text
latest_version
recommended_version
minimum_supported_version
deprecated_versions[]
```

نمونه:

```json
{
  "latest_version": "5.0.0",
  "recommended_version": "5.0.0",
  "minimum_supported_version": "3.0.0",
  "deprecated_versions": [
    "4.0.0"
  ]
}
```

در این مثال:

- `3.0.0` قابل استفاده است.
- `4.0.0` صراحتاً Block می‌شود.
- `5.0.0` قابل استفاده است.

`deprecated_versions` یک deny-list مستقل است و نباید از `minimum_supported_version` مشتق یا حذف شود.

## ۳. بررسی در هر Request

هر Request که از Client به Backend وارد می‌شود باید نسخه Client را همراه داشته باشد.

قرارداد هدف:

```http
X-WooGit-App-Version: 5.0.0
```

در وضعیت فعلی App هنوز این Header را ارسال نمی‌کند. بنابراین این مورد **Migration Target** است و نباید به‌عنوان قابلیت موجود Client گزارش شود.

پس از اضافه شدن این قابلیت به App، Version Gate باید روی تمام مسیرهای Backend اعمال شود و نباید فقط هنگام Login یا Startup بررسی شود.

## ۴. ترتیب Enforcement

Version Gate باید قبل از ارسال هر درخواست به Customer WordPress/WooCommerce اجرا شود.

ترتیب هدف:

```text
App Request
    ↓
Client Version Gate
    ├── deprecated → REJECT
    └── allowed
          ↓
Authentication / Session
          ↓
Site Identity
          ↓
Subscription
          ↓
Entitlement / Permission
          ↓
Rate Limit / Security
          ↓
Controlled Proxy
          ↓
Customer WordPress / WooCommerce
```

اگر نسخه منسوخ باشد، هیچ outbound request تجاری به سایت مشتری نباید ارسال شود.

## ۵. نسخه‌های منسوخ‌شده مستقل از Minimum

این قسمت یک اصل معماری است:

> **`minimum_supported_version` مرز عمومی پشتیبانی است؛ `deprecated_versions[]` امکان استثنای صریح برای Block کردن نسخه‌های خاص را فراهم می‌کند.**

بنابراین Backend باید بتواند چنین policyهایی را بیان کند:

```text
minimum_supported_version = 3.0.0

deprecated_versions =
  4.0.0
```

و نتیجه:

| Client Version | نتیجه |
|---|---|
| 2.x | رد؛ پایین‌تر از حداقل پشتیبانی |
| 3.x | مجاز، در صورت قرار نداشتن در deny-list |
| 4.0.0 | رد؛ صراحتاً منسوخ شده |
| 4.1.0 | مجاز، در صورت قرار نداشتن در deny-list و عبور از minimum |
| 5.x | مجاز، در صورت قرار نداشتن در deny-list |

این قابلیت اجازه می‌دهد نسخه‌ای که مثلاً به‌دلیل یک باگ خاص، مشکل امنیتی یا ناسازگاری Backend باید فوراً متوقف شود، بدون اینکه نسخه‌های دیگر الزاماً متوقف شوند.

## ۶. پشتیبانی از Range در آینده

برای V1 وجود `deprecated_versions[]` دقیق الزامی است.

معماری باید طوری طراحی شود که بعداً بتوان range نیز اضافه کرد؛ برای مثال:

```text
4.0.x -> deprecated
```

یا:

```text
>=4.0.0 <4.1.0 -> deprecated
```

این قابلیت نباید با `minimum_supported_version` جایگزین شود.

## ۷. رفتار نسخه منسوخ

وقتی Client نسخه منسوخ ارسال کند، Backend باید یک خطای استاندارد و machine-readable برگرداند.

نمونه:

```json
{
  "code": "APP_VERSION_DEPRECATED",
  "message": "این نسخه از WooGit دیگر پشتیبانی نمی‌شود.",
  "minimum_supported_version": "3.0.0",
  "latest_version": "5.0.0",
  "update_required": true,
  "retryable": false,
  "request_id": "..."
}
```

Backend نباید برای این خطا، response خام WordPress/WooCommerce را عبور دهد.

## ۸. رفتار نسخه ناشناخته یا فاقد Version

تا زمان Migration کامل، Clientهای قدیمی ممکن است Version Header نداشته باشند.

Policy مربوط به این وضعیت باید صریحاً در زمان implementation انتخاب شود؛ Backend نباید رفتار را حدس بزند.

دو حالت ممکن:

```text
Legacy / Migration Mode
    missing version -> allow or controlled warning
```

یا بعد از پایان Migration:

```text
missing version -> APP_VERSION_REQUIRED
```

این تصمیم باید قبل از فعال شدن enforcement اجباری ثبت شود.

## ۹. مالک Policy

Backend مرجع نهایی Version Policy است.

Client نباید بتواند با ارسال Version دلخواه، سیاست را تغییر دهد.

`latest_version`، `recommended_version`، `minimum_supported_version` و `deprecated_versions[]` باید از تنظیمات معتبر Backend خوانده شوند و تغییر آن‌ها باید قابل audit باشد.

## ۱۰. عدم عبور درخواست برای نسخه Block شده

این invariant اجباری است:

```text
Deprecated Client
      ↓
Backend Version Gate
      ↓
APP_VERSION_DEPRECATED
      ↓
NO Customer Request
```

یعنی حتی اگر Subscription معتبر باشد یا Credential سایت کاملاً صحیح باشد، نسخه منسوخ اجازه عبور به Proxy را ندارد.

## ۱۱. وابستگی به Client

در حال حاضر UI/handling مربوط به `APP_VERSION_DEPRECATED` داخل App ساخته نشده است.

در Migration آینده Client باید:

1. Version اپ را ارسال کند.
2. خطای `APP_VERSION_DEPRECATED` را تشخیص دهد.
3. `update_required` را در UX مناسب نمایش دهد.
4. در صورت نیاز کاربر را به مسیر Update هدایت کند.

این موارد مسئولیت repository Backend نیستند؛ Backend فقط contract و enforcement سمت سرور را فراهم می‌کند.

## ۱۲. Definition of Done

این قابلیت زمانی کامل محسوب می‌شود که:

- [ ] Client Version در هر Backend Request قابل دریافت باشد.
- [ ] Version Policy سمت Backend authoritative باشد.
- [ ] `latest_version` وجود داشته باشد.
- [ ] `recommended_version` وجود داشته باشد.
- [ ] `minimum_supported_version` وجود داشته باشد.
- [ ] `deprecated_versions[]` برای Block کردن نسخه‌های مشخص وجود داشته باشد.
- [ ] یک نسخه مشخص بتواند بدون Block کردن نسخه قبل/بعد منسوخ شود.
- [ ] Version Gate قبل از outbound Customer request اجرا شود.
- [ ] `APP_VERSION_DEPRECATED` استاندارد باشد.
- [ ] نسخه منسوخ هیچ درخواست Customer را دریافت نکند.
- [ ] تغییر Policy قابل audit باشد.
- [ ] Contract Client برای handling این خطا بعداً به‌روزرسانی شود.
