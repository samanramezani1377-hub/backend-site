# قرارداد UX Flow تم WooGit

> وضعیت: V1 — قرارداد UX پیش از پیاده‌سازی
>
> این سند مرجع جریان کاربر، انتقال بین وضعیت‌ها و رفتار تجربه کاربری Theme است. این سند مکمل `THEME.md`، `THEME_API_CONTRACT.md`، `THEME_AUTH_FLOW.md` و مستندات `docs/theme/` است و نباید مسئولیت‌های Backend یا App را دوباره پیاده‌سازی کند.

## 1. هدف و اصل مرجع

UX باید قبل از UI مشخص باشد. Backend مرجع Account، Site Ownership، Authentication، Session، Subscription، Billing و Entitlement است. Theme نسخه وب App نیست و هیچ Flow عملیاتی برای Products، Orders، Sync، Conflicts، Inventory یا Store Dashboard ندارد.

## 2. نقشه اصلی تجربه کاربر

```text
Visitor → Landing / Public Website
        → Pricing / Features / How It Works / FAQ
        → Login / Register
        → Web Authentication / Bootstrap
        → Web Session
        → Customer Portal
             ├── Overview
             ├── Subscription
             ├── Billing
             ├── Payments
             ├── Connected Site
             └── Account / Security
```

Public navigation و Portal navigation جدا هستند، ولی Design System مشترک دارند.

## 3. High-Fidelity Prototype Contract

Prototype مستقیماً به‌صورت **High-Fidelity UI** طراحی می‌شود؛ Wireframe جداگانه مرحله اجباری نیست. هدف، مشخص کردن ساختار بصری، hierarchy، component usage، navigation و responsive intent پیش از PHP/CSS/JS است.

### 3.1 Global Visual Direction

- حس کلی: Modern + Technological.
- Liquid Glass ملایم؛ Glass نباید کل UI را بپوشاند.
- رنگ غالب: Purple/Violet نرم و کنترل‌شده.
- Background: Gradient بسیار ظریف و زنده.
- Header: Glass + Sticky.
- Desktop: Logo چپ + Navigation وسط + Login/Primary CTA راست.
- Mobile: Hamburger + Menu Sheet؛ Bottom Navigation وجود ندارد.
- Hero: مینیمال و typography-focused.
- Animation: Subtle + Professional.
- Border radius: Medium.
- Dark Mode: از V1.
- Raycast فقط reference برای کیفیت، نظم و restraint است و نباید کپی شود.

### 3.2 Page-specific Glass Intensity

| صفحه | شدت Glass |
|---|---|
| Home | محسوس‌تر، اما کنترل‌شده |
| Pricing | Glass Cards |
| Login | محدود و بسیار تمیز |
| Register | محدود و بسیار تمیز |
| Portal Overview | ملایم‌تر و information-first |
| Subscription | ملایم و کاربردی |
| Billing | بسیار کنترل‌شده |
| Payments | بسیار کنترل‌شده |
| Connected Site | ملایم و status-focused |
| Account/Security | ملایم و کاربردی |

Portal و صفحات داخلی نسبت به Landing تزئینات کمتری دارند و خوانایی/تراکم اطلاعات اولویت بالاتری دارد.

## 4. صفحه‌های اصلی Prototype

### 4.1 Home

```text
Header
  ↓
Minimal Hero
  ├── Typography-focused value proposition
  ├── Primary CTA: «دریافت WooGit»
  └── WooGit App Preview
        ├── Products preview
        └── Orders preview
  ↓
Features
  ↓
How It Works
  ↓
Pricing
  ↓
Why WooGit / Benefits
  ↓
FAQ
  ↓
Final CTA
  ↓
Full Footer
```

App Preview باید واقعی و قابل‌فهم به نظر برسد، اما Hero نباید شلوغ شود؛ value proposition و CTA اولویت بصری دارند.

Footer شامل Logo، short description، links، contact، social، Privacy، Terms، Documentation، Support، eNAMAD و Copyright است.

### 4.2 Pricing

Glass Cards؛ Plan اصلی برجسته؛ Free Trial با برجستگی ترکیبی و واضح؛ Planهای احتمالی آینده فقط در صورت نیاز به‌صورت muted و بدون جعل قیمت/مشخصات. قیمت، Currency، مدت و eligibility از Backend می‌آیند.

