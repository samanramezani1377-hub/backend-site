# WooGit Theme UX — Visual Direction

> قرارداد بصری High-Fidelity Prototype. این سند مکمل `docs/THEME_UX_FLOW.md` است.
>
> **Prototype baseline:** فایل HTML ارائه‌شده برای Home به‌عنوان مبنای بصری V1 پذیرفته می‌شود. این baseline تصمیم‌های بصری را concrete می‌کند، اما هیچ business rule یا API authority جدیدی ایجاد نمی‌کند.

## Global Visual Direction

- حس کلی: Modern + Technological.
- Liquid Glass ملایم؛ Glass نباید کل UI را بپوشاند.
- رنگ غالب: Purple/Violet نرم و کنترل‌شده.
- Background: Gradient بسیار ظریف و زنده.
- Header: Glass + Sticky.
- Desktop: **Logo در سمت چپ بصری + Navigation در مرکز + Login/Primary CTA در سمت راست بصری**. این قرارداد مستقل از RTL بودن محتوای فارسی است.
- Mobile: Hamburger + Menu Sheet؛ Bottom Navigation وجود ندارد.
- Hero: مینیمال و typography-focused، با یک visual preview کنترل‌شده در کنار/زیر متن.
- Home sections: Why WooGit / Features، How It Works، Product/App Preview، Pricing، FAQ، Final CTA و Footer.
- Animation: Subtle + Professional.
- Border radius: Medium.
- Dark Mode: از V1.
- Raycast فقط reference برای کیفیت، نظم و restraint است و نباید کپی شود.

## Home High-Fidelity Baseline

Home باید از ساختار زیر پیروی کند:

```text
Sticky Glass Header
├── Brand
├── Public Navigation
└── Login + Primary CTA

Hero
├── Eyebrow / Product label
├── Typography-first headline
├── Short supporting copy
├── Primary CTA: «دریافت WooGit»
├── Secondary CTA: «مشاهده پیش‌نمایش»
└── Compact product visual preview

Why WooGit / Features
└── Compact feature grid with clear hierarchy

How It Works
└── 3-step explanatory flow

Product / App Preview
└── Non-operational visual representation of the product identity

Pricing
└── Trial + Primary Plan + Business/Contact

FAQ
└── Native expandable questions

Final CTA
└── Single clear conversion action

Footer
├── Brand / short description
├── Quick links
├── Resources
└── Account links
```

### Hero rules

- Hero باید whitespace کافی و hierarchy واضح داشته باشد.
- Visual preview فقط برای توضیح محصول و ایجاد visual grounding است؛ نباید به Store Dashboard عملیاتی تبدیل شود.
- متن Hero باید کوتاه بماند و CTA اصلی در اولین viewport قابل تشخیص باشد.
- CTA ثانویه برای preview/navigation است و نباید با CTA اصلی رقابت بصری داشته باشد.
- متن‌های امنیتی/اعتماد مانند «عدم ذخیره Credentialهای فروشگاه مشتری» می‌توانند به‌صورت supporting note نمایش داده شوند، اما نباید Hero را شلوغ کنند.

### Feature presentation

- Featureها به‌صورت کارت/گرید فشرده با icon، عنوان کوتاه و توضیح یک‌خطی نمایش داده شوند.
- Feature grid نباید به یک دیوار متن یا dashboard تبدیل شود.
- در mobile، featureها به یک ستون خوانا تبدیل شوند.

### Product / App Preview

- Preview یک **visual identity preview** است، نه operational UI.
- محتوای آن باید فقط stateها و اطلاعاتی را نشان دهد که با قرارداد Theme سازگارند؛ نمونه‌های نمایشی نباید به‌عنوان داده واقعی کاربر تعبیر شوند.
- در صورت نمایش وضعیت، از semantic status و label استفاده شود و رنگ تنها حامل معنا نباشد.

### Pricing presentation

- Trial، Plan اصلی و Business باید hierarchy بصری مشخص داشته باشند.
- Plan اصلی visually prominent باشد، اما decoration نباید اطلاعات قیمت و action را پنهان کند.
- مقدار واقعی price/currency/duration/features/eligibility از source of truth می‌آید؛ prototype فقط layout و presentation را مشخص می‌کند.

### Footer / link integrity

- تمام لینک‌های نمایش‌داده‌شده در implementation باید مقصد واقعی یا route قراردادی داشته باشند.
- Placeholderهای `#`، social link جعلی یا CTA بدون مقصد در نسخه production مجاز نیستند.
- Footer باید مکمل navigation باشد و اطلاعات را دوباره با hierarchy پایین‌تر ارائه کند، نه اینکه یک navigation دوم و شلوغ بسازد.

## Header / Navigation UX

### Desktop

- Header sticky و Glass است.
- Brand از navigation و actionها به‌صورت بصری جدا باشد.
- Navigation در مرکز قرار بگیرد و actionهای account/conversion در سمت مقابل brand قرار گیرند.
- هنگام scroll، header نباید با blur/shadow سنگین محتوای زیر خود را مختل کند.

### Mobile

- Hamburger تنها trigger اصلی navigation است.
- Menu Sheet باید شامل navigation عمومی، Login و CTA اصلی باشد.
- باز و بسته شدن Sheet باید state قابل مشاهده، focus-safe و keyboard-accessible داشته باشد.
- پس از انتخاب یک route، Sheet بسته شود.
- Bottom Navigation برای Home و Public Website وجود ندارد.

## Prototype Principle

Prototype مستقیماً به‌صورت **High-Fidelity UI** طراحی می‌شود؛ Wireframe جداگانه مرحله اجباری نیست. هدف، مشخص کردن ساختار بصری، hierarchy، component usage، navigation و responsive intent پیش از PHP/CSS/JS است.

Prototype HTML مبنای visual review است؛ هر deviation در implementation باید دلیل UX، accessibility یا performance داشته باشد و صرفاً برای سلیقه بصری انجام نشود.

## Files

- صفحات: `docs/theme-ux/PAGES.md`
- جریان‌ها: `docs/theme-ux/FLOWS.md`
- State و Accessibility: `docs/theme-ux/STATES.md`
