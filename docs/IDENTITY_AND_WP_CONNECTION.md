# هویت حساب و اتصال WordPress در WooGit

## ۱. وضعیت این سند

این سند مرجع Flow اتصال سایت، احراز Account و Credentialهای مقصد است.

اپ Android موجود WooGit در repository مستقل قرار دارد و صفحه اتصال فعلی آن شامل Credentialهای سایت مشتری است. Backend نباید برای این Flow یک معماری سنگین‌تر از نیاز واقعی اپ تحمیل کند.

## ۲. Credentialهای اتصال سایت مشتری

اپ فعلی این چهار Credential را در اختیار دارد و در مدل V1 آن‌ها را برای درخواست به Backend ارسال می‌کند:

- `WordPress Username`
- `WordPress Application Password`
- `WooCommerce Consumer Key`
- `WooCommerce Consumer Secret`

این Credentialها برای احراز هویت **در سایت مقصد** هستند، نه برای احراز هویت مصرف‌کننده در WooGit.

Application Password باید Credential برنامه‌ای WordPress باشد، نه رمز اصلی ورود به `wp-admin`. WordPress آن را برای API و احراز هویت ماشینی طراحی کرده و استفاده از آن برای REST API باید روی HTTPS باشد. citeturn0search0turn0search1turn0search2

مدل مفهومی:

```text
WP Username + WP Application Password
+ WC Consumer Key + WC Consumer Secret
        ↓
احراز هویت در Customer WordPress/WooCommerce
```

## ۳. WooGit Session

در کنار Credentialهای مقصد، درخواست عادی یک `WooGit Session` نیز دارد.

این Session برای احراز هویت و مجاز بودن مصرف‌کننده در **WooGit Backend** است و هیچ جایگزینی برای Credentialهای سایت مشتری نیست.

```text
WooGit Session
    = هویت/دسترسی در WooGit Backend

Customer Credentials
    = اعتبار دسترسی به Customer Site
```

## ۴. Flow درخواست عادی

```text
WooGit Android
      │
      │ WooGit Session
      │ + site_id / destination
      │ + 4 Customer Credentials
      │ + همان path/query/body عملیات
      ▼
WooGit Backend / WooGit Plugin
      │
      ├─ Session
      ├─ Account status
      ├─ Trial / Subscription
      ├─ Entitlement
      ├─ Site ownership
      ├─ Version / Rate Limit / Security
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

Backend نباید Request/Response را بی‌دلیل بازسازی یا Mirror کند. Customer WordPress/WooCommerce منبع اصلی داده فروشگاه باقی می‌ماند.

## ۵. Verification و Onboarding اولیه

اولین اتصال، چون ممکن است هنوز WooGit Session کامل وجود نداشته باشد، یک Bootstrap/Verification Flow جدا از درخواست‌های عادی است.

ترتیب کلی:

```text
App
  ↓
Customer Credentials
  ↓
WordPress reachability + authentication
  ↓
WooCommerce verification
  ↓
Site Identity
  ↓
Account / Trial lifecycle
  ↓
WooGit Session
  ↓
Normal requests
```

اگر Verification اولیه شکست بخورد، اتصال موفق، Account/Trial موفق یا Dashboard نباید ثبت/اعلام شود.

## ۶. Site موجود

برای Site Identity موجود، موفقیت Verification Credentialهای همان سایت اثبات دسترسی به مقصد است. پس از آن Backend می‌تواند Account مربوط به همان Site را شناسایی و Session را ادامه دهد، مطابق Flow نهایی Account Lifecycle.

ورود عادی به WooGit نباید وابسته به Google Account یا رمز جداگانه‌ای باشد مگر اینکه در یک سؤال معماری بعدی صراحتاً چنین چیزی تصویب شود.

## ۷. Site جدید

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
   ↓
Dashboard / normal operations
```

## ۸. نگهداری Credential در Backend

در مدل فعلی **Credential Vault اجباری برای هر درخواست نیست**.

Credentialهای سایت در درخواست عادی از Client می‌آیند و Backend آن‌ها را فقط برای همان مقصد و همان درخواست مصرف می‌کند. Backend نباید برای Forward کردن هر Request به Vault lookup وابسته باشد.

اگر در آینده قابلیت‌هایی مانند background jobs، webhooks یا عملیات بدون حضور Client به نگهداری امن Credential نیاز داشته باشند، آن موضوع باید به‌عنوان یک تصمیم جداگانه تعیین شود.

## ۹. قوانین امنیتی Credential

Backend باید:

- Credentialها را روی مسیر HTTPS دریافت کند؛
- آن‌ها را در Log، Analytics، Crash Report یا Audit Metadata ثبت نکند؛
- آن‌ها را در Error Response یا Response عادی به دیگری برنگرداند؛
- آن‌ها را به Account یا Site دیگری افشا نکند؛
- فقط برای Customer Site مقصد استفاده کند؛
- از Credential ارسالی برای دسترسی به مقصدی غیر از Site مجاز استفاده نکند.

این تصمیم به این معنی نیست که Credentialها در Client «ممنوع» هستند؛ اپ فعلی همین Flow مستقیم را دارد و V1 عمداً تغییرات Client را حداقلی نگه می‌دارد.

## ۱۰. اصل نهایی

```text
WooGit Session
        → احراز و مجوز مصرف‌کننده در Backend

Customer Credentials
        → احراز Backend نزد Customer Site

Backend
        → کنترل‌های ضروری WooGit
        → Lightweight Proxy
        → حداقل تغییر در Request/Response
```

Customer WordPress/WooCommerce منبع اصلی داده فروشگاه است و WooGit Backend مرجع Account، Site ownership، Subscription و Entitlement است.
