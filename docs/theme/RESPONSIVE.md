# Responsive و RTL/LTR Theme WooGit

## اصول

Theme از mobile-first و RTL-first شروع می‌کند و تا desktop بدون تغییر در content architecture توسعه می‌یابد.

### Mobile
- navigation فشرده و accessible
- layout تک‌ستونه برای محتوای اصلی
- Billing/Payment table به card یا list قابل اسکرول تبدیل شود
- CTAها touch-friendly
- Auth forms تک‌ستونه

### Tablet
- دو ستون فقط در صورت حفظ خوانایی
- grid فرم‌ها و cardها واکنش‌گرا

### Desktop
- max-width خوانا
- breathing room کافی
- Portal sidebar در صورت مفید بودن
- Landing چندستونه در صورت حفظ hierarchy
- Pricing بدون overflow

## Direction

از CSS logical properties مانند `margin-inline`، `padding-inline` و `inset-inline` استفاده شود. URL، email، code و داده‌های فنی direction مناسب خود را حفظ کنند.

## Performance

Responsive image، CSS-first breakpoint و asset splitting ترجیح دارند. برای layout نباید JavaScript سنگین لازم باشد.

## QA Matrix

حداقل mobile کوچک/بزرگ، tablet، laptop، wide desktop، zoom بالا، font scaling و Reduced Motion بررسی شوند.
