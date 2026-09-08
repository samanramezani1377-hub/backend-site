# مدیریت صفحه اصلی

## Hero

مدیر باید بتواند موارد زیر را تغییر دهد:

- Hero Image / Tablet Image
- Mobile Hero Image
- عنوان اصلی
- توضیح
- متن و لینک CTA اصلی
- متن و لینک CTA دوم

تصاویر فقط از WordPress Media Library انتخاب می‌شوند و Theme فقط Media ID را نگه می‌دارد.

## Features

هر Feature شامل:

- عنوان
- توضیح
- Icon یا Image
- وضعیت فعال/غیرفعال
- ترتیب نمایش

مدیر می‌تواند Feature را اضافه، ویرایش، حذف و مرتب کند. داده با schema نرمال‌شده ذخیره می‌شود و مقدار قدیمی `text` برای سازگاری ورودی به `description` تبدیل می‌شود.

## How It Works

هر Step شامل:

- شماره/ترتیب نمایش
- عنوان
- توضیح
- تصویر یا Illustration
- لینک اختیاری
- وضعیت فعال/غیرفعال

Stepها قابل افزودن، حذف، ویرایش، فعال/غیرفعال و مرتب‌سازی هستند.

## Section Management

وضعیت نمایش این Sectionهای Home قابل مدیریت است:

- Features
- How It Works
- Pricing
- Product/App
- FAQ

محتوای معرفی Sectionها نیز از Theme Management قابل ویرایش است. داده تجاری Pricing شامل price، currency، duration، limits و entitlement عمداً در Theme ذخیره نمی‌شود و از Backend/Commerce می‌آید.

هدف این است که تغییر محتوای اصلی و وضعیت نمایش سایت نیازمند تغییر PHP/HTML نباشد.
