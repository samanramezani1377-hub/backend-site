# مشخصات Theme WooGit

> وضعیت: V1 — Documentation Before Implementation
>
> این فایل **نقطه ورود Agent** و خلاصه کامل قرارداد Theme است. جزئیات اجرایی در اسناد مرجع پایین نگهداری می‌شوند.
>
> **مهم:** `theme/woogit/` در زمان این سند هنوز وارد implementation نشده است. هیچ کدی را بر اساس وجود فایل‌های آینده فرض نکنید.

## 1. Theme چیست؟

WooGit Theme دو نقش دارد:

1. وب‌سایت رسمی محصول: معرفی، قابلیت‌ها، روش کار، قیمت، FAQ، مستندات عمومی، پشتیبانی و صفحات حقوقی.
2. Customer Portal: حساب، اشتراک، Billing، پرداخت‌ها، سایت متصل و امنیت حساب.

Theme **Web App عملیاتی WooGit نیست**.

```text
Android App  → Products / Orders / Sync / Conflicts / Inventory / Store Operations
Theme       → Public Website + Customer Portal + Subscription/Billing UI
Main Plugin → Public REST API + Auth + Account + Site + Ownership + Entitlement + Billing + Security + Business Logic
```

### مرز قطعی

Theme نباید این موارد را پیاده‌سازی کند:

- Products، Orders و Order Detail عملیاتی؛
- Sync، Conflict Resolution و Inventory؛
- Media operations و Forwarding عملیاتی؛
- Store Dashboard عملیاتی؛
- اتصال مستقیم به WooCommerce مشتری؛
- تصمیم‌گیری Authorization یا Entitlement؛
- کپی/استفاده مستقیم از کلاس‌ها، سرویس‌ها، Migration، Database code یا Business Logic داخلی Plugin.

Backend تنها مرجع authoritative برای Account، Site، Ownership، Authentication، Session، Subscription، Entitlement، Billing و Authorization است.

## 2. معماری و اسناد مرجع

```text
Browser
  ↓
WooGit Theme
  ↓
Public WooGit REST API
  ↓
WooGit Main Plugin / Backend
```

Theme فقط Presentation، interaction و orchestration لازم برای نمایش داده Backend را انجام می‌دهد.

**اسناد authoritative:**

| سند | مرجع برای |
|---|---|
| `THEME.md` | جایگاه، مرزها و نقشه کلی Theme |
| `THEME_ARCHITECTURE_CONTRACT.md` | ساختار اجرایی و مرز فایل‌ها؛ Freeze معماری |
| `THEME_UX_FLOW.md` | UX Flow، State و Transition |
| `THEME_API_CONTRACT.md` | REST API و قرارداد Client/Server |
| `THEME_AUTH_FLOW.md` | Web Auth و Session |
| `theme/DESIGN-SYSTEM.md` | Design System و Liquid Glass |
| `theme/PAGES.md` | Page Inventory و ساختار صفحات |
| `theme/PORTAL.md` | Customer Portal IA |
| `theme/RESPONSIVE.md` | Responsive و RTL/LTR |
| `theme/PERFORMANCE.md` | Performance |
| `theme/ACCESSIBILITY.md` | Accessibility |
| `THEME_TESTING.md` | تست و CI |
| `THEME_MANAGEMENT.md` + `theme-management/*` | مدیریت محتوای Theme |

اگر بین خلاصه این فایل و سند تخصصی اختلافی دیده شد، **سند تخصصی مربوط به همان حوزه مرجع است**؛ تناقض واقعی باید قبل از implementation رفع شود.

## 3. صفحات و Information Architecture

### Public Website

```text
Home
├── Hero
├── Why WooGit
├── Features
├── How It Works
├── Pricing
├── FAQ
├── CTA
└── Footer
```

صفحات عمومی تکمیلی: Features، Pricing، Documentation، Support، Service Status، Privacy، Terms.

Header عمومی:

```text
WooGit
محصول | قابلیت‌ها | نحوه کار | قیمت | مستندات | پشتیبانی
[ورود] [شروع کنید]
```

Navigation موبایل فشرده و accessible است، بدون تغییر semantics یا مقصد.

### Auth

- Login
- Register / Web Bootstrap / اتصال اولیه
- وضعیت اعتبارسنجی اتصال در صورت نیاز
- Logout

### Customer Portal

```text
Customer Portal
├── Overview
├── Subscription
├── Billing
├── Payments
├── Connected Site
└── Account / Security
```

Portal navigation و Public navigation جدا هستند؛ Design System مشترک دارند. هیچ صفحه عملیاتی فروشگاه در Theme وجود ندارد.

## 4. Authentication و Site Onboarding

### Login V1

ورود با **Site URL + Web Password** است، نه Email/Password عمومی.

