# مشخصات واکنش‌گرایی تم WooGit

> سند متمرکز Responsive در [docs/theme/RESPONSIVE.md](theme/RESPONSIVE.md) قرار دارد. این فایل خلاصه و مسیر سازگاری قبلی است.

## قرارداد V1

- Mobile-first، RTL-first و بدون تغییر semantics محتوا
- Mobile: navigation فشرده، layout تک‌ستونه، Billing به card/scrollable list، Auth تک‌ستونه
- Tablet: دو ستون فقط وقتی خوانایی حفظ شود
- Desktop: max-width خوانا، breathing room و Portal sidebar اختیاری
- CSS logical properties برای RTL/LTR
- responsive images، controlled font loading و asset splitting
- بدون JavaScript سنگین برای breakpoint/layout
- پشتیبانی از zoom، font scaling و Reduced Motion
- breakpoint نباید منطق Backend یا معماری محتوا را تغییر دهد.
