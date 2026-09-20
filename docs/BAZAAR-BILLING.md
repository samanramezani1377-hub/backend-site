# Cafe Bazaar Billing — V1

این مسیر فقط روی Branch/Release مخصوص Bazaar فعال می‌شود و مسیر پرداخت WooCommerce/ZarinPal نسخه Standard را تغییر نمی‌دهد.

## Android

نسخه Bazaar از Poolakey 2.2.0 استفاده می‌کند. Poolakey اتصال به سرویس Billing کافه‌بازار، خرید Subscription و دریافت `purchaseToken` را انجام می‌دهد. urlPoolakey official repositoryhttps://github.com/cafebazaar/Poolakey

App پس از موفقیت UI خرید، هیچ entitlement محلی صادر نمی‌کند. `purchaseToken` و SKU به Backend ارسال می‌شوند.

## Backend

Endpoint:

`POST /wp-json/woogit/v1/billing/bazaar/verify`

Backend باید:

1. Session را احراز کند.
2. Account + Site Ownership را دوباره بررسی کند.
3. package name را با package پیکربندی‌شده مقایسه کند.
4. SKU را به Plan/Variation واقعی WooGit map کند.
5. Purchase را با Cafe Bazaar Developer API بررسی کند.
6. Purchase Token را به‌صورت idempotent ثبت کند.
7. Entitlement را فعال کند.
8. Billing Session را به Operational Session تبدیل کند.

Developer API credentialها نباید داخل APK قرار بگیرند.

## Server configuration

در `wp-config.php` یا محیط deployment، مقادیر زیر باید قبل از فعال‌سازی production تنظیم شوند:

- `WOOGIT_BAZAAR_PACKAGE_NAME`
- `WOOGIT_BAZAAR_CLIENT_ID`
- `WOOGIT_BAZAAR_CLIENT_SECRET`
- `WOOGIT_BAZAAR_REFRESH_TOKEN`
- اختیاری: `WOOGIT_BAZAAR_API_BASE_URL`

Access token فقط به‌صورت transient سمت Backend cache می‌شود.

## Plan SKU mapping

هر WooGit Subscription Product یک فیلد `Cafe Bazaar SKU` دارد که داخل همان Product Data پنل Milo نمایش داده می‌شود. برای Variable Subscription، هر Variation نیز SKU جداگانه دارد. برای تنظیمات محصول از صفحه جداگانه WooGit/Bazaar استفاده نمی‌شود؛ Milo مرجع محصول و دوره Subscription است.

App فقط SKU برگشتی از Backend را مصرف می‌کند؛ قیمت یا مدت را از Client قبول نمی‌کند.

## Android RSA key

RSA Public Key کافه‌بازار secret محسوب نمی‌شود، اما باید برای local Poolakey security در build مربوط به Bazaar تنظیم شود:

`-Pwoogit.bazaar.rsaPublicKey="..."`

اگر کلید تنظیم نشده باشد، local security check غیرفعال می‌شود و تأیید نهایی همچنان فقط از Backend انجام می‌شود؛ برای production باید کلید واقعی تنظیم شود.

## مهم

فعلاً تا زمانی که SKUهای واقعی محصولات و credentialهای Developer API تنظیم نشده‌اند، build Bazaar از نظر کد و CI قابل build است ولی خرید production قابل تکمیل نیست.