### 4.3 Login

```text
Welcome back

[ Site URL ]
[ Password ]

[ ورود ]

فراموشی رمز عبور؟
```

قرارداد V1 بر Site URL + Web Password است، نه Email/Password عمومی. **Contact Email برای ارتباط با مشتری است و credential/identifier ورود محسوب نمی‌شود.**

### 4.4 Register

Wizard چهارمرحله‌ای:

```text
1. فروشگاه
   ↓
2. اتصال
   ↓
3. حساب
   ↓
4. تأیید
```

Progress، validation و recovery واضح هستند. Secretهای WordPress/WooCommerce هرگز در browser persistence ذخیره نمی‌شوند.

### 4.5 Portal Overview

Dashboard عملی و ساده شامل Welcome/account context، Subscription summary، Connected Site، Current Plan، Recent Billing و Quick Actions.

**Products، Orders، Inventory و سایر عملیات WooCommerce نباید در Portal نمایش داده شوند.**

### 4.6 Subscription

مدیریت کامل: Current Plan، Status، Start Date، End Date، Trial، Upgrade، Renew، Cancel و Change Plan. فقط actionهای مجاز Backend نمایش داده شوند.

### 4.7 Billing

```text
Billing

Current Plan
Next Billing
Payment Method

────────────────

Billing History
```

Billing در UI یعنی اشتراک + وضعیت مالی کلی + تاریخچه مالی.

### 4.8 Payments

صفحه‌ای مستقل از Billing برای transaction/payment detail و history با statusهای canonical مانند Pending، Paid و Failed. تفاوت Billing و Payments باید در navigation، title و visual hierarchy کاملاً واضح باشد.

### 4.9 Connected Site

```text
Connected Site

● Connected
example.com

Site Status
Connection Status
Last verification

[ Logout ]
```

`Logout` در این صفحه به خروج از Web Session و Portal اشاره دارد و باید از `POST /web/logout` استفاده کند. Logout به معنی حذف Account یا تغییر مالکیت Site نیست. Theme مستقیماً به WooCommerce مشتری متصل نمی‌شود.

### 4.10 Account / Security

```text
Account / Security
├── Site URL
├── Contact Email
├── Password
└── Security
```

**Site URL** اطلاعات اصلی حساب برای ورود به Portal و Login است. **Contact Email** فقط برای ارتباط با مشتری است و برای Login یا جایگزینی Site URL در احراز حساب استفاده نمی‌شود.

Security شامل Change Password، Logout و Logout All Sessions است. **Active Sessions در V1 نمایش داده نمی‌شود.**

## 5. Global UX State Model

```text
Idle → Loading → Success
                  ├→ Empty
                  └→ Pending
Loading / Action → Error
                    ├→ Recoverable
                    ├→ Authentication Required
                    ├→ Forbidden
                    ├→ Not Found
                    ├→ Conflict
                    ├→ Rate Limited
                    └→ Server Error
```

- Success فقط با نتیجه موفق Backend.
- Empty با Error یکی نیست.
- Pending یعنی نتیجه نهایی هنوز قطعی نیست.
- Error باید قابل فهم، غیرحساس و دارای recovery مناسب باشد.
- UI نباید فقط با رنگ state را منتقل کند.
- Actionهای mutation تا تعیین نتیجه باید در برابر duplicate submission کنترل شوند.

## 6. Core Flows

### Login

```text
Login → Site URL + Web Password → Loading → POST /web/login
Success → Web Session → Portal Overview
Error   → Login Error State
```

### Register / Web Bootstrap

```text
Register Wizard
  → Client validation
  → POST /account/web-bootstrap
  → Validate + verify site
  → Resolve/Create Account + Site
  → Ownership validation
  → Create Web Credential
  → Create Web Session
  → Portal
```

`App Session` نباید جعل شود و `/sites/verify` App-only نباید توسط Theme دور زده شود.

### Portal Entry

```text
Portal URL → GET /web/me
Valid → Portal
401   → Login / new Web Session
```

وجود token محلی به‌تنهایی authenticated بودن را ثابت نمی‌کند.

### Subscription

