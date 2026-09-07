# Design System یکپارچه Theme WooGit

> **Status:** V1 Design Direction / UI Contract — pre-implementation
>
> این سند تصمیمات بصری و UX/UI پایه Theme را ثبت می‌کند. جزئیات ریز CSS که فقط هنگام اجرای واقعی و مشاهده UI قابل قضاوت هستند، در implementation تنظیم می‌شوند؛ اما نباید با این قرارداد در تضاد باشند.

## 1. Product UI Direction

Theme وب‌سایت رسمی و Customer Portal ووگیت است، نه UI اپلیکیشن عملیاتی WooGit.

زبان بصری باید:

- **Modern + Technological** باشد.
- از **Raycast** به‌عنوان مرجع حسی/کیفی الهام بگیرد، نه به‌عنوان طرحی برای کپی‌کردن.
- مینیمال، تمیز و دارای hierarchy واضح باشد.
- فضای خالی و typography قوی داشته باشد.
- از افکت‌های نمایشی غیرضروری دوری کند.

Landing و Portal یک هویت بصری مشترک دارند، اما information architecture و density آن‌ها یکسان نیست.

## 2. Visual Language

Theme از **Liquid Glass ملایم** به‌عنوان یکی از زبان‌های بصری اصلی استفاده می‌کند؛ Glass باید محسوس اما کنترل‌شده باشد.

اصل اولویت:

1. خوانایی
2. Performance
3. hierarchy و usability
4. visual polish
5. decorative effects

افکت نباید برای رسیدن به ظاهر Glass، usability یا performance را خراب کند.

### Background

- پایه روشن نرم نزدیک `#EFF1F7`.
- از **Gradient بسیار ظریف و زنده** استفاده می‌شود.
- Gradient باید ambient و کم‌کنتراست باشد، نه یک پس‌زمینه جیغ یا distracting.
- در Dark Mode معادل آن باید عمیق و کنترل‌شده باشد، نه مشکی مطلق همراه با Glow سنگین.

### Color Direction

- رنگ هویتی اصلی: **Purple / Violet ملایم و غیر جیغ**.
- Accentهای ambient: Mint، Peach، Lavender، Sky.
- Gradient تأکیدی بنفش → صورتی می‌تواند استفاده شود، اما ظریف و کنترل‌شده.
- Green برای live/healthy.
- Orange برای warning/urgent.
- Semantic error/success/info/warning باید از رنگ‌های semantic مستقل استفاده کنند.
- رنگ‌های accent نباید hierarchy متن یا contrast را تضعیف کنند.

### Glass

- glass surface نیمه‌شفاف.
- blur/haze کنترل‌شده.
- border و highlight ظریف.
- shadow نرم.
- Liquid Glass در تمام صفحه به‌صورت افراطی استفاده نمی‌شود.
- Portal نسبت به Landing باید Glass کمتری برای جلوگیری از کاهش خوانایی و افزایش visual noise داشته باشد.

## 3. Theme Modes

Theme از **Light و Dark Mode از V1** پشتیبانی می‌کند.

هر دو Mode باید از یک semantic token system استفاده کنند؛ component نباید رنگ مخصوص یک Mode را به‌صورت hardcoded داخل خود تعریف کند.

تغییر Mode نباید ساختار محتوا، API، business logic یا navigation را تغییر دهد.

## 4. Design Tokens

قبل از اجرای componentها، مقادیر مشترک باید به‌صورت token تعریف شوند تا یک تصمیم در کل UI یکسان بماند.

Token categories:

- color
- typography scale
- spacing
- radius
- border
- shadow
- blur
- glass opacity
- elevation
- motion duration/easing
- focus ring
- breakpoint

در این مرحله **اصل و hierarchy** این tokenها قرارداد است؛ مقدارهای ریز Shadow/Blur/Opacity یا spacingهای خاص component در implementation و با بررسی UI تنظیم می‌شوند.

Pipeline اجباری:

```text
Design Decision
      ↓
Design Token
      ↓
Component Contract
      ↓
CSS Variable
      ↓
Implementation
```

مقدارهای ثابت تکرارشونده نباید در componentهای مختلف hardcode شوند.

### Typography

Typography باید hierarchy مشخص برای موارد زیر داشته باشد:

- Display
- H1
- H2
- H3
- Body Large
- Body
- Body Small
- Caption
- Label

Font فارسی باید خوانا، مدرن، حرفه‌ای و مناسب UI باشد و در Light/Dark و اندازه‌های مختلف کیفیت خود را حفظ کند. انتخاب نهایی font در implementation انجام می‌شود و نباید به فونتی وابسته باشد که بدون fallback قابل استفاده نیست.

### Spacing

