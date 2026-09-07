# مدیریت محتوای عمومی

## Pricing Presentation

Theme فقط Presentation مربوط به Pricing را مدیریت می‌کند:

- عنوان صفحه
- Subtitle
- متن توضیحی
- Badge/Labelهای نمایشی
- متن CTA
- توضیحات عمومی
- FAQ یا توضیحات کنار Pricing

قیمت واقعی، Currency، Subscription state، Entitlement، Checkout و Billing در اختیار Backend هستند و نباید به‌صورت دستی از Theme Management جعل یا override شوند.

```text
Theme Management → Pricing Presentation
Backend → Price / Plan / Subscription / Entitlement / Billing
```

## FAQ

هر FAQ شامل:

- سؤال
- پاسخ
- وضعیت فعال/غیرفعال
- ترتیب نمایش

FAQ باید قابل افزودن، ویرایش، حذف و مرتب‌سازی باشد.

## محتوای عمومی سایر صفحات

در صورت وجود بخش‌های محتوایی دیگر مانند معرفی محصول، Documentation، Support، Privacy و Terms، متن‌ها و تصاویر Presentation آن‌ها باید تا حد لازم بدون تغییر کد قابل مدیریت باشند.

برای متن ساده از فیلد استاندارد، برای متن چندخطی از textarea و فقط در موارد لازم از Rich Text Editor استفاده شود.
