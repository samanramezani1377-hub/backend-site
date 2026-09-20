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

در WordPress Admin از مسیر `WooGit → Settings → کافه‌بازار` می‌توان این مقادیر را تنظیم کرد. برای deploymentهای امن، تعریف Constantهای متناظر در `wp-config.php` نیز پشتیبانی می‌شود و Constant بر مقدار ذخیره‌شده در Settings اولویت دارد.

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


## Developer API OAuth setup

The Backend now includes a one-time OAuth authorization flow for obtaining the Cafe Bazaar Developer API refresh token.

Redirect URI to register in the Cafe Bazaar Developer API client:

`https://woogit.ir/wp-json/woogit/v1/billing/bazaar/oauth/callback`

Flow:

1. In `WooGit → Settings → کافه‌بازار`, save Package Name, Client ID and Client Secret.
2. Click `اتصال به کافه‌بازار`.
3. WooGit creates a short-lived, single-use state bound to the WordPress administrator and redirects to Cafe Bazaar authorization.
4. Cafe Bazaar returns an authorization code to the callback.
5. Backend exchanges the code server-side at `/devapi/v2/auth/token/`.
6. The returned Refresh Token is stored in the existing `woogit_bazaar_settings` option. Client Secret and Refresh Token are never placed in the Android app.

The callback URL is generated from WordPress' registered REST route rather than being hard-coded in the runtime flow, and the OAuth state is deleted before token exchange so it cannot be replayed.
