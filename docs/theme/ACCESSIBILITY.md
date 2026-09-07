# Accessibility Theme WooGit

## اصول V1

Accessibility باید بخشی از architecture باشد، نه polish بعد از پیاده‌سازی.

- semantic HTML و heading hierarchy صحیح
- keyboard-only operation برای همه actionهای اصلی
- focus state واضح و focus order منطقی
- label واقعی برای کنترل‌های فرم
- خطای هر فیلد با خود فیلد مرتبط شود
- status/loading/error با روش قابل درک برای assistive technology اعلام شود
- contrast کافی برای متن، کنترل و focus
- touch target مناسب در موبایل
- پشتیبانی از browser zoom و font scaling
- `prefers-reduced-motion`

## RTL/LTR

صفحات فارسی RTL-first هستند، اما URL، email، code، key و داده‌های فنی باید direction مناسب خود را حفظ کنند. از CSS logical properties استفاده شود.

## Forms

Login، registration، password و billing forms باید:

1. label مشخص داشته باشند؛
2. validation قابل فهم داشته باشند؛
3. خطا را نزدیک کنترل مرتبط نشان دهند؛
4. هنگام loading وضعیت disabled/processing واضح داشته باشند؛
5. اطلاعات حساس را بی‌دلیل در صفحه یا DOM قابل مشاهده نکنند.

## Dialog و Navigation

Dialog/Sheet باید keyboard trap صحیح، escape behavior و focus return داشته باشد. Mobile navigation باید با keyboard و screen reader قابل استفاده باشد.

## Visual Effects

Liquid Glass باید decorative باشد، نه تنها روش انتقال معنا. وضعیت‌ها نباید فقط با رنگ قابل تشخیص باشند.

## QA

Accessibility باید در تست‌های unit/integration، visual QA و CI بررسی شود و regression آن blocker محسوب شود اگر یک قابلیت اصلی را غیرقابل استفاده کند.