Theme اعتبار رمز، Account یا Ownership را تعیین نمی‌کند؛ Backend این موارد را resolve/verify کرده و Web Session صادر می‌کند.

پیام Login باید عمومی و غیرحساس باشد و وجود/وضعیت Account یا Site را بیش از قرارداد افشا نکند.

### Web-first Registration

ثبت‌نام مستقیم وب باید از قرارداد اختصاصی زیر استفاده کند:

```text
POST /account/web-bootstrap
```

ورودی مفهومی:

```text
site_url
wp_username
wp_application_password
consumer_key
consumer_secret
web_password
web_password_confirmation
```

جریان authoritative:

```text
Validate input
  ↓
Rate Limit
  ↓
Verify real WooCommerce site (Read-only)
  ↓
Resolve/Create Account + Site
  ↓
Verify Site ↔ Account ownership
  ↓
Create Web Credential
  ↓
Issue Web Session
```

این mutation باید `Idempotency-Key` داشته باشد.

Credentialهای WordPress/WooCommerce **فقط request-scoped** هستند و هرگز نباید در DB/options، session یا cache پایدار، cookie/browser storage، log، telemetry، audit، HTML یا JavaScript bundle ذخیره شوند.

`/sites/verify` قرارداد App/bootstrap است مگر Backend صراحتاً آن را برای Web منتشر کند؛ Theme نباید با جعل header/payload آن را Web API کند.

Verification فقط اثبات **کنترل فنی معتبر در این جریان** است، نه مالکیت حقوقی دامنه، و نباید برای تست Product/Order/Media mutation انجام دهد.

## 5. Session و Security

دو Session کاملاً جدا هستند:

```text
Android App → X-WooGit-Session
Theme       → X-WooGit-Web-Session
```

Theme نباید App Session را برای Web reuse یا جعل کند. Session منقضی‌شده locally revive نمی‌شود؛ Login مجدد باید Session جدید بسازد و Backend دوباره Account + Site Ownership + Entitlement لازم را بررسی کند.

Token Web نباید در URL باشد. گزینه ترجیحی browser session، Cookie امن با `HttpOnly` + `Secure` + `SameSite` مناسب یا BFF/Bridge امن است. اگر Backend فقط Header پشتیبانی کند، ذخیره raw token در `localStorage` بدون تصمیم امنیتی صریح مجاز نیست.

پس از Password Change، Backend همه Web Sessionهای قبلی را revoke می‌کند و Theme باید local state را پاک کرده و Login مجدد بخواهد. Logout نیز باید revoke سمت Backend و پاک‌سازی state موقت را انجام دهد.

`account_id` و `site_id` ارسالی کاربر هرگز مرجع Authorization نیستند؛ Context معتبر Backend مرجع است.

## 6. Pricing، Subscription و Billing

Pricing **data-driven** است و price، currency یا duration در Theme hard-code نمی‌شوند. در صورت ارائه توسط Backend، Theme نام پلن، قیمت، Currency، مدت، features، site limit، AI credit، پلن فعلی و actionهای Upgrade/Renew را نمایش می‌دهد.

Subscription باید state authoritative را نمایش دهد، از جمله:

- Trial / Active / Expired / Cancelled؛
- plan و dates؛
- Billing status؛
- Entitlement status؛
- payment history؛
- actionهای مجاز Upgrade/Renew.

Theme با local state نباید Premium/Active را جعل کند.

### Checkout

```text
Pricing / Subscription
  ↓
Backend Billing Checkout
  ↓
WooCommerce Order
  ↓
Payment Gateway
  ↓
Server payment/subscription event
  ↓
BillingService / Entitlement
```

Checkout با `Idempotency-Key` انجام می‌شود و retry همان logical operation باید همان key را حفظ کند.

**Timeout-after-success:** timeout یا قطع شبکه به معنی شکست نیست. نتیجه Unknown باید با همان operation/idempotency context از Backend re-query شود؛ ایجاد Checkout دوم با key جدید برای همان عملیات ممنوع است مگر کاربر صریحاً عملیات جدیدی آغاز کند.

### Payment Return

بازگشت از Gateway proof of payment نیست. Theme باید `/payment/result` را به‌صورت «در حال بررسی» نمایش دهد و وضعیت را دوباره از Backend بگیرد، مثلاً با `GET /billing/status` و Web Session. queryهایی مانند `success=1` trusted نیستند.

وضعیت‌های Pending / Failed / Success باید جدا نمایش داده شوند. Pending می‌تواند با polling محدود و rate-limit-aware دوباره بررسی شود؛ Failure مسیر امن retry/plan selection دارد.

`/billing/activate-session` برای صدور Operational App Session **App-only** است و Theme نباید آن را مصرف کند.

