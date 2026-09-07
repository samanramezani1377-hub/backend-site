# قرارداد UX Flow تم WooGit

> وضعیت: V1 — قرارداد UX پیش از پیاده‌سازی
>
> این سند مرجع جریان کاربر، انتقال بین وضعیت‌ها و رفتار تجربه کاربری Theme است. این سند مکمل `THEME.md`، `THEME_API_CONTRACT.md`، `THEME_AUTH_FLOW.md` و مستندات `docs/theme/` است و نباید مسئولیت‌های Backend یا App را دوباره پیاده‌سازی کند.

## 1. هدف و اصل مرجع

UX باید قبل از UI مشخص باشد. هر صفحه، action و API-driven interaction باید بداند کاربر از کجا آمده، چه stateهایی ممکن است ببیند، چه چیزی باعث transition می‌شود و در خطا به کجا برمی‌گردد.

Backend مرجع Account، Site Ownership، Authentication، Session، Subscription، Billing و Entitlement است. Theme مرجع state نهایی کسب‌وکار نیست و نباید موفقیت را از state محلی یا URL حدس بزند.

Theme نسخه وب App نیست. هیچ Flow برای Products، Orders، Sync، Conflicts، Inventory یا Store Dashboard عملیاتی در این قرارداد وجود ندارد.

## 2. نقشه اصلی تجربه کاربر

```text
Visitor
  ↓
Landing / Public Website
  ↓
Pricing / Features / How It Works / FAQ
  ↓
┌───────────────┬─────────────────┐
│ Login         │ Register        │
│ Existing user │ New user/site   │
└───────┬───────┴────────┬────────┘
        ↓                ↓
      Web Authentication / Bootstrap
                 ↓
           Web Session
                 ↓
        Customer Portal
                 ├── Overview
                 ├── Subscription
                 ├── Billing
                 ├── Payments
                 ├── Connected Site
                 ├── Security
                 └── Account
```

Public navigation و Portal navigation جدا هستند، ولی Design System مشترک دارند.

## 3. Global UX State Model

هر interaction یا صفحه API-driven در صورت مرتبط بودن باید این stateها را مدل کند:

```text
Idle
  ↓
Loading
  ↓
Success ───────────────→ Idle / Next Flow
  ├→ Empty
  └→ Pending

Loading / Action
  └→ Error
       ├→ Recoverable
       ├→ Authentication Required
       ├→ Forbidden
       ├→ Not Found
       ├→ Conflict
       ├→ Rate Limited
       └→ Server Error
```

### قواعد عمومی

- Loading یعنی درخواست در حال انجام است؛ action تکراری که می‌تواند mutation را duplicate کند باید تا تعیین نتیجه کنترل شود.
- Success فقط وقتی نمایش داده می‌شود که Backend نتیجه موفق قراردادی را برگرداند.
- Empty یعنی درخواست معتبر بوده ولی داده قابل نمایش وجود ندارد؛ Empty با Error یکی نیست.
- Pending یعنی نتیجه نهایی هنوز توسط Backend قطعی نشده است.
- Error باید قابل فهم، غیرحساس و متناسب با recovery باشد.
- UI نباید فقط با رنگ state را منتقل کند؛ متن/label/icon مناسب نیز لازم است.
- Refresh صفحه نباید state کسب‌وکار محلی را authoritative کند.

## 4. Visitor / Public Website Flow

### 4.1 Landing

```text
Visitor → Landing
```

Landing باید مسیرهای روشن به Features، How It Works، Pricing، FAQ، Documentation، Support و CTA ثبت‌نام/ورود داشته باشد.

### 4.2 Pricing

```text
Landing → Pricing
Pricing → plan information
Pricing → Login (existing customer)
Pricing → Register (new customer)
Pricing → Checkout (authenticated + eligible)
```

قیمت، Currency، مدت، محدودیت‌ها و وضعیت پلن از Backend می‌آیند. اگر Pricing API در Loading باشد، UI نباید قیمت ساختگی نمایش دهد.

