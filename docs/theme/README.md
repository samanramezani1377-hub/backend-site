# مستندات Theme WooGit

> وضعیت: V1 — Documentation Before Implementation
>
> `theme/woogit/` هنوز وارد فاز پیاده‌سازی نشده است. این پوشه قرارداد و معماری مورد نیاز قبل از اجرای کد را متمرکز می‌کند.

## جایگاه Theme

Theme وب‌سایت رسمی WooGit و Customer Portal است؛ Web App عملیاتی فروشگاه نیست.

- **Theme:** Presentation، صفحات عمومی، Customer Portal، Account، Subscription و Billing UI
- **Main Plugin / Backend:** API authority، Authentication، Session، Account، Site Ownership، Entitlement، Billing، Security و Business Logic
- **Android App:** عملیات فروشگاه مانند Products، Orders، Sync و Conflict Resolution

Theme نباید منطق عملیاتی App یا اتصال مستقیم به WooCommerce مشتری را پیاده‌سازی کند.

## اسناد

| سند | مسئولیت |
|---|---|
| [THEME.md](../THEME.md) | قرارداد اصلی Theme و مرز مسئولیت‌ها |
| [THEME_UX_FLOW.md](../THEME_UX_FLOW.md) | **مرجع UX Flow و State Transition** |
| [THEME_ARCHITECTURE_CONTRACT.md](../THEME_ARCHITECTURE_CONTRACT.md) | **قرارداد اجرایی و فریز ساختار فایل Theme** |
| [ARCHITECTURE.md](ARCHITECTURE.md) | معماری هدف، لایه‌بندی و ساختار فایل پیشنهادی |
| [PAGES.md](PAGES.md) | معماری صفحات و Page Inventory |
| [PORTAL.md](PORTAL.md) | معماری Customer Portal و Information Architecture |
| [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md) | توکن‌ها، Liquid Glass و Component System |
| [RESPONSIVE.md](RESPONSIVE.md) | Responsive و RTL/LTR |
| [PERFORMANCE.md](PERFORMANCE.md) | Performance و Progressive Enhancement |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | Accessibility و UX resilience |
| [THEME_API_CONTRACT.md](../THEME_API_CONTRACT.md) | قرارداد API عمومی |
| [THEME_AUTH_FLOW.md](../THEME_AUTH_FLOW.md) | Auth و Web Session |
| [THEME_TESTING.md](../THEME_TESTING.md) | Testing و CI |
| [theme-management/README.md](../theme-management/README.md) | Theme Management و محتوای قابل مدیریت |

## قواعد یکپارچه V1

1. Backend تنها مرجع authoritative برای Account، Site، Ownership، Entitlement، Billing و Authorization است.
2. Theme فقط API عمومی منتشرشده را مصرف می‌کند و به internals افزونه وابسته نیست.
3. Web Session و App Session جدا هستند.
4. Session منقضی‌شده هرگز locally revive نمی‌شود.
5. Payment Return به‌تنهایی proof of payment نیست؛ وضعیت باید دوباره از Backend خوانده شود.
6. Checkout retry باید همان `Idempotency-Key` را حفظ کند.
7. Theme Management فقط presentation/content را مدیریت می‌کند.
8. Products، Orders، Sync، Conflicts، Inventory و Store Operations در Theme وجود ندارند.
9. طراحی mobile-first، RTL-first، accessible و performance-first است.
10. همه صفحات API-driven باید Loading، Empty/Pending در صورت نیاز، Error و Success state داشته باشند.
11. 400، 401، 403، 404، 409، 429 و 5xx باید طبق `THEME_UX_FLOW.md` رفتار شوند.
12. Payment Pending، Payment Failed و Payment Success باید در UX از هم تفکیک شوند.
13. Timeout-after-success در Checkout باید پوشش داده شود و نتیجه Unknown نباید به‌عنوان Failure تفسیر شود.
14. هیچ credential حساس مشتری نباید در storage پایدار، HTML، log یا telemetry نگهداری شود.
15. Progressive enhancement ترجیح دارد و functionality نباید بدون JavaScript غیرضروری از کار بیفتد.
16. ساختار اجرایی `theme/woogit/` و مرز مسئولیت فایل‌ها طبق `THEME_ARCHITECTURE_CONTRACT.md` فریز است؛ تغییر معماری باید ابتدا در مستندات تصویب شود.

## قبل از شروع کدنویسی

هر قرارداد مبهم Backend، به‌خصوص مسیر مستقیم ثبت‌نام وب، ایجاد Web Credential، Web Billing و Payment Return، باید ابتدا در Backend/API Contract نهایی شود. Theme نباید برای شکاف‌های قراردادی راه‌حل امنیتی مستقل اختراع کند.
