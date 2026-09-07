# Design System یکپارچه Theme WooGit

## Visual Language

Theme از Liquid Glass به‌عنوان زبان بصری استفاده می‌کند، اما performance و readability بر افکت‌ها اولویت دارند.

- پایه روشن نرم نزدیک `#EFF1F7`
- ambient accents: Mint، Peach، Lavender، Sky
- glass surfaces نیمه‌شفاف
- blur/haze کنترل‌شده
- border و highlight ظریف
- shadow نرم
- gradient تأکیدی بنفش به صورتی
- green برای live/healthy و orange برای urgent/warning
- hierarchy واضح برای متن

## Design Tokens

قبل از اجرای UI، tokenهای مشترک برای این موارد تعریف شوند:

- color
- typography scale
- spacing
- radius
- border
- shadow
- blur
- elevation
- motion duration/easing
- focus ring
- breakpoint

مقدارهای ثابت باید در componentهای مختلف تکرار نشوند.

## Component Library

کتابخانه داخلی کوچک و reusable:

- Glass Surface
- Header / Navigation
- Glass Card
- Primary / Secondary Button
- Icon Button
- Input / Select / Textarea
- Search
- Badge / Status
- List / List Item
- Section
- Empty / Error / Loading / Skeleton
- Dialog / Sheet
- Toast / Notification
- Responsive Billing / Payment table

Component نباید business decision بگیرد؛ فقط presentation و interaction عمومی را مدیریت کند.

## Shared vs Page-specific

Landing و Portal باید از همین primitives استفاده کنند، اما componentهای page-specific نباید صرفاً برای اشتراک ظاهری در کل سایت global شوند.

## Accessibility

- semantic HTML
- visible focus
- keyboard navigation
- real labels
- field-level errors
- adequate contrast
- reduced motion
- browser zoom/font scaling
- touch targets مناسب

## Motion

Glass effects، blur و animation باید graceful degradation داشته باشند. در `prefers-reduced-motion` حرکت غیرضروری حذف یا کاهش یابد.

## Performance rule

Liquid Glass نباید بهانه‌ای برای تصویرهای سنگین، blurهای بی‌رویه، JavaScript سراسری یا assetهای unused شود.
