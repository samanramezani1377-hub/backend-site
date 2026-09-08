# WooGit Theme UX — States

> مدل state مشترک UX در Theme.
>
> Home HTML prototype مبنای visual baseline است؛ state contract همچنان از Backend/API authority می‌آید.

## Global UX State Model

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

## Home Prototype State Rules

- Theme Toggle دارای state روشن/تاریک و persistence محلی برای **ترجیحات نمایش** است؛ این state نباید business state یا authorization را تغییر دهد.
- Mobile Menu دارای open/closed state است؛ با انتخاب route بسته می‌شود.
- CTAها در implementation باید loading/disabled state داشته باشند و هنگام درخواست فعال از duplicate submission جلوگیری کنند.
- Previewهای Home اگر در prototype مقدار نمونه نشان می‌دهند، باید در نسخه واقعی یا به داده قراردادی متصل شوند یا به‌وضوح presentation-only باقی بمانند؛ داده نمونه نباید به‌عنوان وضعیت واقعی کاربر نمایش داده شود.
- Toast/Notification برای feedback کوتاه مجاز است، اما برای خطاهای مهم نباید تنها کانال اطلاع‌رسانی باشد.
- FAQ disclosureها state باز/بسته دارند و نباید باعث از دست رفتن context یا دسترسی keyboard شوند.

## Navigation / Responsive / Accessibility

- Public و Portal navigation جدا هستند.
- Desktop: **Logo در سمت چپ بصری + Menu وسط + Login/CTA در سمت راست بصری**.
- Mobile: Hamburger + accessible Menu Sheet؛ Bottom Navigation وجود ندارد.
- Mobile Menu Sheet باید open/closed، close action، focus management و keyboard interaction قابل پیش‌بینی داشته باشد.
- Portal mobile بدون horizontal overflow و با ترتیب منطقی محتوا.
- RTL-first با logical CSS properties؛ LTR نیز بدون شکستن semantics.
- Keyboard navigation، visible focus، labels، error association، contrast، reduced motion و zoom/font scaling الزامی است.
- Glass، blur و animation باید graceful degradation داشته باشند.
- Hover نباید تنها راه انتقال affordance یا state باشد؛ touch و keyboard باید معادل قابل استفاده داشته باشند.

## Prototype Acceptance Criteria

1. هر ۱۰ صفحه اصلی High-Fidelity direction مشخص دارند.
2. Home از ساختار prototype baseline شامل Hero، Why/Features، How It Works، Product/App Preview، Pricing، FAQ، Final CTA و Footer پیروی می‌کند.
3. Home Hero typography-first و مینیمال است و Primary CTA آن «دریافت WooGit» است.
4. Home visual preview عملیاتی نیست و نباید Store Dashboard یا Products/Orders UI مشتری را القا کند.
5. Pricing دارای Free Trial + Plan اصلی برجسته است، بدون hard-code کردن business truth.
6. Login دقیقاً Site URL + Web Password است.
7. Register چهارمرحله‌ای است.
8. Portal هیچ operational WooCommerce data ندارد.
9. Subscription مدیریت کامل مورد توافق را پوشش می‌دهد.
10. Billing و Payments از نظر مفهوم و UI جدا هستند.
11. Connected Site فقط وضعیت سایت و Logout را ارائه می‌کند؛ Verify Again و Disconnect Site وجود ندارند.
12. Active Sessions نمایش داده نمی‌شود.
13. Footer Home کامل است و هیچ dead link/placeholder در production ندارد.
14. شدت Glass بر اساس page contract رعایت می‌شود.
15. Dark Mode از V1 در Design Direction لحاظ شده است.
16. Desktop header visual placement با قرارداد Logo-left / Nav-center / Actions-right سازگار است و RTL باعث جابه‌جایی ناخواسته آن نمی‌شود.
17. Mobile navigation با Hamburger + Menu Sheet پیاده‌سازی می‌شود و Bottom Navigation اضافه نمی‌شود.
18. Prototype با API/Backend authority و UX state contract تناقض ندارد.
19. هیچ implementation PHP/CSS/JS بر اساس assumption خارج از این قرارداد شروع نمی‌شود.

## Prototype / Implementation Boundary

Prototype قرارداد بصری و UX است، نه جایگزین Design System، API Contract یا Backend Contract.

جزئیات ریز مانند مقدار دقیق blur، opacity، shadow، spacing خاص، breakpointهای دقیق و tuning نهایی typography در زمان اجرای واقعی UI قابل تنظیم هستند، مشروط بر اینکه با قرارداد و Design System تناقض نداشته باشند.

پس از تأیید Prototype، مرحله بعدی اجرای Theme طبق `THEME_ARCHITECTURE_CONTRACT.md` است؛ هیچ business logic جدیدی نباید از Prototype وارد Theme شود.
