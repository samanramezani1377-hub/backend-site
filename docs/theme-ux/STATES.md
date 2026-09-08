# WooGit Theme UX — States

> مدل state مشترک UX در Theme.

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

## Navigation / Responsive / Accessibility

- Public و Portal navigation جدا هستند.
- Desktop: Logo چپ + Menu وسط + Login/CTA راست.
- Mobile: Hamburger + accessible Menu Sheet؛ Bottom Navigation وجود ندارد.
- Portal mobile بدون horizontal overflow و با ترتیب منطقی محتوا.
- RTL-first با logical CSS properties؛ LTR نیز بدون شکستن semantics.
- Keyboard navigation، visible focus، labels، error association، contrast، reduced motion و zoom/font scaling الزامی است.
- Glass، blur و animation باید graceful degradation داشته باشند.

## Prototype Acceptance Criteria

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

## Prototype / Implementation Boundary

Prototype قرارداد بصری و UX است، نه جایگزین Design System، API Contract یا Backend Contract.

جزئیات ریز مانند مقدار دقیق blur، opacity، shadow، spacing خاص، breakpointهای دقیق و tuning نهایی typography در زمان اجرای واقعی UI قابل تنظیم هستند، مشروط بر اینکه با قرارداد و Design System تناقض نداشته باشند.

پس از تأیید Prototype، مرحله بعدی اجرای Theme طبق `THEME_ARCHITECTURE_CONTRACT.md` است؛ هیچ business logic جدیدی نباید از Prototype وارد Theme شود.
