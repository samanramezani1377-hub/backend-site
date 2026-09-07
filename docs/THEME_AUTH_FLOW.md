# معماری احراز هویت و نشست وب تم WooGit

## ۱. دامنه

این سند فقط احراز هویت وب‌سایت و پرتال مشتری را تعریف می‌کند. احراز هویت عملیاتی App مستقل است.

## ۲. دو نوع نشست

```text
Android App  → X-WooGit-Session
Web Theme    → X-WooGit-Web-Session
```

این دو نشست قابل جایگزینی نیستند.

## ۳. ورود

ورود V1 با این اطلاعات انجام می‌شود:

```text
Site URL
Password
```

Backend باید Site و Account مرتبط را پیدا و اعتبار Web Password را بررسی کند. Theme فقط نتیجه را مصرف می‌کند.

## ۴. ثبت‌نام و اتصال اولیه

فرم اولیه شامل Store URL، نام کاربری WordPress، Application Password وردپرس، Consumer Key و Consumer Secret ووکامرس است. این Credentialها فقط برای جریان اعتبارسنجی اولیه request-scoped هستند.

Theme نباید آن‌ها را در Cookie، Local Storage، Session Storage، دیتابیس Theme، لاگ، تحلیل‌گر یا HTML نگه دارد.

## ۵. ساخت Web Credential

طبق قرارداد فعلی Backend، تنظیم Web Password از مسیر `setup-web-credentials` به یک App Session معتبر وابسته است. این موضوع باید در پیاده‌سازی نهایی ثبت‌نام وب به‌صورت صریح حل و مستند شود و Theme نباید یک جریان جایگزین حدس بزند.

## ۶. نگهداری نشست در مرورگر

Token نشست وب نباید در URL قرار گیرد. پیاده‌سازی باید در برابر XSS و سرقت نشست مقاوم باشد. گزینه ترجیحی، Cookie امن `HttpOnly`، `Secure` و `SameSite` مناسب یا یک لایه BFF/Bridge امن است، مشروط به سازگاری با قرارداد Backend.

اگر قرارداد فعلی فقط Header را پشتیبانی کند، Theme نباید بدون طراحی امنیتی مشخص Token خام را در `localStorage` قرار دهد.

## ۷. پایان نشست

در Logout، Theme باید وضعیت موقت محلی را پاک کند و Backend باید نشست را revoke کند.

در `401` یا اعلام انقضای نشست:

```text
پاک‌سازی نشست موقت
        ↓
نمایش Login
        ↓
ایجاد Web Session جدید
        ↓
بررسی دوباره Account + Site Ownership توسط Backend
```

Theme حق revive کردن نشست منقضی‌شده را ندارد.

## ۸. تغییر رمز

تغییر رمز باید از endpoint قراردادی Backend انجام شود. پس از تغییر موفق رمز، همه Web Sessionهای قبلی طبق قرارداد Backend revoke می‌شوند و Theme باید کاربر را به ورود مجدد هدایت کند.

## ۹. تفکیک Credentialها

Customer Site Credential با WooGit Web Password یکسان نیست. Theme نباید Credential سایت مشتری را به عنوان Account Password ذخیره یا بازاستفاده کند.

## ۱۰. Rate Limit و خطا

Login و عملیات حساس باید رفتار `429` را پشتیبانی کنند. Theme نباید با retry تهاجمی محدودیت Backend را دور بزند.

پیام خطای ورود باید عمومی باشد و وجود یا وضعیت دقیق Account/Site را بیش از قرارداد Backend افشا نکند.
