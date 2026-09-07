# WooGit Theme Specification

> وضعیت: V1 — Design/Implementation Contract
>
> این سند فقط مربوط به `theme/woogit/` است و مرز Theme با WooGit Main Plugin و WooCommerce را تعریف می‌کند.

## 1. هدف

`WooGit Theme` رابط وب عمومی و Billing/Account وب‌سایت اصلی WooGit است. Theme یک Presentation Layer است و نباید منطق امنیتی، احراز هویت، Authorization، Site Ownership، Entitlement، Session lifecycle یا Customer Credential storage را مالک شود.

ساختار Repository:

```text
backend-site/
├── plugin/woogit-backend/   # Backend / domain / API / security
├── theme/woogit/            # Website presentation
├── docs/
└── .github/
```

Theme و Plugin باید کاملاً از نظر فایل، مسئولیت و lifecycle جدا باشند. Theme نباید فایل PHP، class، service، migration، database code یا business logic مربوط به Plugin را کپی کند.

## 2. اصل معماری

```text
Browser
  ↓
WooGit Theme
  ↓
WooGit Main Plugin / REST API
  ├─ Account
  ├─ Site Identity
  ├─ Authentication
  ├─ Session
  ├─ Ownership
  ├─ Trial / Subscription
  ├─ Entitlement
  ├─ Billing
  ├─ Security
  └─ Authorization
       ↓
WooCommerce / Customer Site
```

Theme فقط UI، navigation، rendering و interaction را ارائه می‌کند. تصمیم‌های authoritative از Backend دریافت می‌شوند.

## 3. صفحات اصلی

Theme باید برای این صفحات طراحی شود:

- Home / Landing
- Pricing
- Login
- Register / Connect Store
- Site Verification state
- Dashboard
- Account
- Subscription
- Billing / Payment History
- Checkout handoff
- Connected Sites
- Usage
- Documentation
- Status
- Privacy
- Terms

صفحات می‌توانند در صورت نیاز با WordPress routing/template hierarchy پیاده‌سازی شوند، اما قرارداد API و منطق Backend نباید داخل Theme تکرار شود.

## 4. Login

مدل ورود V1 با Account معمولی Email/Password جایگزین نمی‌شود. Login بر اساس Site identity و credential قراردادی Backend طراحی می‌شود:

```text
Site URL
Password
```

Theme باید این دو ورودی را دریافت و به endpoint قراردادی Backend ارسال کند. Theme نباید Password را ذخیره کند یا خودش اعتبار آن را تعیین کند.

UI پیشنهادی:

```text
Welcome back

Site URL
[ https://example.com ]

Password
[ ******** ]

[ Sign in ]

Forgot password?
```

رفتار موفق/ناموفق کاملاً بر اساس response قرارداد Backend است.

## 5. Register / Connect Store

ثبت‌نام اولیه هم‌زمان با Bootstrap/Verification سایت انجام می‌شود. فرم شامل این اطلاعات است:

- Store URL
- WordPress Username
- WordPress Application Password
- WooCommerce Consumer Key
- WooCommerce Consumer Secret

```text
Connect your store

Store URL
[ https://example.com ]

WordPress Username
[ ... ]

WordPress Application Password
[ ... ]

WooCommerce Consumer Key
[ ... ]

WooCommerce Consumer Secret
[ ... ]

[ Verify & Create Account ]
```

این اطلاعات request-scoped هستند. Theme، مرورگر و Backend نباید آن‌ها را به عنوان اطلاعات دائمی Theme ذخیره کنند. Backend در V1 Customer Credential را در DB، Vault، persistent cache، Log، Telemetry، Audit یا Response نگه نمی‌دارد.

## 6. Verification UX

پس از Submit، Theme فقط وضعیت مراحل را نمایش می‌دهد و نتیجه را از Backend می‌گیرد:

```text
Network / HTTPS
      ↓
WordPress reachability / authentication
      ↓
WordPress identity/access
      ↓
WooCommerce availability / authentication
      ↓
Site Identity
      ↓
Account / Trial lifecycle
      ↓
WooGit Session
```

Verification باید read-only باشد و برای تست اتصال نباید Product/Order/Media mutation انجام دهد.

موفقیت Verification به معنی اثبات کنترل معتبر فنی سایت است، نه ادعای مالکیت حقوقی دامنه.

## 7. Session و دسترسی

Theme نباید Session را مرجع Authorization بداند. Backend تنها مرجع معتبر است.

V1 دو Scope دارد:

- `billing`: ورود به Account و Billing؛
- `operational`: قابلیت‌های Customer-site / Commerce.

Session منقضی‌شده معتبر نیست و locally revive نمی‌شود. Automatic re-login باید Session Creation جدید باشد و Backend دوباره Account + Site Ownership + Entitlement را بررسی کند.

اگر Trial/Subscription منقضی باشد، کاربر همچنان می‌تواند وارد Billing شود، اما Theme نباید UI را طوری نمایش دهد که `/forward` یا قابلیت عملیاتی بدون Entitlement مجاز است.