### 4.3 Public Error States

خطای یک بخش عمومی نباید کل سایت را بی‌دلیل غیرقابل استفاده کند. در صورت شکست یک API عمومی، همان بخش باید Error/Retry مناسب داشته باشد و navigation عمومی تا حد امکان باقی بماند.

## 5. Login Flow

```text
Visitor
  ↓
Login
  ↓
Enter Site URL + Web Password
  ↓
Submit
  ↓
Loading
  ↓
Backend Web Login
  ├─ Success → Web Session → Portal
  └─ Error → Login Error State
```

### Success

پس از موفقیت Login، Theme Session را طبق معماری امن نگهداری و کاربر را به مقصد تعیین‌شده هدایت می‌کند. اگر مقصد مشخصی وجود نداشته باشد، مقصد پیش‌فرض Portal Overview است.

### Login Error

پیام نباید وجود یا وضعیت Account/Site را بیش از قرارداد Backend افشا کند. Credentials نباید در URL یا storage پایدار ذخیره شوند.

### 401 در Login

برای Login ناموفق، UI باید خطای عمومی اعتبارسنجی را نشان دهد؛ retry کاربرمحور مجاز است اما retry خودکار تهاجمی مجاز نیست.

## 6. Register / Web Bootstrap Flow

ثبت‌نام مستقیم وب باید از قرارداد اختصاصی Web Bootstrap استفاده کند و نباید `App Session` جعل یا `/sites/verify` App-only را دور بزند.

```text
Register
  ↓
Site URL + WP Username + WP Application Password
+ WooCommerce Consumer Key + Consumer Secret
+ Web Password + Confirmation
  ↓
Client-side validation
  ↓
Loading
  ↓
POST /account/web-bootstrap
  ↓
Backend validates + verifies site
  ↓
Resolve/Create Account + Site
  ↓
Ownership validation
  ↓
Create Web Credential
  ↓
Create Web Session
  ↓
Success → Portal
```

Credentialهای WordPress/WooCommerce request-scoped هستند و Theme نباید آن‌ها را در Cookie، Local Storage، Session Storage، Database، Log، Telemetry، Audit، Cache پایدار، HTML یا JavaScript bundle نگه دارد.

### Bootstrap Failure

در خطا، فرم تا حد امکان داده‌های غیرحساس را حفظ می‌کند تا کاربر مجبور به ورود مجدد نشود. Secretها نباید در state persistence یا error payload ذخیره شوند.

## 7. Portal Entry Flow

### Authenticated

```text
Portal URL
  ↓
Web Session موجود
  ↓
Backend /web/me
  ├─ Valid → Portal
  └─ 401 → Expired/Invalid Session Flow
```

### Unauthenticated

```text
Portal URL
  ↓
No valid Web Session
  ↓
Login
```

Theme نباید صرفاً وجود یک token محلی را به معنی authenticated بودن بداند؛ Backend باید مرجع اعتبار Session باشد.

## 8. Portal Navigation Contract

```text
Customer Portal
├── Overview
├── Subscription
├── Billing
├── Payments
├── Connected Site
├── Security
└── Account
```

Navigation باید بین Portal sections پایدار باشد. ورود مستقیم به یک subsection نیز باید همان authorization/session rules را اجرا کند.

## 9. Overview Flow

```text
Portal → Overview
       ↓
Loading
       ↓
GET authoritative account/subscription/site data
       ├─ Success → Overview
       ├─ Empty → Empty Overview (فقط اگر قرارداد داده اجازه دهد)
       ├─ 401 → Re-auth
       ├─ 403 → Forbidden
       └─ 5xx → Recoverable Error
```

Overview نباید داده عملیاتی فروشگاه را نمایش دهد.

## 10. Subscription Flow

Subscription وضعیت plan، status، dates، entitlement و actionهای مجاز را نمایش می‌دهد.

```text
Subscription
  ↓
Loading
  ↓
Backend status
  ├─ Trial
  ├─ Active
  ├─ Expired
  └─ Cancelled
```

