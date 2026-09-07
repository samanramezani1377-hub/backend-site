# راهبرد تست تم WooGit

## ۱. هدف

تست‌های Theme باید از تست‌های Plugin/Backend جدا باشند. تست‌های فعلی Backend در `tests/plugin/` قرار دارند و تست‌های مخصوص Theme باید در `tests/theme/` قرار بگیرند.

## ۲. تست‌های واحد

- منطق نمایش و تبدیل داده API
- اعتبارسنجی فرم‌ها
- نگاشت کدهای خطا به پیام UI
- وضعیت‌های Loading/Success/Error
- ساخت Idempotency Key برای Checkout
- پاک‌سازی نشست پس از خروج یا انقضا

## ۳. تست‌های یکپارچه

- Login با Site URL + Password
- Logout و revoke نشست
- دریافت اطلاعات Account
- دریافت Billing و Payment History
- تغییر رمز و ورود مجدد
- Checkout و بازگشت از درگاه
- بررسی دوباره وضعیت پس از Payment Return
- رفتار با Session منقضی‌شده
- رفتار با `401`، `403`، `404`، `409`، `429` و `5xx`

## ۴. تست امنیتی

باید اطمینان حاصل شود که:

- Credential مشتری در DOM، HTML، log و storage باقی نمی‌ماند؛
- نشست در URL قرار نمی‌گیرد؛
- Account/Site از ورودی کاربر قابل جعل نیست؛
- entitlement در Theme جعل نمی‌شود؛
- redirect پرداخت به مقصد دلخواه تبدیل نمی‌شود؛
- XSS و CSRF طبق مدل نشست انتخاب‌شده پوشش داده می‌شوند؛
- خطاهای Backend اطلاعات حساس را افشا نمی‌کنند.

## ۵. تست بصری

صفحات اصلی در viewportهای موبایل، تبلت و دسکتاپ بررسی شوند. Liquid Glass باید بدون افت خوانایی یا عملکرد حفظ شود.

## ۶. تست دسترس‌پذیری

- keyboard-only
- screen reader labels
- focus order
- contrast
- reduced motion
- zoom

## ۷. CI

Theme CI باید مستقل از Plugin باشد و حداقل lint، syntax، تست‌های واحد/یکپارچه، تست امنیتی و build قابل نصب Theme را اجرا کند. در صورت شکست هر تست الزامی، نتیجه نهایی CI باید قرمز باشد، اما سایر تست‌های مستقل تا حد ممکن اجرا شوند تا همه خطاها یکجا گزارش شوند.

## ۸. اصل عدم جعل

تست‌ها نباید با mock نتیجه موفقیت Billing، Entitlement یا Authentication را جعل کنند مگر در تست واحدی که هدف آن فقط منطق UI باشد. مسیرهای قرارداد واقعی باید با Backend تست شوند.
