# هویت حساب و اتصال WordPress در WooGit Backend

## ۱. وضعیت این سند

این سند مرجع Flow اتصال سایت، احراز Account و Credentialهای مقصد است.

اپ Android موجود WooGit مستقل است و صفحه اتصال فعلی آن Credentialهای Customer Site را دریافت می‌کند. Backend باید با همین سطح اتصال سازگار بماند.

## ۲. تنظیمات هر Customer Site

هر مشتری/فروشگاه یک `Site Identity` مستقل در WooGit دارد. تنظیمات اتصال یک Site نباید با Site دیگر مشترک یا مخلوط شود.

اطلاعات اتصال Customer Site در V1 شامل این چهار Credential است:

- `WordPress Username`
- `WordPress Application Password`
- `WooCommerce Consumer Key`
- `WooCommerce Consumer Secret`

این Credentialها برای احراز هویت **در سایت مقصد** هستند، نه برای احراز Client در WooGit.

Application Password باید Credential برنامه‌ای WordPress باشد، نه رمز اصلی ورود به `wp-admin`.

```text
Account
  └── Site Identity
       ├── WordPress URL
       ├── WordPress Username
       ├── WordPress Application Password
       ├── WooCommerce Consumer Key
       └── WooCommerce Consumer Secret
```

## ۳. تنظیمات خود WooCommerce

تنظیمات خود WooCommerce متعلق به Customer Site است و روی همان WordPress مشتری باقی می‌ماند.

WooGit Backend نباید این تنظیمات را با تنظیمات Account یا Site Identity خود یکی فرض کند.

```text
WooGit Backend
├── Account / Subscription / Entitlement
└── Site Identity + Connection Metadata

Customer WordPress / WooCommerce
├── WooCommerce Settings
├── Products
├── Orders
├── Customers
├── Media
└── سایر داده‌های فروشگاه
```

Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است.

## ۴. WooGit Session

در کنار چهار Credential مقصد، درخواست عادی یک `WooGit Session` معتبر دارد.

Session برای احراز هویت و مجاز بودن Client در **WooGit Backend** است و جایگزین Credentialهای Customer Site نیست.

Access Token + Refresh Token جزو معماری V1 نیست.

```text
WooGit Session
    = هویت/دسترسی در WooGit Backend

Customer Credentials
    = اعتبار دسترسی به Customer WordPress/WooCommerce
```

## ۵. Flow درخواست عادی

```text
WooGit Android
      │
      │ WooGit Session
      │ + site_id
      │ + 4 Customer Credentials
      │ + همان path/query/body عملیات
      ▼
WooGit Backend / Main Plugin
      │
      ├─ Session
      ├─ Account status
      ├─ Trial / Subscription
      ├─ Entitlement
      ├─ Site ownership
      ├─ Version / Rate Limit / Security
      └─ Idempotency where required
      │
      ▼
Lightweight Proxy
      │
      │ همان Request با Customer Credentials
      ▼
Customer WordPress / WooCommerce
      │
      │ Response
      ▼
WooGit Backend
      │
      │ حداقل تغییر لازم
      ▼
WooGit Android
```

اگر Account بسته/غیرفعال یا Trial/Subscription منقضی باشد، Request نباید به Customer Site ارسال شود.

## ۶. Verification و Onboarding اولیه

ممکن است در اولین اتصال WooGit Session هنوز وجود نداشته باشد؛ بنابراین Verification یک Bootstrap Flow مستقل دارد:

```text
App
  ↓
4 Customer Credentials + Site URL
  ↓
WordPress reachability + authentication
  ↓
WooCommerce verification
  ↓
Site Identity
  ↓
Existing Account OR New Account
  ↓
Trial eligibility
  ↓
WooGit Session
  ↓
Normal requests
```

Verification باید read-only باشد و صرفاً برای تست، Product/Order/Media mutation انجام ندهد.

## ۷. Site موجود

برای Site Identity موجود، Verification با Credentialهای همان Site انجام می‌شود و Backend Account مالک آن Site را resolve می‌کند.

موفقیت Verification یک Site نباید به معنی دسترسی به Site دیگر باشد.

## ۸. Site جدید

برای Site بدون Account قبلی:

```text
Verification موفق
   ↓
Site Identity
   ↓
Account creation / completion
   ↓
Trial eligibility
   ↓
WooGit Session
```

Trial برابر ۱۵ روز است و به Site Identity/دامنه تعلق دارد.

## ۹. نگهداری Credential در Backend

Credential Vault برای Proxy عادی اجباری نیست.

در V1:

```text
Client → Session + 4 Customer Credentials → Backend → Customer Site
```

Backend می‌تواند Credentialهای ارسالی را فقط برای همان Request مصرف کند. اگر در آینده background jobs، webhooks یا عملیات بدون حضور Client نیاز به Credential پایدار داشته باشند، نگهداری رمزنگاری‌شده باید به‌عنوان یک قابلیت جداگانه تصمیم‌گیری شود.

## ۱۰. قوانین امنیتی

Backend باید:

- Credentialها را فقط از مسیر امن دریافت کند؛
- آن‌ها را در Log، Analytics، Crash Report یا Audit Metadata ثبت نکند؛
- آن‌ها را در Error/Response برنگرداند؛
- آن‌ها را به Account یا Site دیگر افشا نکند؛
- فقط برای Customer Site مجاز استفاده کند؛
- از `site_id` برای اعمال Site Isolation استفاده کند.

## ۱۱. اصل نهایی

```text
WooGit Session
        → احراز و مجوز Client در Backend

Customer Credentials
        → احراز Backend نزد Customer WordPress/WooCommerce

WooCommerce Settings
        → متعلق به Customer Site

Backend
        → Account / Site / Subscription / Entitlement
        → Lightweight Proxy
        → حداقل تغییر در Request/Response
```

`WooGit Gateway Plugin` یک Plugin جداگانه روی سایت مشتری است و در Scope فعلی Backend توسعه داده نمی‌شود.
