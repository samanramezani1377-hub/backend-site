# قرارداد اجرایی معماری Theme WooGit

> وضعیت: V1 — Architecture Contract Before Implementation
>
> این سند ساختار `theme/woogit/` را از یک ساختار هدف مستنداتی به قرارداد اجرایی پیاده‌سازی تبدیل می‌کند.
>
> **مهم:** در زمان تصویب این سند، `theme/woogit/` هنوز وارد فاز پیاده‌سازی نشده است. این سند مجوز ایجاد کد را صادر نمی‌کند؛ فقط مشخص می‌کند کد آینده دقیقاً کجا و با چه مرزهایی قرار می‌گیرد.

## 1. هدف و اصل Freeze

ساختار فایل Theme برای V1 در این سند **فریز** می‌شود. در جریان implementation نباید برای راحتی یک feature، فایل‌ها یا لایه‌های جدید با مسئولیت معماری جدید ایجاد شوند.

اگر نیاز جدیدی پیدا شد که در این قرارداد پوشش داده نشده است:

```text
نیاز جدید
  ↓
بررسی Architecture Contract
  ↓
تصمیم معماری
  ↓
به‌روزرسانی مستندات
  ↓
سپس implementation
```

کد نباید ابتدا معماری را تغییر دهد.

این قرارداد مکمل `THEME.md`، `THEME_UX_FLOW.md` و `docs/theme/ARCHITECTURE.md` است و هیچ‌کدام از مرزهای قبلی را حذف یا جایگزین نمی‌کند.

---

## 2. ساختار قطعی V1

```text
theme/woogit/
│
├── assets/
│   ├── css/
│   │   ├── foundation.css
│   │   ├── components.css
│   │   ├── pages.css
│   │   └── responsive.css
│   │
│   ├── js/
│   │   ├── core.js
│   │   ├── navigation.js
│   │   ├── auth.js
│   │   └── portal.js
│   │
│   └── images/
│
├── inc/
│   ├── setup/
│   ├── admin/
│   │   └── theme-management/
│   ├── api/
│   ├── auth/
│   ├── portal/
│   └── helpers/
│
├── templates/
│   ├── public/
│   ├── auth/
│   └── portal/
│
├── template-parts/
│   ├── header/
│   ├── footer/
│   ├── hero/
│   ├── features/
│   ├── pricing/
│   ├── faq/
│   └── portal/
│
├── functions.php
├── style.css
└── ...
```

`...` فقط برای فایل‌های استاندارد و ضروری WordPress Theme یا فایل‌هایی است که بعداً با همین قرارداد تصویب شوند؛ مجوز ایجاد لایه معماری جدید نیست.

---

## 3. مرز مسئولیت دایرکتوری‌ها

### 3.1 `assets/css/`

#### `foundation.css`
مسئول:
- CSS reset/base؛
- design tokens؛
- typography پایه؛
- رنگ‌ها و surfaceهای پایه؛
- spacing و sizing primitives؛
- RTL/LTR foundation.

ممنوع:
- business logic؛
- state تصمیم‌گیری؛
- استایل اختصاصی یک feature که باید در component/page باشد.

#### `components.css`
مسئول استایل Componentهای reusable مانند:
- Button؛
- Input؛
- Card؛
- Badge؛
- Navigation؛
- Dialog/Sheet؛
- Toast؛
- Loading/Skeleton؛
- Empty/Error state.

#### `pages.css`
فقط layout و styling مخصوص یک صفحه یا یک family از صفحات.

#### `responsive.css`
فقط breakpoint و responsive behavior. Responsive نباید semantics یا business state را تغییر دهد.

---

### 3.2 `assets/js/`

JavaScript برای interaction و progressive enhancement است، نه بازسازی Theme به‌صورت SPA مگر اینکه قرارداد جداگانه‌ای تصویب شود.

#### `core.js`
فقط utilityهای عمومی client-side که چند feature به آن‌ها نیاز دارند؛ مانند state-independent DOM helpers و behaviorهای عمومی.

نباید شامل Billing/Auth business rules باشد.

