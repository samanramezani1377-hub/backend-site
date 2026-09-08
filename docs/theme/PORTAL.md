# معماری Customer Portal

## هدف

Portal برای مدیریت حساب WooGit و سرویس SaaS است، نه مدیریت فروشگاه مشتری. منبع هر داده طبق `docs/THEME_DATA_OWNERSHIP.md` تعیین می‌شود.

## Navigation

```text
Customer Portal
├── Overview
├── Subscription
├── Billing
├── Payments
├── Connected Site
└── Account / Security
```

## Overview

Account context، Subscription/Entitlement و Site information از Backend authoritative نمایش داده می‌شوند. داده‌های خرید و پرداخت WooGit در صورت نمایش از WooCommerce خود `woogit.ir` می‌آیند.

## Subscription

Plan، status، dates، entitlement و actionهای مجاز مانند upgrade/renew طبق Backend authoritative نمایش داده می‌شوند.

## Billing و Payments

Billing یک صفحه ترکیبی است:

```text
Current Plan / Subscription / Entitlement → WooGit Backend
Payment Method / Payment History / Order payment details → WooCommerce خود woogit.ir
```

Payment History تراکنش‌های خرید WooGit را از WooCommerce خود `woogit.ir` می‌گیرد، از طریق adapter مناسب Theme. Backend می‌تواند در صورت نیاز projection امن ارائه کند، اما نباید Source of Truth دوم بسازد.

Checkout می‌تواند Backend orchestration داشته باشد؛ Order و Payment state در WooCommerce خود `woogit.ir` باقی می‌ماند و Entitlement توسط Backend مدیریت می‌شود.

Payment Return موفقیت پرداخت را صرفاً از query string نتیجه‌گیری نمی‌کند؛ payment/order state و Subscription/Entitlement state باید از منابع authoritative بررسی شوند.

## Connected Site

اطلاعات Account/Site از Backend می‌آید. Theme نباید مستقیماً به WooCommerce **فروشگاه مشتری** وصل شود. این ممنوعیت شامل WooCommerce خود `woogit.ir` نیست.

## Account / Security

این بخش برای مدیریت حساب WooGit و Web Session security است، نه مدیریت Credentialهای WooCommerce مشتری.

## State model

```text
Loading → Success
        ↘ Empty/Pending
        ↘ Error

Session expired → Clear temporary state → Login
```

## ممنوعیت

Portal نباید به Store Dashboard، Product Manager، Order Manager، Sync Center، Conflict Manager یا Inventory Manager فروشگاه مشتری تبدیل شود.

## Terminology

- `WooCommerce خود woogit.ir` = فروشگاه رسمی WooGit و منبع Order/Payment مربوط به خریدهای WooGit.
- `Customer WooCommerce` = فروشگاه متصل‌شده مشتری.
- `WooGit Backend` = مرجع Account، Session، Ownership، Entitlement و Authorization.
