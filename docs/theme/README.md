# مستندات Theme WooGit

> وضعیت: **V1 — Documentation Frozen / Implementation Ready**
>
> `theme/woogit/` هنوز وارد فاز پیاده‌سازی نشده است. این پوشه قراردادهای مورد نیاز قبل از اجرای کد را متمرکز می‌کند.

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
| [THEME_DOCUMENTATION_FREEZE.md](../THEME_DOCUMENTATION_FREEZE.md) | وضعیت Freeze و قواعد تغییر قرارداد |
| [THEME_UX_FLOW.md](../THEME_UX_FLOW.md) | مرجع UX Flow و State Transition |
| [THEME_ARCHITECTURE_CONTRACT.md](../THEME_ARCHITECTURE_CONTRACT.md) | قرارداد اجرایی و فریز ساختار فایل Theme |
| [ARCHITECTURE.md](ARCHITECTURE.md) | معماری هدف و لایه‌بندی |
| [PAGES.md](PAGES.md) | معماری صفحات و Page Inventory |
| [PORTAL.md](PORTAL.md) | معماری Customer Portal و Information Architecture |
| [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md) | Design System، توکن‌ها، Liquid Glass و Component System |
| [RESPONSIVE.md](RESPONSIVE.md) | Responsive و RTL/LTR |
| [PERFORMANCE.md](PERFORMANCE.md) | Performance و Progressive Enhancement |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | Accessibility و UX resilience |
| [THEME_API_CONTRACT.md](../THEME_API_CONTRACT.md) | قرارداد API عمومی |
| [THEME_AUTH_FLOW.md](../THEME_AUTH_FLOW.md) | Auth و Web Session |
| [THEME_TESTING.md](../THEME_TESTING.md) | Testing و CI |
| [theme-management/README.md](../theme-management/README.md) | Theme Management و محتوای قابل مدیریت |

## قواعد یکپارچه V1

1. Backend تنها مرجع authoritative برای Account، Site، Ownership، Entitlement، Billing و Authorization است.
2. WooCommerce خود `woogit.ir` مرجع داده تجاری خرید WooGit است.
3. Theme فقط API عمومی و adapterهای قراردادی را مصرف می‌کند و به internals افزونه وابسته نیست.
4. Web Session و App Session جدا هستند.
5. Session منقضی‌شده هرگز locally revive نمی‌شود.
6. Payment Return به‌تنهایی proof of payment نیست.
7. Checkout retry باید همان `Idempotency-Key` را حفظ کند.
8. Theme Management فقط presentation/content را مدیریت می‌کند.
9. Products، Orders، Sync، Conflicts، Inventory و Store Operations در Theme وجود ندارند.
10. طراحی mobile-first، RTL-first، accessible و performance-first است.
11. همه صفحات API-driven باید Loading، Empty/Pending در صورت نیاز، Error و Success state داشته باشند.
12. 400، 401، 403، 404، 409، 429 و 5xx باید طبق UX contract رفتار شوند.
13. Payment Pending، Payment Failed و Payment Success باید در UX از هم تفکیک شوند.
14. Timeout-after-success در Checkout باید Unknown باقی بماند و Failure فرض نشود.
15. هیچ credential حساس مشتری نباید در storage پایدار، HTML، log یا telemetry نگهداری شود.
16. Progressive enhancement ترجیح دارد.
17. ساختار اجرایی `theme/woogit/` طبق `THEME_ARCHITECTURE_CONTRACT.md` فریز است.
18. تغییر معماری، Source of Truth، security boundary، page inventory، navigation model یا business boundary بدون بازکردن رسمی Freeze مجاز نیست.

## Implementation Boundary

جزئیات ریز قابل tuning در implementation شامل مقدار دقیق blur، opacity، shadow، spacing خاص component، breakpointهای دقیق و tuning نهایی typography هستند؛ مشروط بر اینکه با قراردادهای Frozen در تضاد نباشند.