Theme نمی‌تواند با state محلی Premium/Active را جعل کند. Actionهای Upgrade/Renew فقط در صورت اعلام مجاز بودن توسط Backend ارائه شوند.

## 11. Billing Flow

### 11.1 Plan Selection

```text
Pricing / Subscription
  ↓
Select Plan
  ↓
Validate eligibility / current status via Backend
  ↓
Checkout
```

### 11.2 Checkout

```text
Checkout Action
  ↓
Loading
  ↓
POST /billing/checkout
  + Idempotency-Key
  ↓
Backend
  ├─ payment_url → Redirect to gateway
  ├─ pending → Pending UI
  ├─ conflict → Conflict Recovery
  └─ error → Error UI
```

برای retry همان logical operation باید همان `Idempotency-Key` حفظ شود.

### 11.3 Timeout-after-success

اگر Theme timeout/network failure دریافت کرد، نباید فرض کند Checkout شکست خورده است.

```text
Checkout request
  ↓
Timeout / connection lost
  ↓
Unknown result
  ↓
Re-query Backend using operation/idempotency context
  ├─ Completed → continue to payment/result flow
  ├─ Pending → Pending UI
  ├─ Failed → Failure UI
  └─ Unknown → Safe recovery / retry according to contract
```

ساخت Checkout دوم با Idempotency-Key جدید برای همان عملیات ممنوع است مگر اینکه کاربر یک عملیات جدید را صریحاً آغاز کند.

## 12. Payment Return Flow

بازگشت از درگاه proof of payment نیست.

```text
Gateway
  ↓
Theme /payment/result
  ↓
Show "در حال بررسی نتیجه پرداخت"
  ↓
GET /billing/status
  ↓
Backend authoritative result
```

### Payment Success

```text
Billing Status = paid/successful
  ↓
Show success
  ↓
Refresh subscription/entitlement data
  ↓
Offer Portal / Subscription destination
```

URL query مانند `success=1` به‌تنهایی معتبر نیست.

### Payment Pending

```text
Payment Return
  ↓
Pending
  ↓
Re-query Billing Status according to bounded retry/polling policy
  ├─ Success → Success
  ├─ Failed → Failed
  └─ Still Pending → Pending + manual retry/support path
```

Polling باید bounded و rate-limit-aware باشد؛ Theme نباید با polling تهاجمی Backend را تحت فشار قرار دهد.

### Payment Failed

```text
Billing Status = failed
  ↓
Explain failure safely
  ↓
Retry Checkout / Return to plan selection
```

در صورت وجود `payment_pending` یا failure قراردادی، UI نباید نتیجه را به موفقیت تبدیل کند.

## 13. Payments / History Flow

```text
Payments
  ↓
Loading
  ↓
GET /web/billing/history
  ├─ Success + records → Payment list
  ├─ Success + no records → Empty State
  ├─ 401 → Re-auth
  ├─ 403 → Forbidden
  └─ 5xx → Error + Retry
```

Empty History به معنی خطا نیست.

## 14. Connected Site Flow

Connected Site فقط اطلاعاتی را نمایش می‌دهد که Backend برای Portal منتشر کرده است.

```text
Connected Site
  ↓
Loading
  ↓
Backend site/account context
  ├─ Success → Site information
  ├─ Empty → No connected site (only if contract allows)
  ├─ 401 → Re-auth
  └─ 403 → Forbidden / account unavailable
```

Theme مستقیماً به WooCommerce مشتری متصل نمی‌شود.

## 15. Security Flow

### Password Change

```text
Security
  ↓
Enter current + new password + confirmation
  ↓
Validation
  ↓
Loading
  ↓
Backend
  ↓
Success
  ↓
All Web Sessions revoked
  ↓
Theme clears local session state
  ↓
Login
```

پس از تغییر موفق رمز، Theme نباید Session قبلی را معتبر نگه دارد.

### Logout

```text
Logout
  ↓
POST /web/logout
  ↓
Clear temporary client state
  ↓
Public Landing / Login
```