Spacing باید یک scale مشترک و قابل پیش‌بینی داشته باشد و فاصله‌های componentها از همان scale استفاده کنند. فاصله‌های خاص فقط زمانی مجازند که نیاز واقعی layout داشته باشند.

### Radius

Radius کلی **متوسط** است:

- نه sharp و خشک.
- نه بیش از حد rounded.
- سطوح بزرگ، controlها و عناصر interactive باید از hierarchy شعاعی یکسان پیروی کنند.

### Shadow / Elevation

Shadowها باید نرم و کنترل‌شده باشند و بر اساس elevation hierarchy استفاده شوند؛ از shadowهای سنگین و متعدد در Liquid Glass اجتناب شود.

### Blur / Glass Opacity

Blur و opacity باید به‌صورت token مدیریت شوند. هیچ component نباید صرفاً برای زیباتر شدن blur یا opacity بالایی اضافه کند.

### Semantic Colors

رنگ‌ها باید بر اساس semantic meaning مصرف شوند، نه نام ظاهری رنگ:

- brand
- neutral
- success
- warning
- danger/error
- info
- live/healthy
- pending
- disabled
- surface/background
- text/secondary/muted
- border/focus

## 5. Component Library

کتابخانه داخلی کوچک و reusable شامل این primitives/components است:

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
- Responsive Billing / Payment Table

Component نباید business decision بگیرد؛ فقط presentation و interaction عمومی را مدیریت کند.

### Button Direction

Buttonها باید حداقل این hierarchy را داشته باشند:

- Primary
- Secondary
- Tertiary/Ghost
- Icon Button
- در صورت نیاز Danger

هر variant باید stateهای قابل پیش‌بینی داشته باشد:

- default
- hover
- active/pressed
- focus
- disabled
- loading

CTA اصلی سایت باید از Primary hierarchy استفاده کند.

### Input Direction

Input / Select / Textarea باید stateهای زیر را به‌صورت واضح و قابل دسترس داشته باشند:

- default
- hover
- focus
- invalid/error
- success در موارد لازم
- disabled
- read-only در موارد لازم

خطا باید علاوه بر رنگ، با متن/ساختار مناسب قابل تشخیص باشد.

### Card Direction

Cardها باید variantهای مشخص و محدود داشته باشند، از جمله:

- Glass
- Flat/standard
- Elevated در موارد لازم
- Interactive در موارد لازم
- Status-oriented در موارد لازم

Card صرفاً برای decoration نباشد و نباید اطلاعات را پشت افکت Glass پنهان کند.

### Badge / Status

Badge و Status باید semantic باشند و حداقل این معناها را پوشش دهند:

- neutral
- success/live
- warning/pending
- danger/error
- info
- brand

رنگ تنها روش انتقال وضعیت نباشد؛ متن/label یا icon مناسب نیز در موارد لازم استفاده شود.

## 6. Header & Navigation

### Desktop

Header باید:

- Glass باشد.
- در بالای صفحه **sticky** بماند و هنگام scroll از viewport خارج نشود.
- ساختار اصلی آن:

```text
Logo (left) + Main Navigation (center) + Login / CTA (right)
```

CTA اصلی می‌تواند «دریافت WooGit» باشد.

Header نباید با shadow/blur سنگین خوانایی محتوا را کاهش دهد.

### Mobile

Navigation موبایل به‌صورت:

**Hamburger + Menu Sheet**

است.

Bottom Navigation برای سایت اصلی استفاده نمی‌شود؛ Bottom Navigation برای محیط‌های application-like مناسب‌تر است و با ساختار Marketing Site + Customer Portal این Theme هم‌خوانی کمتری دارد.

با فعال‌شدن Hamburger، یک Menu Sheet تمیز و Glass باز می‌شود و شامل موارد لازم مانند:

- لینک‌های اصلی سایت
- Login
- CTA «دریافت WooGit»

است.

Sheet باید keyboard-accessible، focus-safe و قابل بستن با close action مناسب باشد.

## 7. Landing / Hero

Hero صفحه اصلی باید:

- **خیلی مینیمال** باشد.
- **Typography محور** باشد.
- از شلوغی تصویری پرهیز کند.
- hierarchy واضح داشته باشد.
- CTA اصلی آن **«دریافت WooGit»** باشد.

Hero نباید برای نمایش قابلیت‌های عملیاتی اپلیکیشن به یک Dashboard کامل تبدیل شود.

## 8. Pricing

Pricing باید از **Glass Cards** استفاده کند، اما Glass ملایم بماند.

- Plan اصلی باید visually prominent باشد.
- Plan رایگان/Trial موجود است و **مدت محدود برای تست** دارد.
- تفاوت Plan اصلی و Trial باید از نظر hierarchy کاملاً واضح باشد.
- قیمت، currency، subscription، entitlement و checkout business truth نیستند و از Backend می‌آیند.
- UI فقط presentation قرارداد Backend را نمایش می‌دهد.

