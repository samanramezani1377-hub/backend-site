# Performance و Progressive Enhancement Theme

## اصل

Theme باید سریع، سبک و content-first باشد. Visual effects هرگز نباید بر خوانایی یا Core Web Vitals غلبه کنند.

## Asset Strategy

- CSS را به foundation/component/page/responsive concerns تقسیم کنید.
- JavaScript را feature/page-scoped بارگذاری کنید.
- assetهای Billing/Auth/Portal روی صفحات نامرتبط لود نشوند.
- تصاویر با اندازه و فرمت مناسب و responsive ارائه شوند.
- font loading کنترل‌شده باشد و از blocking غیرضروری جلوگیری شود.
- third-party scriptها حداقلی و lazy باشند.

## Rendering

صفحات عمومی باید تا حد امکان با HTML قابل نمایش اولیه ارائه شوند. JavaScript برای interaction و progressive enhancement استفاده شود، نه اینکه کل محتوای ضروری را بدون دلیل پشت client rendering پنهان کند.

## Liquid Glass

Blur، backdrop-filter و shadow باید کنترل‌شده باشند. در محیط‌هایی که افکت‌ها پشتیبانی نمی‌شوند، surface خوانا و قابل استفاده باقی بماند.

## Responsive Performance

Responsive image، asset splitting و CSS-first breakpoints ترجیح دارند. برای تغییر layout نباید JavaScript سنگین اجرا شود.

## کیفیت قابل اندازه‌گیری

در CI و QA باید حداقل این موارد قابل اندازه‌گیری باشند:

- page load و rendering performance
- حجم CSS/JS صفحه
- assetهای unused یا global غیرضروری
- تصویرهای oversized
- accessibility/performance regressions

Threshold دقیق Performance بعد از baseline واقعی V1 تعیین می‌شود؛ در این مرحله نباید عدد ساختگی به‌عنوان قرارداد ثبت شود.
