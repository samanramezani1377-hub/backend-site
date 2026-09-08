# WooGit Theme UX — Pages

> قرارداد UX صفحات اصلی Theme. این سند مکمل `docs/THEME_UX_FLOW.md` است.

## Page Map

### Home

- Header
- Minimal Hero
- Primary CTA: «دریافت WooGit»
- WooGit App Preview: Products / Orders
- Features
- How It Works
- Pricing
- Why WooGit / Benefits
- FAQ
- Final CTA
- Full Footer

App Preview باید واقعی و قابل‌فهم به نظر برسد، اما Hero نباید شلوغ شود؛ value proposition و CTA اولویت بصری دارند.

Footer شامل Logo، short description، links، contact، social، Privacy، Terms، Documentation، Support، eNAMAD و Copyright است.

### Pricing

Glass Cards؛ Plan اصلی برجسته؛ Free Trial با برجستگی ترکیبی و واضح؛ Planهای احتمالی آینده فقط در صورت نیاز به‌صورت muted و بدون جعل قیمت/مشخصات. قیمت، Currency، مدت و eligibility از Backend می‌آیند.

### Login

```text
Welcome back

[ Site URL ]
[ Password ]

[ ورود ]

فراموشی رمز عبور؟
```

قرارداد V1 بر Site URL + Web Password است، نه Email/Password عمومی. Contact Email برای ارتباط با مشتری است و credential/identifier ورود محسوب نمی‌شود.

### Register

Wizard چهارمرحله‌ای:

```text
1. فروشگاه
   ↓
2. اتصال
   ↓
3. حساب
   ↓
4. تأیید
```

Progress، validation و recovery واضح هستند. Secretهای WordPress/WooCommerce هرگز در browser persistence ذخیره نمی‌شوند.

### Portal Overview

Dashboard عملی و ساده شامل Welcome/account context، Subscription summary، Connected Site، Current Plan، Recent Billing و Quick Actions.

Products، Orders، Inventory و سایر عملیات WooCommerce نباید در Portal نمایش داده شوند.

### Subscription

مدیریت کامل: Current Plan، Status، Start Date، End Date، Trial، Upgrade، Renew، Cancel و Change Plan. فقط actionهای مجاز Backend نمایش داده شوند.

### Billing

```text
Billing

Current Plan
Next Billing
Payment Method
```

Billing در UI یعنی وضعیت اشتراک و وضعیت مالی فعلی؛ تاریخچه تراکنش‌ها در صفحه Payments نمایش داده می‌شود.

### Payments

صفحه‌ای مستقل از Billing برای transaction/payment detail و history با statusهای canonical مانند Pending، Paid و Failed. تفاوت Billing و Payments باید در navigation، title و visual hierarchy کاملاً واضح باشد.

### Connected Site

```text
Connected Site

● Connected
example.com

Site Status
Connection Status
Last verification

[ Logout ]
```

`Logout` در این صفحه به خروج از Web Session و Portal اشاره دارد و باید از `POST /web/logout` استفاده کند. Logout به معنی حذف Account یا تغییر مالکیت Site نیست. Theme مستقیماً به WooCommerce مشتری متصل نمی‌شود.

### Account / Security

```text
Account / Security
├── Site URL
├── Contact Email
├── Password
└── Security
```

Site URL اطلاعات اصلی حساب برای ورود به Portal و Login است. Contact Email فقط برای ارتباط با مشتری است و برای Login یا جایگزینی Site URL در احراز حساب استفاده نمی‌شود.

Security شامل Change Password، Logout و Logout All Sessions است. Active Sessions در V1 نمایش داده نمی‌شود.

## Page-specific Glass Intensity

| صفحه | شدت Glass |
|---|---|
| Home | محسوس‌تر، اما کنترل‌شده |
| Pricing | Glass Cards |
| Login | محدود و بسیار تمیز |
| Register | محدود و بسیار تمیز |
| Portal Overview | ملایم‌تر و information-first |
| Subscription | ملایم و کاربردی |
| Billing | بسیار کنترل‌شده |
| Payments | بسیار کنترل‌شده |
| Connected Site | ملایم و status-focused |
| Account/Security | ملایم و کاربردی |

Portal و صفحات داخلی نسبت به Landing تزئینات کمتری دارند و خوانایی/تراکم اطلاعات اولویت بالاتری دارد.
