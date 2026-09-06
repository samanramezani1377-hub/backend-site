# هویت حساب و اتصال WordPress در WooGit Backend

## ۱. وضعیت این سند

این سند مرجع Flow اتصال سایت، هویت Account و Credentialهای مقصد است.

اصل قفل‌شده V1: **Account بر اساس Email ساخته یا Resolve نمی‌شود. Site Identity ریشه هویت و مالکیت Account است.**

## ۲. اصل هویت Account و Site

هر Customer Site یک `Site Identity` مستقل در WooGit دارد و Account متعلق به همان Site است.

```text
Verified Customer Site
        │
        ▼
   Site Identity
        │
        ▼
      Account
```

`email` فقط اطلاعات تماس (Contact Metadata) است و **هرگز** نباید به‌عنوان شناسه هویتی Account، کلید Resolve Account، یا اثبات مالکیت Account استفاده شود.

بنابراین Backend نباید هیچ‌وقت این مدل را اجرا کند:

```text
Request email
   ↓
findOrCreate(email)
   ↓
Account identity
```

و نباید فرض کند کسی که Credential معتبر یک Site را دارد، مالک Account مربوط به هر Email دلخواه در Request است.

در عوض، Verification موفق Credentialهای WordPress و WooCommerce فقط کنترل **همان Customer Site** را اثبات می‌کند و Account فقط برای همان Site ایجاد یا Resolve می‌شود.

## ۳. تنظیمات هر Customer Site

هر مشتری/فروشگاه یک `Site Identity` مستقل در WooGit دارد. تنظیمات اتصال یک Site نباید با Site دیگر مشترک یا مخلوط شود.

اطلاعات اتصال Customer Site در V1 شامل این چهار Credential است:

- `WordPress Username`
- `WordPress Application Password`
- `WooCommerce Consumer Key`
- `WooCommerce Consumer Secret`

این Credentialها برای احراز هویت **در سایت مقصد** هستند، نه برای احراز Client در WooGit.

Application Password باید Credential برنامه‌ای WordPress باشد، نه رمز اصلی ورود به `wp-admin`.

این Credentialها در Backend به‌عنوان دادهٔ پایدار Site Identity ذخیره نمی‌شوند و فقط در Scope همان Request مصرف می‌شوند.

```text
Site Identity
   └── Account
       └── Contact Metadata
            └── email (optional)

Customer Credentials
  └── Request-scoped only
```

## ۴. تنظیمات خود WooCommerce

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

## ۵. WooGit Session

در کنار چهار Credential مقصد، درخواست عادی یک `WooGit Session` معتبر دارد.

Session برای احراز هویت و مجاز بودن Client در **WooGit Backend** است و جایگزین Credentialهای Customer Site نیست.

Access Token + Refresh Token جزو معماری V1 نیست.

```text
WooGit Session
    = هویت/دسترسی در WooGit Backend

Customer Credentials
    = اعتبار دسترسی به Customer WordPress/WooCommerce
```

## ۶. Flow درخواست عادی

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

## ۷. Verification و Registration اولیه

در اولین اتصال ممکن است WooGit Session هنوز وجود نداشته باشد. بنابراین **اولین درخواست می‌تواند هم‌زمان Registration + Site Connection + Verification باشد**.

```text
App
  ↓
Site URL + Customer Credentials
  ↓
WordPress reachability + authentication
  ↓
WordPress identity/access verification
  ↓
WooCommerce availability/authentication
  ↓
Resolve existing Site Identity by verified site
  OR
Create new Site Identity + Account for that site
  ↓
Trial eligibility
  ↓
Create / Activate WooGit Session
  ↓
Normal requests
```

Verification موفق با Credentialهای معتبر WordPress/WooCommerce مبنای اثبات کنترل کاربر روی **همان Customer Site** است. این Verification مالکیت Email یا Account دیگری را اثبات نمی‌کند.

بعد از Verification موفق، اگر Site قبلاً وجود داشته باشد، Backend فقط Account متصل به همان Site را Resolve می‌کند. اگر Site جدید باشد، Account جدید فقط برای همان Site ایجاد می‌شود.

Email در این Flow اختیاری و صرفاً Contact Metadata است. ارسال Email دلخواه نمی‌تواند باعث Resolve شدن Account متعلق به آن Email شود.

`pending_verification` برای مسیر موفق اولیه اجباری نیست؛ Site پس از Verification کامل می‌تواند `verified/active` شود.

Verification باید read-only باشد و صرفاً برای تست، Product/Order/Media mutation انجام ندهد.