## 7. API Contract خلاصه

Base API:

```text
/wp-json/woogit/v1/
```

Web routes فعلی:

```text
GET  /account/requirements
POST /account/web-bootstrap
POST /web/login
POST /web/logout
GET  /web/me
POST /web/account/contact-email
POST /web/account/password
GET  /web/billing/history
```

Billing:

```text
GET  /billing/plans
GET  /billing/status
POST /billing/checkout
POST /billing/activate-session   # App-only
```

`plans` عمومی است. `status` و `checkout` برای Theme باید در قرارداد نهایی Web Session را بپذیرند. Backend باید App/Web Session را به authorization context مشترکی مانند `account_id`, `site_id`, `client_type`, `session_id` نگاشت کند، بدون تکرار Billing Business Logic.

Client version از API version جداست. Theme نباید `X-WooGit-App-Version` را جعل کند؛ الگوی عمومی پیشنهادی:

```text
X-WooGit-Client: web
X-WooGit-Client-Version: 1.0.0
```

Errorها بر اساس HTTP status + canonical `code` مصرف می‌شوند، نه متن. کدهای کلیدی شامل `validation_error`, `invalid_web_credentials`, `invalid_web_session`, `invalid_session`, `account_inactive`, `site_not_owned`, `not_entitled`, `idempotency_conflict`, `operation_pending`, `operation_unknown`, `rate_limited`, `server_error` هستند. برای 429، در صورت وجود `Retry-After`/`retry_after` رفتار کنترل‌شده و بدون retry تهاجمی لازم است.

قرارداد کامل Error در `docs/API_ERROR_CODES.md` است؛ اگر این فایل در شاخه فعلی وجود نداشت، نبود آن یک gap قراردادی است و باید قبل از implementation نهایی شود.

## 8. UX State Contract

تمام interactionهای API-driven مرتبط باید در صورت نیاز این stateها را پوشش دهند:

```text
Idle → Loading → Success
                  ├→ Empty
                  └→ Pending
Loading/Action → Error
                 ├─ Recoverable
                 ├─ Authentication Required
                 ├─ Forbidden
                 ├─ Not Found
                 ├─ Conflict
                 ├─ Rate Limited
                 └─ Server Error
```

Rules:

- Success فقط با نتیجه موفق Backend؛
- Empty با Error یکی نیست؛
- Pending یعنی Backend هنوز نتیجه نهایی را قطعی نکرده؛
- Error باید قابل فهم، غیرحساس و دارای recovery مناسب باشد؛
- state فقط با رنگ منتقل نشود؛
- mutation در Loading نباید duplicate شود؛
- Refresh/local state مرجع business truth نیست؛
- 400/401/403/404/409/429/5xx طبق `THEME_UX_FLOW.md` رفتار می‌شوند.

Flowهای اجباری UX شامل Login، Web Bootstrap، Portal entry، Subscription، Billing/Checkout، timeout-after-success، Payment Return، Payment Pending/Failed/Success، Payment History، Connected Site، Password Change، Logout، session expiry، direct URL و refresh است.

## 9. Design، Responsive و Accessibility

زبان بصری **Liquid Glass** است، اما readability و performance اولویت بالاتری دارند. Design System token-driven و reusable است: typography، spacing، color، radius، shadow، motion، surface و componentهای پایه.

اصول مشترک:

- RTL-first و LTR صحیح برای URL/email/data فنی؛
- semantic HTML؛
- keyboard navigation و visible focus؛
- label و error واقعی؛
- contrast مناسب؛
- reduced motion؛
- touch target مناسب؛
- Loading/Skeleton، Empty، Error و Pending قابل فهم.

Responsive: mobile-first، بدون تغییر semantics/content architecture؛ mobile تک‌ستونه و nav فشرده، tablet حداکثر دو ستون در صورت حفظ خوانایی، desktop با max-width خوانا و Portal sidebar اختیاری. CSS logical properties برای RTL/LTR، responsive images، controlled font loading و asset splitting استفاده شود و breakpoint با JavaScript سنگین مدیریت نشود.

جزئیات Design/Responsive/Accessibility در اسناد تخصصی `docs/theme/` است.

## 10. Theme Management

Theme Management فقط **Website Presentation/Content** است:

- General: logo، alternate logo، favicon، site title، short description، brand info، default OG image؛
- Home: Hero، Features، How It Works و sectionهای قابل تنظیم؛
- Content: Pricing Presentation، FAQ و محتوای عمومی؛
- Trust/Footer: Footer، تماس، Social و eNAMAD؛
- Security: capability، nonce، sanitize، escape و عدم ذخیره secret.

Pricing Management فقط presentation است؛ price/currency/subscription/entitlement/checkout authority در Backend است.

