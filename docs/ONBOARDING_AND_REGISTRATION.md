# ثبت‌نام و اتصال اولیه WooGit

> وضعیت: **V1 — Locked**

## ۱. اصل معماری

در V1، اولین درخواست اتصال اپ می‌تواند هم‌زمان **ثبت‌نام WooGit، اتصال Customer Site و Verification اولیه** باشد.

هیچ مرحله جداگانه‌ای برای تأیید دستی صاحب سایت بعد از اتصال موفق لازم نیست.

دلیل این است که اپ برای اتصال Customer Site، Credentialهای لازم را ارائه می‌کند و Backend با آن‌ها احراز هویت واقعی WordPress و WooCommerce را انجام می‌دهد. موفقیت این Verification نشان می‌دهد درخواست‌کننده Credential معتبر و دسترسی لازم به همان سایت را در اختیار دارد.

## ۲. Credentialهای اولین درخواست

اپ در اولین اتصال این اطلاعات را ارسال می‌کند:

- Store URL
- WordPress Username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

در این مرحله ممکن است هنوز WooGit Session وجود نداشته باشد؛ بنابراین این درخواست یک Bootstrap/Onboarding request است و نباید به Session عادی وابسته باشد.

## ۳. Flow قطعی V1

```text
Android
  │
  │ First Request + Customer Credentials
  ▼
WooGit Backend / Main Plugin
  │
  ├─ WordPress reachability
  ├─ WordPress authentication
  ├─ WordPress identity/access verification
  ├─ WooCommerce availability/authentication
  │
  ├─ Resolve existing Site Identity
  │       OR
  ├─ Create new Site Identity
  │
  ├─ Resolve existing Account
  │       OR
  ├─ Create Account
  │
  ├─ Check Trial eligibility
  │
  └─ Create / activate WooGit Session
          │
          ▼
      Normal Requests
```

## ۴. اثبات کنترل سایت

در این مدل، `successful verification` مبنای ثبت و فعال‌سازی Site است.

اگر Backend بتواند با Credentialهای ارائه‌شده:

1. به WordPress متصل شود؛
2. هویت/دسترسی WordPress را تأیید کند؛
3. به WooCommerce متصل شود؛
4. Site Identity را با موفقیت resolve یا ایجاد کند؛

نیازی به ارسال درخواست تأیید جداگانه به صاحب سایت نیست.

> صرف داشتن URL سایت یا ایمیل، اثبات مالکیت/کنترل سایت محسوب نمی‌شود؛ Credential verification موفق مبنای این Flow است.

## ۵. وضعیت Site

اگر Verification اولیه با موفقیت کامل شود، Site برای استفاده WooGit به وضعیت `verified/active` می‌رسد و `pending_verification` برای این مسیر اجباری نیست.

در صورت شکست هر مرحله، Account/Site نباید به‌عنوان اتصال فعال ثبت شود و Backend نباید عملیات تجاری محافظت‌شده را به Customer Site Forward کند.

## ۶. Account موجود و جدید

### Account موجود

```text
Credentials
 → Verification
 → Resolve Site Identity
 → Resolve owning Account
 → Trial/Subscription/Entitlement checks
 → Session
```

Site جدید نباید به Account دیگری متصل شود.

### Account جدید

```text
Credentials
 → Verification
 → New Site Identity
 → Account creation
 → Trial eligibility
 → Session
```

Trial اولیه طبق سیاست V1 مدت ۱۵ روز دارد و به Site Identity/دامنه مربوط می‌شود.

## ۷. تأیید دستی جداگانه ممنوع برای این Flow

V1 نباید پس از Verification موفق یک مرحله اجباری مانند موارد زیر اضافه کند:

```text
Verification موفق
 → Send approval email
 → Wait for owner approval
 → Activate Site
```

این مرحله برای مدل فعلی redundant است و نباید بخشی از Onboarding پایه باشد.

## ۸. امنیت

- Credentialها فقط برای Verification/اتصال مقصد مصرف می‌شوند.
- Credentialها در Log، Error Response، Analytics، Crash Report یا Audit Metadata ثبت نمی‌شوند.
- Verification باید تا حد امکان read-only باشد.
- صرفاً برای تست اتصال نباید Product/Order/Media mutation انجام شود.
- پس از Onboarding، Session برای احراز Client در WooGit استفاده می‌شود و Customer Credentials همچنان اعتبار مقصد هستند.

## ۹. مرز با Gateway Plugin

این Flow به `WooGit Gateway Plugin` مشتری وابسته نیست. `WooGit Main Plugin` می‌تواند Verification و ثبت Site را با WordPress/WooCommerce موجود انجام دهد.

`WooGit Gateway Plugin` همچنان خارج از Scope پیاده‌سازی V1 این repository است.