#### `navigation.js`
فقط:
- mobile navigation؛
- menu state؛
- accessible navigation interaction؛
- focus management مرتبط با navigation.

#### `auth.js`
فقط interaction مربوط به Web Auth:
- login؛
- register/bootstrap UI؛
- loading/error/success state فرم‌ها؛
- session-related UI behavior.

تصمیم Account، Ownership، Entitlement یا اعتبار Credential با JS انجام نمی‌شود.

#### `portal.js`
فقط interaction مشترک Customer Portal.

Products، Orders، Sync، Inventory، Conflicts یا Store Operations نباید وارد آن شوند.

Assetهای JS باید تا حد امکان feature/page scoped باشند و asset غیرضروری روی همه صفحات load نشود.

---

## 4. `inc/` — منطق اجرایی Theme

`inc/` تنها محل کد PHP اجرایی Theme خارج از فایل‌های bootstrap و templateها است.

### `inc/setup/`
مسئول bootstrap و registrationهای استاندارد WordPress Theme:
- theme setup؛
- enqueue registration؛
- menus؛
- supports؛
- image sizes در صورت نیاز؛
- hooks عمومی WordPress.

نباید Business Logic Backend را در خود نگه دارد.

### `inc/api/`
**تنها مرز ارتباط Theme با WooGit Public REST API.**

مسئول:
- ساخت request؛
- HTTP transport؛
- headerهای قراردادی؛
- parse/normalize response؛
- mapping errorهای API به مدل قابل مصرف Theme.

این لایه نباید:
- Entitlement را محاسبه کند؛
- Ownership را تعیین کند؛
- Billing truth را جعل کند؛
- Authorization را جایگزین Backend کند؛
- مستقیماً به Database یا کلاس‌های داخلی Plugin دسترسی داشته باشد.

ساختار پیشنهادی در همین لایه می‌تواند بر اساس domainهای قراردادی باشد:

```text
inc/api/
├── client.php
├── account.php
├── auth.php
├── billing.php
└── site.php
```

این فایل‌ها نمونه mapping معماری هستند؛ ایجاد فایل فقط در صورت نیاز واقعی و مطابق همین مرز مجاز است.

### `inc/auth/`
مسئول Web Authentication و Session orchestration:
- login flow؛
- logout flow؛
- session presence/validation؛
- expired-session handling؛
- auth state مورد نیاز rendering.

App Session و Web Session کاملاً جدا هستند. Theme نباید App Session را به Web تبدیل یا locally revive کند.

### `inc/portal/`
مسئول orchestration داده و state مورد نیاز Customer Portal:
- Overview؛
- Subscription؛
- Billing؛
- Payments؛
- Connected Site؛
- Account / Security.

این لایه فقط state authoritative دریافتی از API را برای presentation آماده می‌کند و Business Logic Backend را تکرار نمی‌کند.

### `inc/admin/theme-management/`
تنها محل PHP مربوط به مدیریت محتوای قابل تنظیم Theme در WordPress Admin.

مجاز:
- Logo؛
- Favicon؛
- Hero؛
- Features؛
- How It Works؛
- Pricing Presentation؛
- FAQ؛
- Footer؛
- Social؛
- eNAMAD؛
- سایر presentation/content settings مصوب.

ممنوع:
- Account؛
- Billing authority؛
- Entitlement؛
- Web Session authority؛
- WooCommerce customer credentials؛
- Store Operations.

تمام ورودی‌های Admin باید طبق قرارداد امنیتی Theme با capability، nonce، sanitize و escape مناسب مدیریت شوند.

### `inc/helpers/`
فقط helperهای عمومی و بدون وابستگی به یک domain تجاری خاص.

Helper نباید به محل مخفی Business Logic تبدیل شود.

---

## 5. `templates/` — Page Composition

Template مسئول **صفحه کامل** است، نه business logic.

### `templates/public/`
صفحات عمومی:
- Home؛
- Features؛
- How It Works؛
- Pricing؛
- FAQ؛
- Documentation؛
- Support؛
- Service Status؛
- Privacy؛
- Terms.

### `templates/auth/`
صفحات:
- Login؛
- Register/Web Bootstrap؛
- وضعیت اتصال/اعتبارسنجی در صورت وجود صفحه مستقل.