برای eNAMAD مدل V1 شامل `enabled`, `namad_id`, `namad_code`, `image_media_id`, `verification_url` اختیاری، `alt_text`, `placement` و `optional_text` است. تصویر از WP Media Library و با media ID/reference نگهداری می‌شود؛ raw HTML/JS انتخاب اصلی V1 نیست و کلیک تصویر باید به verification رسمی eNAMAD برود.

Theme Management با Account، Auth، Session، Ownership، Entitlement، Billing، WooCommerce credentials یا Store Operations کاری ندارد.

## 11. Architecture Contract خلاصه

ساختار V1 فریز است؛ اگر نیاز جدیدی خارج از آن بود، **اول مستندات/تصمیم معماری و بعد کد**.

```text
theme/woogit/
├── assets/
│   ├── css/{foundation.css,components.css,pages.css,responsive.css}
│   ├── js/{core.js,navigation.js,auth.js,portal.js}
│   └── images/
├── inc/
│   ├── setup/
│   ├── admin/theme-management/
│   ├── api/
│   ├── auth/
│   ├── portal/
│   └── helpers/
├── templates/{public,auth,portal}/
├── template-parts/{header,footer,hero,features,pricing,faq,portal}/
├── functions.php
├── style.css
└── ...
```

مرزها:

- `inc/api/` تنها مرز REST API عمومی؛ request/transport/headers/response/error mapping؛ بدون DB یا Business Logic.
- `inc/auth/` Web Auth و session orchestration؛ بدون تصمیم Account/Ownership/Entitlement.
- `inc/portal/` آماده‌سازی authoritative data برای Portal؛ بدون تکرار Business Logic.
- `inc/admin/theme-management/` فقط content/presentation settings.
- `inc/setup/` bootstrap استاندارد WordPress.
- `inc/helpers/` helper عمومی، نه محل پنهان Business Logic.
- `templates/` صفحه کامل؛ بدون HTTP/DB/WooCommerce مستقیم.
- `template-parts/` reusable presentation؛ بدون API call/DB query مستقیم.
- `functions.php` bootstrap نازک؛ God File ممنوع.
- `style.css` metadata استاندارد WordPress؛ Design System اصلی در `assets/css/`.
- JS برای interaction/progressive enhancement است، نه SPA مگر قرارداد جداگانه.
- assetها تا حد امکان page/feature scoped باشند.

Dependency مجاز:

```text
Bootstrap → inc/* → View Data → templates → template-parts
```

ممنوع: template-part→API، template→HTTP، JS→PHP internals، Theme→Plugin internals، Theme→Backend DB، Theme→Customer WooCommerce API.

## 12. Testing و Definition of Done

Theme testها از Plugin جدا و در `tests/theme/` هستند. حداقل پوشش:

- Unit: data mapping، form validation، error mapping، state handling، Idempotency Key، session cleanup؛
- Integration: Login، Logout/revoke، Account، Billing/History، Password Change، Checkout، Payment Return/status re-check، expired session و 401/403/404/409/429/5xx؛
- Security: عدم باقی‌ماندن credential در DOM/HTML/log/storage، عدم token در URL، عدم جعل Account/Site/Entitlement، redirect امن، XSS/CSRF مطابق session model، عدم افشای internals؛
- Visual: mobile/tablet/desktop و Liquid Glass بدون افت readability/performance؛
- Accessibility: keyboard، screen reader labels، focus، contrast، reduced motion، zoom.

CI باید مستقل از Plugin و حداقل شامل lint، syntax، unit/integration، security و build قابل نصب Theme باشد. اگر تستی fail شد، CI در نهایت قرمز است اما تست‌های مستقل باید تا حد امکان ادامه یابند تا خطاها یکجا گزارش شوند.

تست‌ها نباید Billing/Auth/Entitlement success را fake کنند مگر Unit Testی که هدفش صرفاً UI mapping است؛ مسیرهای قراردادی باید با Backend واقعی تست شوند.

## 13. Pre-Implementation Gate

قبل از ایجاد کد Theme، این موارد باید قطعی باشند:

1. `POST /account/web-bootstrap` و Web-first registration؛
2. پشتیبانی Web Session برای Billing `status` و `checkout`؛
3. App-only بودن `activate-session`؛
4. Payment Return و canonical Billing/Payment status؛
5. canonical Error Registry و یکسان‌سازی codeها؛
6. Web client versioning؛
7. Integration tests برای Web contract؛
8. timeout-after-success، expired Web Session و idempotency tests.

**اصل نهایی:** اگر Backend contract چیزی را مشخص نکرده است، Theme حدس نمی‌زند و workaround امنیتی/Business Logic مستقل نمی‌سازد؛ ابتدا قرارداد اصلاح می‌شود، سپس implementation انجام می‌شود.
