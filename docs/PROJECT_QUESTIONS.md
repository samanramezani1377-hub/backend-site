# 🟨 باکس سؤالات تعیین‌تکلیف پروژه WooGit Backend

> **وضعیت:** بازطراحی‌شده بر اساس معماری فعلی V1 و تصمیمات ثبت‌شده تا این مرحله.
>
> این باکس فقط سؤال‌هایی را نگه می‌دارد که پاسخ آن‌ها واقعاً یک تصمیم معماری، محصولی، امنیتی یا عملیاتی باز ایجاد می‌کند. موضوعاتی که قبلاً در اسناد پروژه قطعی شده‌اند نباید دوباره به‌عنوان سؤال مطرح شوند.
>
> **وضعیت فعلی:** سؤال‌های بی‌پاسخ فعلاً از این سند حذف شده‌اند و فقط تصمیم‌های ثبت‌شده نگه داشته می‌شوند.

## وضعیت کلی

- پاسخ داده‌شده: **۱ / ۱**
- سؤال باز: **۰**

---

## سؤال 01 — مدل احراز هویت App ↔ Backend و مدل Proxy

**وضعیت:** ✅ پاسخ داده شد

**تصمیم نهایی:**

برای کمترین تغییر در Android App و کمترین محاسبات غیرضروری در Backend، مسیر عادی عملیات WooCommerce باید یک **Proxy سبک** باشد. Backend نباید برای هر درخواست داده‌های WooCommerce را بازسازی، Mirror یا بی‌دلیل تبدیل کند؛ باید تا حد ممکن همان درخواست App را به سایت مشتری Forward کند و Response سایت مشتری را تقریباً همان‌طور به App برگرداند.

### اعتبار اتصال به سایت مشتری

App برای اتصال Backend به WordPress/WooCommerce سایت مقصد این چهار مقدار را در اختیار Backend قرار می‌دهد:

- `WordPress Username`
- `WordPress Application Password`
- `WooCommerce Consumer Key`
- `WooCommerce Consumer Secret`

### اعتبار مصرف Backend

- `WooGit Session`

این Session برای شناسایی و اعتبارسنجی کاربر/حساب در WooGit Backend استفاده می‌شود و با Credentialهای سایت مشتری تفاوت دارد.

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
        │ customer credentials
        │ + path/query/body همان عملیات
        ▼
WooGit Backend
        │
        ├─ بررسی WooGit Session
        ├─ بررسی وضعیت Account
        ├─ بررسی اعتبار Trial / Subscription
        ├─ بررسی Subscription / Entitlement
        ├─ بررسی دسترسی Account به Site
        ├─ Version / Rate Limit / Security checks
        │
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

اگر حساب بسته/غیرفعال باشد یا Trial/Subscription/زمان مجاز دسترسی تمام شده باشد، درخواست نباید به سایت مشتری ارسال شود.

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
- نباید توسط Backend به App برگردانده شوند مگر در یک Flow صریح و امن که برای آن تصمیم‌گیری شده باشد.

### محدوده این تصمیم

این مدل برای **درخواست‌های عادی بعد از وجود WooGit Session معتبر** است. Flow اولین اتصال/Onboarding طبق اسناد معماری و قرارداد Client انجام می‌شود.

---

> **یادداشت:** سؤال‌های بی‌پاسخ برای جلوگیری از باقی‌ماندن تصمیم‌های معلق در مستندات، فعلاً حذف شده‌اند. در صورت نیاز به بازکردن یک تصمیم جدید، سؤال مشخص و محدود در همین سند اضافه خواهد شد.