اگر logout request به علت network failure نامشخص باشد، Theme نباید اطلاعات حساس را نگه دارد و باید طبق قرارداد امن logout state را مدیریت کند.

## 16. Account Flow

Contact email و اطلاعات حساب از API رسمی Backend مدیریت می‌شوند.

```text
Account
  ↓
Loading
  ↓
Backend
  ├─ Success → Account data
  ├─ Validation Error → Field error
  ├─ 401 → Re-auth
  ├─ 403 → Forbidden
  └─ 5xx → Retryable error
```

## 17. Session Lifecycle

```text
No Session
   ↓ Login/Bootstrap
Valid Web Session
   ↓
Authenticated Portal
   ↓
Session expires / revoked
   ↓
401
   ↓
Clear temporary session state
   ↓
Login
   ↓
New Web Session
   ↓
Backend rechecks Account + Site Ownership + required Entitlement
```

Session منقضی‌شده قابل revive محلی نیست. Theme نباید expiration را با افزایش محلی زمان یا نگه‌داشتن token دور بزند.

## 18. Global HTTP Error → UX Contract

| HTTP | Semantic | UX رفتار | Retry |
|---|---|---|---|
| 400 | Validation / malformed request | خطای نزدیک فیلد یا action + اصلاح ورودی | فقط پس از اصلاح |
| 401 | Invalid/expired authentication | پاک‌سازی temporary session و Login | user-driven |
| 403 | Authenticated but not allowed/unavailable | Forbidden یا توضیح محدود + مسیر مجاز | معمولاً خیر |
| 404 | Resource/page not found | Not Found با مسیر برگشت | معمولاً خیر |
| 409 | Conflict / idempotency / pending | نمایش وضعیت conflict و recovery قراردادی | طبق contract |
| 429 | Rate limited | پیام واضح + احترام به `Retry-After` | بعد از زمان مجاز |
| 5xx | Backend/server failure | خطای عمومی + Retry | bounded/user-driven |

### اصل 401

401 نباید با retry بی‌نهایت حل شود. Session پاک می‌شود و کاربر به Login هدایت می‌شود.

### اصل 403

403 به معنی Login مجدد نیست مگر قرارداد Backend صراحتاً چنین چیزی را اعلام کند.

### اصل 404

404 صفحه عمومی، resource یا endpoint باید با context مناسب نمایش داده شود؛ Theme نباید همه 404ها را یکسان و گمراه‌کننده نشان دهد.

### اصل 409

409 ممکن است نتیجه‌ای مثل `idempotency_conflict`، `operation_pending` یا conflict دیگر داشته باشد. UX باید بر اساس `code` رفتار کند، نه فقط HTTP status.

### اصل 429

Theme باید در صورت وجود `Retry-After` آن را رعایت کند و retry خودکار تهاجمی انجام ندهد.

### اصل 5xx

جزئیات داخلی، stack trace، SQL، secret یا implementation detail نمایش داده نمی‌شود.

## 19. Error / Empty / Pending Copy Rules

- متن خطا باید action-oriented و قابل فهم باشد.
- برای Secret یا Credential هرگز مقدار ورودی در error message بازنمایی نشود.
- Empty state باید توضیح دهد چرا چیزی برای نمایش نیست و اگر ممکن است action بعدی را ارائه کند.
- Pending state باید صادقانه بگوید نتیجه هنوز قطعی نشده است.
- Success باید فقط پس از نتیجه authoritative نمایش داده شود.
- Error قابل retry باید CTA مشخص برای Retry داشته باشد.

## 20. Browser Navigation Rules

### Back

Back باید کاربر را به مرحله منطقی قبلی برگرداند، اما نباید Checkout mutation را دوباره اجرا کند.

### Refresh

Refresh باید صفحه را از state authoritative Backend بازسازی کند. Form input حساس نباید به‌صورت ناخواسته persist شود.

### Direct URL

