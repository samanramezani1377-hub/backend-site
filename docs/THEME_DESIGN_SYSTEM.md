# سیستم طراحی تم WooGit

> سند اجرایی Design System در [docs/theme/DESIGN-SYSTEM.md](theme/DESIGN-SYSTEM.md) نگهداری می‌شود. این فایل برای سازگاری مسیر قبلی باقی مانده است.

## خلاصه قرارداد

Theme از Liquid Glass به‌عنوان زبان بصری استفاده می‌کند، اما performance و readability اولویت بالاتری دارند.

- RTL-first و پشتیبانی صحیح LTR برای URL، email و داده فنی
- token-driven typography، spacing، color، radius، shadow و motion
- component library کوچک و reusable
- semantic HTML، keyboard navigation، visible focus و reduced motion
- stateهای Loading / Success / Error / Empty/Pending
- Landing و Customer Portal دارای visual language مشترک اما information architecture جدا هستند
- Theme نسخه وب App نیست و UI عملیاتی Products، Orders، Sync، Conflicts و Store Operations ندارد.

جزئیات کامل در سند جدید متمرکز شده است.