### `templates/portal/`
صفحات Customer Portal:
- Overview؛
- Subscription؛
- Billing؛
- Payments؛
- Connected Site؛
- Account / Security؛
- Payment Result در صورت نیاز به template مستقل.

Template می‌تواند adapter/orchestrator مناسب را مصرف کند، اما نباید مستقیماً transport HTTP، Database یا WooCommerce را اجرا کند.

---

## 6. `template-parts/` — Reusable Presentation

Template Part یک بخش reusable از UI است.

### `header/`
Header عمومی و Portal header در صورت نیاز، با حفظ separation مناسب.

### `footer/`
Footer عمومی، legal links، contact/social و trust presentation.

### `hero/`
Hero و variantهای presentation آن.

### `features/`
Feature section و feature item/card.

### `pricing/`
Pricing section، pricing card و CTAهای مربوط به Pricing.

**Pricing Part نباید قیمت یا entitlement را خودش محاسبه کند؛ داده authoritative را render می‌کند.**

### `faq/`
FAQ section و FAQ item.

### `portal/`
Component/sectionهای reusable پرتال مانند:
- account summary؛
- subscription summary؛
- billing summary؛
- payment state؛
- connected site summary؛
- portal navigation.

Template Parts نباید مستقیماً API call یا Database query انجام دهند.

---

## 7. `functions.php`

`functions.php` **bootstrap نازک Theme** است.

مسئولیت:
- load کردن فایل‌های مورد نیاز Theme؛
- اجرای bootstrap استاندارد؛
- wiring محدود و قابل ردیابی.

ممنوع:
- تبدیل شدن به God File؛
- قرار دادن تمام API/Auth/Billing code در آن؛
- query مستقیم Database؛
- WooCommerce customer access؛
- Business Logic.

هر مسئولیت جدید باید به محل قراردادی خودش منتقل شود.

---

## 8. `style.css`

`style.css` فایل استاندارد WordPress Theme و entry metadata است.

تا حد امکان نباید محل اصلی Design System باشد. Design System و CSS اجرایی در `assets/css/` قرار می‌گیرند.

---

## 9. Asset و Dependency Contract

اصل dependency:

```text
WordPress Theme Bootstrap
        ↓
   inc/setup
        ↓
   inc/api / inc/auth / inc/portal / inc/admin
        ↓
     View Data
        ↓
    templates
        ↓
 template-parts
```

Presentation نباید dependency معکوس به Backend internals داشته باشد.

قواعد:

- `template-parts` → API مستقیم: **ممنوع**
- `templates` → HTTP مستقیم: **ممنوع**
- `assets/js` → PHP internals: **ممنوع**
- Theme → Plugin internal classes: **ممنوع**
- Theme → Backend Database: **ممنوع**
- Theme → Customer WooCommerce API: **ممنوع**
- API adapter → Backend Public REST API: **مجاز**
- Auth layer → Web Auth contract: **مجاز**
- Portal layer → API adapters: **مجاز**
- Theme Management → WordPress Settings/Media Library: **مجاز**

---

## 10. Page-to-Architecture Mapping

| صفحه/Feature | Template | Reusable Parts | API/Layer |
|---|---|---|---|
| Home | `templates/public/home.php` | hero, features, pricing, faq, footer | public data در صورت نیاز |
| Features | `templates/public/features.php` | features | — |
| How It Works | `templates/public/how-it-works.php` | reusable content parts | — |
| Pricing | `templates/public/pricing.php` | pricing | Billing Plans |
| FAQ | `templates/public/faq.php` | faq | — |
| Login | `templates/auth/login.php` | auth form | Auth/API |
| Register | `templates/auth/register.php` | bootstrap form | Account/Auth API |
| Portal Overview | `templates/portal/overview.php` | portal summaries | web/me + Billing |
| Subscription | `templates/portal/subscription.php` | subscription state | Billing Status |
| Billing | `templates/portal/billing.php` | billing/checkout UI | Billing |
| Payment Result | `templates/portal/payment-result.php` | payment state | Billing Status |
| Payments | `templates/portal/payments.php` | payment list | Billing History |
| Connected Site | `templates/portal/connected-site.php` | site summary | Account/Site contract |
| Account / Security | `templates/portal/account.php` | account + security UI | Web Account/Auth |