## ۸. Site موجود

برای Site Identity موجود، Verification با Credentialهای همان Site انجام می‌شود و Backend Account مالک **همان Site** را از رابطه Site → Account resolve می‌کند.

```text
Verify credentials for Site X
   ↓
Resolve Site X
   ↓
Resolve Account X
   ↓
Trial / Subscription / Entitlement
   ↓
WooGit Session scoped to Account X + Site X
```

حتی اگر Request شامل Email متعلق به Account دیگری باشد، آن Email نباید باعث تغییر Account یا انتقال Site شود.

موفقیت Verification یک Site هرگز به معنی دسترسی به Site دیگر نیست.

## ۹. Site جدید

برای Site بدون Account قبلی:

```text
Verification موفق برای Site X
   ↓
Create Site Identity X
   ↓
Create Account X فقط برای Site X
   ↓
Bind Account X ↔ Site X
   ↓
Trial eligibility
   ↓
WooGit Session scoped to X
```

Trial برابر ۱۵ روز است و به Site Identity/دامنه تعلق دارد.

## ۱۰. Site Isolation و عدم انتقال Account

این invariant اجباری V1 است:

```text
Account A ───────> Site A
Account A ──X────> Site B

Account B ───────> Site B
Account B ──X────> Site A
```

در هر Request عادی، Backend باید `Session → Account → Site Ownership → Entitlement` را بررسی کند. دانستن `site_id`، داشتن Credential سایت دیگر، یا ارسال Email مربوط به Account دیگر نباید Site/Account scope را تغییر دهد.

Account جدید فقط پس از Verification کامل Site ایجاد می‌شود و باید به همان Site متصل شود.

## ۱۱. عدم نگهداری Customer Credential در Backend

در V1، Backend **هیچ Customer Credentialای را در DB، Vault، Cache پایدار یا هر storage دائمی نگهداری نمی‌کند**.

الگوی اجباری:

```text
Client → WooGit Session + 4 Customer Credentials → Backend → Customer Site
```

Credentialهای Customer فقط برای همان Request مصرف می‌شوند و نباید به‌عنوان Credential پایدار نگهداری یا برای Request یا Site دیگری reuse شوند.

Backend نباید:

- Customer Credential را در DB ذخیره کند؛
- برای Customer Credential، Vault یا secret storage پایدار داشته باشد؛
- Customer Credential را در Cache پایدار نگهداری کند؛
- Customer Credential را در Log، Analytics، Crash Report یا Audit Metadata ثبت کند؛
- Customer Credential را در Error/Response برگرداند؛
- Customer Credential را برای Request یا Site دیگری reuse کند.

هر قابلیت آینده‌ای که به Credential پایدار نیاز داشته باشد خارج از این قرارداد V1 است و نمی‌تواند با فرض وجود Credential Storage در Backend طراحی شود.

## ۱۲. قوانین امنیتی

Backend باید:

- Credentialها را فقط از مسیر امن دریافت کند؛
- آن‌ها را در Log، Analytics، Crash Report یا Audit Metadata ثبت نکند؛
- آن‌ها را در Error/Response برنگرداند؛
- آن‌ها را به Account یا Site دیگر افشا نکند؛
- فقط برای Customer Site مجاز استفاده کند؛
- از `site_id` برای اعمال Site Isolation استفاده کند؛
- پس از پایان Request، هیچ storage پایدار حاوی Customer Credential ایجاد نکند.

## ۱۳. اصل نهایی

```text
Site Identity
    = ریشه هویت و مالکیت Account

Account
    = متعلق به همان Site
    = قابل اتصال/دسترسی فقط برای همان Site

Email
    = Contact Metadata
    = نه Authentication Identity
    = نه Account Ownership Proof
    = نه Account Lookup Key

First Request
    → Registration + Site Connection + Verification

Successful WordPress/WooCommerce Verification
    → اثبات کنترل Credential-based روی همان Site
    → بدون تأیید دستی دوم

WooGit Session
    → احراز و مجوز Client در Backend

Customer Credentials
    → احراز Backend نزد Customer WordPress/WooCommerce
    → Request-scoped only
    → Never persisted by Backend

WooCommerce Settings
    → متعلق به Customer Site

Backend
    → Account / Site / Subscription / Entitlement
    → Lightweight Proxy
    → Site Isolation اجباری
    → حداقل تغییر در Request/Response
```

`WooGit Gateway Plugin` یک Plugin جداگانه روی سایت مشتری است و در Scope فعلی Backend توسعه داده نمی‌شود.