## 8. Pricing

Pricing باید داده‌محور باشد. قیمت، مدت و Currency نباید hard-code شوند.

منبع قیمت و مدت، Billing API/ WooCommerce Subscription products است. Theme باید response را render کند و امکان نمایش:

- Plan name
- Price
- Currency
- Duration
- Features
- Site limits
- AI credits در صورت وجود
- Current plan
- Upgrade / renew CTA

را داشته باشد.

## 9. Billing

Billing روی WordPress اصلی WooGit و WooCommerce/WooCommerce Subscriptions انجام می‌شود.

Flow:

```text
Pricing
  ↓
Select Plan
  ↓
Backend Billing Checkout
  ↓
WooCommerce Order
  ↓
Payment Gateway
  ↓
Server-side payment/subscription event
  ↓
Entitlement
  ↓
New operational session when eligible
```

Theme هرگز موفقیت پرداخت را از redirect یا callback کلاینت نتیجه‌گیری نمی‌کند. وضعیت واقعی از Backend خوانده می‌شود.

Endpoints قراردادی V1:

```text
GET  /api/v1/billing/plans
GET  /api/v1/billing/status
POST /api/v1/billing/checkout
POST /api/v1/billing/activate-session
```

`checkout` باید با Session معتبر انجام شود و Account/Site از Session گرفته می‌شود؛ Theme نباید بتواند Account/Site دلخواه را به عنوان authority تعیین کند.

## 10. Checkout UI

Theme می‌تواند صفحه انتخاب/تأیید پلن را نمایش دهد، اما پرداخت واقعی توسط WooCommerce انجام می‌شود.

نمونه:

```text
Your plan
Pro

Duration     30 days
Price        <server value>
Currency     <server value>

[ Continue to payment ]
```

Backend پس از ساخت Order، `payment_url` را برمی‌گرداند و Theme کاربر را به صفحه پرداخت هدایت می‌کند.

## 11. Subscription Dashboard

Dashboard باید وضعیت authoritative را نمایش دهد:

- Trial / Active / Expired / Cancelled
- Current plan
- Start date
- Expiration / renewal
- Payment history
- Upgrade / renewal actions
- Billing status
- Entitlement/access state

Theme نباید با تغییر local state وضعیت Premium/Active را جعل کند.

## 12. Account Dashboard

ساختار پیشنهادی:

```text
Dashboard
├── Overview
├── Subscription
├── Connected Stores
├── Usage
├── Billing
├── Sessions / Security
└── Account
```

در Overview کارت‌های اصلی می‌توانند شامل Current Plan، Access Status، Expiration، Connected Site و Usage باشند.

## 13. Connected Sites

Theme فقط Siteهای متعلق به Account را نمایش می‌دهد که Backend در response مجاز اعلام کرده است. Theme نباید ownership را از URL یا اطلاعات فرم حدس بزند.

Customer Credential نباید در لیست Site، dashboard، HTML source، browser storage یا UI نمایش داده شود.

## 14. Security Boundary

Theme نباید:

- Customer Credential را persistent ذخیره کند؛
- Session را locally معتبر اعلام کند؛
- Entitlement را locally محاسبه کند؛
- Payment success را از client callback قبول کند؛
- URL مقصد دلخواه برای gateway بسازد؛
- Customer WooCommerce را مستقیماً از Browser فراخوانی کند؛
- Secret را در HTML، JavaScript bundle، analytics یا logs قرار دهد؛
- business logic Plugin را duplicate کند.

Theme می‌تواند public content و داده‌های دریافتی از API را render کند، اما authorization همیشه server-side است.

## 15. Separation of Theme and Plugin

قانون اصلی:

```text
THEME
  = Presentation
  = Templates
  = CSS
  = UI JavaScript
  = Navigation
  = Accessibility

PLUGIN
  = REST API
  = Domain logic
  = Auth
  = Sessions
  = Accounts
  = Sites
  = Billing
  = Entitlements
  = Security
  = Database
  = Admin
```

Theme نباید به فایل‌های داخلی Plugin با include/require وابسته شود. Integration فقط از قراردادهای public و پایدار WordPress/REST API انجام شود.

فعال/غیرفعال شدن Theme نباید داده‌های Backend را حذف یا تغییر دهد. فعال/غیرفعال شدن Plugin نیز نباید Theme را به کد داخلی آن وابسته کند؛ در نبود Backend response مناسب، Theme باید graceful error state داشته باشد.

## 16. Performance

Theme باید lightweight باشد:

- بدون page builder اجباری؛
- بدون dependency سنگین غیرضروری؛
- assetهای CSS/JS فقط در صفحات لازم؛
- تصاویر بهینه؛
- lazy loading در موارد مناسب؛
- semantic HTML؛
- responsive؛
- accessibility؛
- حداقل third-party scripts.

## 17. Design System

ظاهر Theme باید یک SaaS/Infrastructure product حرفه‌ای باشد، نه قالب عمومی WordPress.