مسیر و نام فایل می‌تواند فقط در چارچوب همین قرارداد تغییر کند؛ اضافه کردن domain جدید نیازمند تصمیم معماری است.

---

## 11. State Contract

Stateهای UI در Theme از `THEME_UX_FLOW.md` پیروی می‌کنند:

```text
idle
loading
success
empty
pending
error
```

State UI نباید جایگزین state authoritative Backend شود.

مثلاً:

```text
Payment Return
   ↓
loading/checking
   ↓
Backend Billing Status
   ├── paid       → Success UI
   ├── pending    → Pending UI
   ├── failed     → Failed UI
   └── unknown    → Unknown/Error UI
```

`success` در query string یا redirect URL به‌تنهایی proof پرداخت نیست.

---

## 12. Security Contract

این معماری تمام مرزهای امنیتی اسناد قبلی را حفظ می‌کند:

- Backend تنها authority برای Account، Ownership، Entitlement، Billing و Authorization است.
- Web Session و App Session جدا هستند.
- Web Session در URL قرار نمی‌گیرد.
- Session منقضی‌شده locally revive نمی‌شود.
- Credentialهای WordPress/WooCommerce request-scoped هستند و در DB، log، telemetry، audit، cache پایدار، HTML، JS bundle یا browser storage نگهداری نمی‌شوند.
- Theme مستقیماً به WooCommerce مشتری متصل نمی‌شود.
- هیچ secret در asset عمومی قرار نمی‌گیرد.
- Error UI نباید stack trace، SQL، secret یا internal implementation detail افشا کند.

---

## 13. ممنوعیت‌های صریح معماری

در V1 موارد زیر در Theme ممنوع هستند:

1. کپی کردن PHP class/service/plugin logic از Main Plugin؛
2. `include/require` کردن internals افزونه؛
3. Database access مستقیم به Backend یا Plugin tables؛
4. اتصال مستقیم Theme به WooCommerce مشتری؛
5. پیاده‌سازی Authorization/Entitlement در Theme؛
6. ساخت Store Dashboard عملیاتی؛
7. Products/Orders/Sync/Conflicts/Inventory/Media Operations؛
8. ذخیره دائمی Customer Credentials؛
9. قرار دادن Session Token در URL؛
10. تبدیل `functions.php` به محل Business Logic؛
11. API call از Template Part؛
12. قرار دادن Business Logic داخل CSS/JS presentation؛
13. ایجاد architecture layer جدید بدون به‌روزرسانی این سند.

---

## 14. Definition of Done برای معماری

قبل از شروع implementation باید بتوانیم برای هر فایل آینده پاسخ دهیم:

- چرا این فایل وجود دارد؟
- مسئولیت دقیق آن چیست؟
- در کدام لایه قرار دارد؟
- چه dependencyهایی دارد؟
- چه dependencyهایی نباید داشته باشد؟
- آیا با API Contract سازگار است؟
- آیا با UX Flow سازگار است؟
- آیا مرز Theme/App/Backend را حفظ می‌کند؟

اگر پاسخ مشخصی وجود نداشته باشد، فایل هنوز آماده ایجاد نیست.

---

## 15. رابطه با اسناد دیگر

```text
THEME.md
  ↓
THEME_UX_FLOW.md
  ↓
THEME_ARCHITECTURE_CONTRACT.md   ← قرارداد اجرایی ساختار
  ↓
docs/theme/ARCHITECTURE.md
  ↓
THEME_API_CONTRACT.md
  ↓
THEME_AUTH_FLOW.md
  ↓
THEME_DESIGN_SYSTEM.md / RESPONSIVE / ACCESSIBILITY / PERFORMANCE
  ↓
Implementation
```

در صورت مشاهده تناقض، ابتدا باید مستندات هماهنگ شوند؛ implementation نباید یک قرارداد را مخفیانه دور بزند.