Backend authoritative status را نمایش می‌دهد؛ Theme نمی‌تواند Premium/Active را جعل کند. Upgrade/Renew/Cancel/Change Plan فقط طبق capability/eligibility Backend.

### Checkout / Billing

```text
Pricing / Subscription
  → Select Plan
  → Backend eligibility
  → POST /billing/checkout + Idempotency-Key
  → Gateway
  → Payment Return
  → GET /billing/status
```

Timeout-after-success باید به Unknown → re-query تبدیل شود. برای همان logical operation همان Idempotency-Key حفظ می‌شود و Checkout دوم با key جدید ممنوع است مگر عملیات جدید صریحاً آغاز شود.

### Payment Return

`success=1` یا query مشابه proof نیست. ابتدا Checking/Pending UI و سپس نتیجه authoritative Backend.

```text
Payment Return → Checking → GET /billing/status
Paid/Success → Success
Pending      → bounded re-query / Pending
Failed       → Failure + safe retry
```

Polling باید bounded و rate-limit-aware باشد.

### Payments / History

```text
Payments → GET /web/billing/history
records → list
no records → Empty
401 → Re-auth
403 → Forbidden
5xx → Error + Retry
```

### Connected Site

فقط داده‌ای نمایش داده می‌شود که Backend برای Portal منتشر کند؛ اتصال مستقیم به WooCommerce مشتری ممنوع است. در این بخش action جداگانه‌ای برای Verify Again یا Disconnect Site وجود ندارد؛ خروج از Portal با `Logout` انجام می‌شود.

### Password Change / Logout

پس از تغییر موفق رمز، Backend همه Web Sessionها را revoke می‌کند؛ Theme state محلی را پاک کرده و Login می‌خواهد. Logout نیز با `POST /web/logout` انجام می‌شود، Web Session را در Backend revoke می‌کند و state موقت محلی را پاک می‌کند.

## 7. Navigation / Responsive / Accessibility

- Public و Portal navigation جدا هستند.
- Desktop: Logo چپ + Menu وسط + Login/CTA راست.
- Mobile: Hamburger + accessible Menu Sheet؛ Bottom Navigation وجود ندارد.
- Portal mobile بدون horizontal overflow و با ترتیب منطقی محتوا.
- RTL-first با logical CSS properties؛ LTR نیز بدون شکستن semantics.
- Keyboard navigation، visible focus، labels، error association، contrast، reduced motion و zoom/font scaling الزامی است.
- Glass، blur و animation باید graceful degradation داشته باشند.

## 8. Prototype Acceptance Criteria

1. هر ۱۰ صفحه اصلی High-Fidelity direction مشخص دارند.
2. Home ساختار کامل و App Preview دارد.
3. Pricing دارای Free Trial + Plan اصلی برجسته است، بدون hard-code کردن business truth.
4. Login دقیقاً Site URL + Web Password است.
5. Register چهارمرحله‌ای است.
6. Portal هیچ operational WooCommerce data ندارد.
7. Subscription مدیریت کامل مورد توافق را پوشش می‌دهد.
8. Billing و Payments از نظر مفهوم و UI جدا هستند.
9. Connected Site فقط وضعیت سایت و Logout را ارائه می‌کند؛ Verify Again و Disconnect Site وجود ندارند.
10. Active Sessions نمایش داده نمی‌شود.
11. Footer Home کامل است.
12. شدت Glass بر اساس page contract رعایت می‌شود.
13. Dark Mode از V1 در Design Direction لحاظ شده است.
14. Prototype با API/Backend authority و UX state contract تناقض ندارد.
15. هیچ implementation PHP/CSS/JS بر اساس assumption خارج از این قرارداد شروع نمی‌شود.

## 9. مرز Prototype و Implementation

Prototype قرارداد بصری و UX است، نه جایگزین Design System، API Contract یا Backend Contract.

جزئیات ریز مانند مقدار دقیق blur، opacity، shadow، spacing خاص، breakpointهای دقیق و tuning نهایی typography در زمان اجرای واقعی UI قابل تنظیم هستند، مشروط بر اینکه با قرارداد و Design System تناقض نداشته باشند.

پس از تأیید Prototype، مرحله بعدی اجرای Theme طبق `THEME_ARCHITECTURE_CONTRACT.md` است؛ هیچ business logic جدیدی نباید از Prototype وارد Theme شود.
