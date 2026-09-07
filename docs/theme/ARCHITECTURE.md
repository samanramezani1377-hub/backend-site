# معماری Theme WooGit

## ۱. اصل معماری

Theme یک WordPress Theme مستقل است که دو تجربه جدا ارائه می‌کند:

```text
Public Website
    └─ معرفی، قابلیت‌ها، قیمت، FAQ، Docs، Support، Legal

Customer Portal
    └─ Overview، Subscription، Billing، Payments، Connected Site، Account / Security
```

این دو تجربه از یک Design System مشترک استفاده می‌کنند، اما Navigation و Information Architecture آن‌ها یکسان نیست.

## ۲. مرز لایه‌ها

```text
Browser
  ↓
Theme Presentation
  ├─ Templates
  ├─ Template Parts
  ├─ UI Components
  └─ Page/Portal interaction
  ↓
Theme API/Auth adapters
  ↓
Public WooGit REST API
  ↓
WooGit Main Plugin / Backend
```

Theme می‌تواند برای نمایش و تعامل، adapterهای API و state UI داشته باشد؛ اما Account، Ownership، Entitlement، Billing و Authorization را خودش تعیین نمی‌کند.

## ۳. ساختار هدف

این ساختار **هدف مستنداتی قبل از اجرا** است و به معنی وجود کد فعلی نیست:

```text
theme/woogit/
├── assets/
│   ├── css/
│   │   ├── foundation.css
│   │   ├── components.css
│   │   ├── pages.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── core.js
│   │   ├── navigation.js
│   │   ├── auth.js
│   │   └── portal.js
│   └── images/
├── inc/
│   ├── setup/
│   ├── admin/
│   │   └── theme-management/
│   ├── api/
│   ├── auth/
│   ├── portal/
│   └── helpers/
├── templates/
│   ├── public/
│   ├── auth/
│   └── portal/
├── template-parts/
│   ├── header/
│   ├── footer/
│   ├── hero/
│   ├── features/
│   ├── pricing/
│   ├── faq/
│   └── portal/
├── functions.php
└── style.css
```

## ۴. قواعد ساختار

- Template مسئول layout و composition است.
- Template Part مسئول بخش reusable یک صفحه است.
- Componentهای UI باید تا حد ممکن reusable و مستقل از business logic باشند.
- API adapter مسئول transport و mapping قرارداد است، نه تصمیم‌گیری entitlement.
- Auth layer فقط جریان Web Auth/Session را مدیریت می‌کند.
- Portal layer نباید به عملیات فروشگاه App تبدیل شود.
- Theme Management در بخش admin فقط presentation/content settings را نگهداری می‌کند.
- از duplicate business logic بین page، component و adapter جلوگیری شود.

## ۵. JavaScript

JavaScript باید برای interaction لازم و progressive enhancement استفاده شود، نه برای بازسازی کل سایت به‌صورت SPA مگر اینکه بعداً قرارداد معماری صریحی برای آن تصویب شود.

هر script باید تا حد امکان page-scoped یا feature-scoped باشد. Asset مربوط به Billing نباید روی همه صفحات بارگذاری شود.

## ۶. CSS

CSS باید token-driven و component-based باشد. RTL/LTR با logical properties مدیریت شود و breakpointها نباید semantics محتوا را تغییر دهند.

## ۷. ممنوعیت معماری

- include/require کردن کلاس‌ها یا فایل‌های داخلی Plugin
- دسترسی مستقیم Theme به Database Backend
- اتصال مستقیم Theme به WooCommerce مشتری
- پیاده‌سازی Business Logic در Theme
- ذخیره Credential مشتری برای استفاده بعدی
- قرار دادن Web Session در URL
- ساخت Store Dashboard عملیاتی در Theme