دسترسی مستقیم به Portal subsection باید session و authorization را بررسی کند. Public page نباید به‌صورت مصنوعی authentication requirement ایجاد کند.

### Duplicate Submission

در mutationها button/action باید در Loading کنترل شود و mutationهایی که Idempotency دارند از همان کلید استفاده کنند.

## 21. Accessibility UX Contract

تمام stateهای مهم باید برای assistive technology قابل درک باشند:

- Loading با status مناسب اعلام شود؛
- Error نزدیک کنترل مرتبط و قابل دسترسی باشد؛
- Success/Pending به‌صورت معنایی اعلام شود؛
- focus پس از navigation/error/dialog منطقی مدیریت شود؛
- state نباید فقط با رنگ منتقل شود؛
- keyboard و screen reader flow باید معادل mouse/touch باشد.

این قواعد با `docs/theme/ACCESSIBILITY.md` هماهنگ هستند.

## 22. Responsive UX Contract

Responsive نباید Flow یا semantics را تغییر دهد.

```text
Mobile → compact navigation → same destinations
Tablet → readable composition
Desktop → expanded navigation/portal layout
```

در Billing، جدول/لیست در موبایل می‌تواند به Card/List تبدیل شود، اما status، amount، currency، date و actionهای قراردادی نباید حذف شوند.

## 23. Security UX Boundaries

Theme نباید:

- Web Session را در URL قرار دهد؛
- Session منقضی‌شده را locally revive کند؛
- App Session را برای Web جعل کند؛
- Credentialهای WooCommerce/WordPress را ذخیره کند؛
- موفقیت پرداخت را از URL تشخیص دهد؛
- Entitlement را خودش محاسبه کند؛
- مستقیماً به WooCommerce مشتری وصل شود؛
- خطاهای داخلی Backend را به کاربر نمایش دهد.

## 24. UX Acceptance Criteria V1

پیاده‌سازی Theme فقط وقتی از نظر UX قابل قبول است که:

1. تمام مسیرهای اصلی Visitor → Landing → Pricing → Login/Register → Portal مشخص و قابل اجرا باشند.
2. تمام Portal sections مسیر ورود/خروج و session behavior مشخص داشته باشند.
3. Loading، Success، Empty، Pending و Error برای interactionهای مرتبط وجود داشته باشند.
4. 400، 401، 403، 404، 409، 429 و 5xx به UX مشخص نگاشت شوند.
5. Session expiry همیشه به re-auth امن منتهی شود.
6. Payment Pending/Failed/Success از هم تفکیک شوند.
7. Payment Return بدون استعلام Backend موفق تلقی نشود.
8. Checkout retry از Idempotency Contract پیروی کند.
9. timeout-after-success پوشش داده شود.
10. Back/Refresh/Duplicate Submission باعث mutation ناخواسته نشوند.
11. Accessibility و Responsive rules در همه Flowهای اصلی حفظ شوند.
12. هیچ UX state یا UI تصمیم authoritative خارج از قرارداد Backend ساخته نشود.

## 25. ارتباط با سایر مستندات

| سند | مسئولیت |
|---|---|
| `THEME.md` | مشخصات کلی Theme و مرز مسئولیت‌ها |
| `THEME_API_CONTRACT.md` | API و قرارداد ارتباط با Backend |
| `THEME_AUTH_FLOW.md` | پروتکل Auth و Web Session |
| `THEME_UX_FLOW.md` | **مرجع UX Flow و State Transition** |
| `docs/theme/PAGES.md` | فهرست و معماری صفحات |
| `docs/theme/PORTAL.md` | Information Architecture پرتال |
| `docs/theme/ACCESSIBILITY.md` | الزامات Accessibility |
| `docs/theme/RESPONSIVE.md` | الزامات Responsive |
| `docs/theme/DESIGN-SYSTEM.md` | Visual/UI Design System |

در صورت اختلاف، هر سند فقط در حوزه مسئولیت خودش authoritative است و Flowهای UX باید با API/Auth/Backend contract سازگار بمانند.