## 9. Customer Portal

Portal باید:

> **همان Design System را داشته باشد، اما کاربردی‌تر و ساده‌تر از Landing باشد.**

بنابراین:

- Typography مشترک.
- Color system مشترک.
- Button/Card/Status primitives مشترک.
- همان هویت Liquid Glass.
- Glass و decoration کمتر از Landing.
- information density و readability بالاتر.
- تمرکز روی اطلاعات و actionهای واقعی مشتری.

Portal نباید یک طراحی کاملاً جدا از Landing باشد و نباید به یک **Glass-heavy Dashboard** تبدیل شود.

Portal می‌تواند layout کاربردی مانند navigation/sidebar یا بخش‌بندی داشبوردی داشته باشد، اما business architecture آن از قراردادهای Backend پیروی می‌کند.

## 10. Shared vs Page-specific

Landing و Portal باید از primitiveهای مشترک استفاده کنند، اما componentهای page-specific نباید صرفاً برای اشتراک ظاهری global شوند.

Shared component یعنی رفتار و visual contract مشترک؛ نه اینکه همه صفحات مجبور به layout یکسان باشند.

## 11. Responsive Direction

Theme باید mobile-first و RTL-first باشد.

- Mobile: navigation فشرده و accessible، layout عمدتاً یک‌ستونه، billing/payment با layout مناسب صفحه کوچک.
- Tablet: دو ستون فقط وقتی خوانایی حفظ شود.
- Desktop: max-width خوانا و فضای تنفس مناسب.
- Portal می‌تواند در desktop navigation/sidebar داشته باشد.
- CSS باید از logical properties برای RTL/LTR استفاده کند.
- responsive images و asset loading باید کنترل‌شده باشند.
- breakpointها باید در token/contract مشترک تعریف شوند.
- layout نباید با JavaScript سنگین مدیریت شود.

## 12. Accessibility

- semantic HTML
- visible focus
- keyboard navigation
- real labels
- field-level errors
- adequate contrast
- reduced motion
- browser zoom/font scaling
- touch targets مناسب
- focus order منطقی
- Statusها نباید فقط با رنگ قابل تشخیص باشند.

Focus باید یک visual contract مشترک داشته باشد و با backgroundهای Glass و هر دو Theme Mode قابل مشاهده بماند.

## 13. Motion

شدت Animation: **ظریف و حرفه‌ای**.

Animation باید:

- سریع و هدفمند باشد.
- برای feedback و hierarchy استفاده شود، نه decoration دائمی.
- easing و duration مشترک داشته باشد.
- از animationهای سنگین یا continuous motion غیرضروری اجتناب کند.

در `prefers-reduced-motion` حرکت غیرضروری باید حذف یا کاهش یابد.

## 14. Performance Rules

Liquid Glass نباید بهانه‌ای برای:

- تصویرهای سنگین
- blurهای بی‌رویه
- shadowهای متعدد
- JavaScript سراسری غیرضروری
- assetهای unused
- animationهای دائمی و پرهزینه

باشد.

Graceful degradation برای Glass/Blur/Animation الزامی است.

## 15. UI State Principles

Componentها باید حالت‌های پایه را به‌صورت یکسان نمایش دهند:

- Loading
- Success
- Empty
- Pending
- Error
- Disabled
- Active/Selected
- Focus

UI نباید خودش business truth را تعیین کند؛ وضعیت‌هایی مانند subscription، entitlement، payment و account از Backend می‌آیند.

## 16. Design Governance

- هیچ componentی نباید تصمیم business بگیرد.
- هیچ مقدار تکرارشونده‌ای نباید بدون دلیل خارج از token system تعریف شود.
- page-specific styling نباید design system را دور بزند.
- تغییر یک token مشترک باید اثر آن روی Landing و Portal بررسی شود.
- Light/Dark باید هر دو قبل از نهایی‌شدن componentها بررسی شوند.
- جزئیات بصری implementation باید در محدوده این سند باقی بمانند و برای بهبود UI قابل تنظیم باشند.

## 17. Definition of Done — Design/UI

قبل از نهایی‌شدن UI هر component/page:

- با Visual Direction سازگار است.
- Modern/Technological و Raycast-inspired بودن حس می‌شود، بدون کپی‌برداری.
- Liquid Glass ملایم باقی مانده است.
- Purple/Violet جیغ نیست.
- Light و Dark Mode هر دو قابل استفاده‌اند.
- Responsive behavior مشخص است.
- Focus/Keyboard/Contrast بررسی شده است.
- Animation ظریف و قابل کاهش است.
- Performance با افکت‌های بصری قربانی نشده است.
- Landing و Portal هویت مشترک ولی density مناسب خود را دارند.
- هیچ business logic داخل component بصری قرار نگرفته است.