اصول:

- Liquid/glass-inspired UI به شکل کنترل‌شده؛
- typography خوانا؛
- کارت‌های تمیز؛
- border و shadow ظریف؛
- spacing سیستماتیک؛
- responsive کامل؛
- dark/light در صورت نیاز؛
- وضعیت‌ها با visual hierarchy واضح؛
- loading، empty، success و error state برای interactionهای API.

Landing، Pricing، Auth، Billing و Dashboard باید از یک Design System مشترک استفاده کنند.

## 18. Error / Loading States

Theme برای endpointهای Backend باید stateهای زیر را داشته باشد:

```text
idle
loading
success
validation_error
auth_error
verification_error
rate_limited
payment_pending
payment_failed
session_expired
server_error
network_error
```

متن UI باید machine-readable error codeهای Backend را به پیام قابل‌فهم تبدیل کند، بدون نمایش Secret، SQL، stack trace یا اطلاعات داخلی.

برای `429` باید پیام مناسب و در صورت وجود `Retry-After` رفتار retry UI رعایت شود.

## 19. Session Expiration UX

اگر Backend Session را منقضی اعلام کرد:

```text
Session expired
   ↓
Clear local transient session state
   ↓
Ask for required Login information
   ↓
New Session Creation
   ↓
Backend re-checks Account + Site Ownership + Entitlement
```

Theme نباید Session منقضی‌شده را locally تمدید یا معتبر نگه دارد.

## 20. Documentation / Status

Documentation باید برای کاربران و توسعه‌دهندگان قابل دسترسی باشد و شامل Getting Started، Authentication، Site Connection، Billing، Sessions، Errors، Security و Rate Limits باشد.

Status page باید وضعیت سرویس‌های مهم را بدون افشای اطلاعات داخلی نمایش دهد.

## 21. Compatibility

Theme باید با WordPress استاندارد و stack اصلی WooGit سازگار باشد و از APIهای عمومی WordPress و قراردادهای مستند Backend استفاده کند.

Theme نباید فرض کند Customer Gateway Plugin در این Repository نصب است؛ Gateway Plugin مشتری خارج از Scope این Repository است.

## 22. Testing Requirements

Theme قبل از release باید حداقل این مسیرها را تست کند:

- Home rendering؛
- Pricing data rendering؛
- Login success/failure؛
- Register/Connect؛
- Verification states؛
- Expired session؛
- Billing status؛
- Checkout handoff؛
- Payment pending/failed/success reflected from server؛
- Expired entitlement؛
- Responsive layouts؛
- Keyboard/accessibility basics؛
- No credential persistence؛
- No secrets in generated HTML/JS/logs؛
- Theme activation/deactivation isolation from Plugin.

## 23. V1 Non-Goals

این موارد در Theme V1 نباید به منطق مستقل تبدیل شوند:

- پیاده‌سازی Auth مستقل؛
- Token/Refresh-token system جدید؛
- Credential Vault؛
- Customer WooCommerce business API؛
- Payment processor مستقل؛
- Subscription database مستقل؛
- Entitlement database مستقل؛
- Proxy/gateway مستقیم از Browser؛
- وابستگی اجباری به Gateway Plugin مشتری.

## 24. Definition of Done

Theme زمانی V1-ready است که:

1. تمام صفحات اصلی تعریف‌شده را داشته باشد؛
2. Login با Site URL + Password را طبق قرارداد Backend پیاده کند؛
3. Register/Connect با Site URL + چهار Customer Credential را طبق Bootstrap contract پیاده کند؛
4. هیچ Customer Credential را persistent نگه ندارد؛
5. Pricing/Billing را از Backend/WooCommerce data بگیرد؛
6. Checkout را از طریق Backend به WooCommerce payment flow بسپارد؛
7. Subscription/Entitlement را authoritative از Backend نمایش دهد؛
8. Session expiration را امن مدیریت کند؛
9. Theme و Plugin از نظر فایل و مسئولیت مستقل باشند؛
10. با غیرفعال شدن Theme، داده Backend آسیب نبیند؛
11. با غیرفعال شدن Plugin، Theme graceful failure داشته باشد؛
12. responsive، accessible و lightweight باشد؛
13. تست‌های UI و integration موردنیاز را پاس کند؛
14. هیچ business/security logic از Plugin را duplicate نکند.

## 25. مرجع معماری

این سند باید همراه با قراردادهای زیر خوانده شود:

- `docs/API-CONTRACT.md`
- `docs/CLIENT_CONTRACT.md`
- `docs/IDENTITY_AND_WP_CONNECTION.md`
- `docs/ONBOARDING_AND_REGISTRATION.md`
- `docs/BILLING.md`
- `docs/WORDPRESS_MONOREPO.md`
- `docs/SECURITY.md`

در صورت تعارض، قراردادهای Backend و Security مرجع رفتار server-side هستند و Theme باید خود را با آن‌ها تطبیق دهد.
