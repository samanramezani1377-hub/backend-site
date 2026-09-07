# معماری Customer Portal

## هدف

Portal برای مدیریت حساب WooGit و سرویس SaaS است، نه مدیریت فروشگاه WooCommerce.

## Navigation

```text
Customer Portal
├── Overview
├── Subscription
├── Billing
├── Payments
├── Connected Site
├── Security
└── Account
```

Navigation Portal از Navigation سایت عمومی جداست؛ فقط visual language و component system مشترک هستند.

## Overview

نمای کلی باید وضعیت authoritative حساب را از Backend نمایش دهد: وضعیت اشتراک، پلن، سایت متصل و موارد ضروری حساب. UI نباید state محلی را به جای Backend مرجع قرار دهد.

## Subscription

نمایش plan، status، start/end/renewal dates، entitlement و actionهای مجاز مانند upgrade/renew. وضعیت‌های Trial، Active، Expired و Cancelled باید طبق قرارداد Backend نمایش داده شوند.

## Billing و Payments

Billing وضعیت سرویس و عملیات مرتبط را نمایش می‌دهد. Payment History تراکنش‌های منتشرشده توسط Backend را نمایش می‌دهد. Checkout فقط از API قراردادی استفاده می‌کند.

Payment Return موفقیت پرداخت را ثابت نمی‌کند؛ پس از بازگشت، وضعیت باید دوباره از Backend استعلام شود.

## Connected Site

فقط اطلاعاتی که Backend برای نمایش منتشر می‌کند در Portal نمایش داده شود. Theme نباید از Portal مستقیماً به WooCommerce سایت مشتری وصل شود.

## Security

این بخش برای Web Session و Account Security است، نه مدیریت Credentialهای WooCommerce. تغییر رمز و logout طبق قرارداد Backend انجام می‌شوند. Session منقضی‌شده باید پاک و کاربر به Login هدایت شود.

## Account

اطلاعات حساب و contact email از API رسمی Backend مدیریت می‌شوند. تغییرات موفق باید نتیجه Backend را مبنا قرار دهند.

## State model

هر صفحه Portal باید دست‌کم این stateها را در صورت مرتبط بودن داشته باشد:

```text
Loading → Success
        ↘ Empty/Pending
        ↘ Error

Session expired → Clear temporary state → Login
```

## ممنوعیت

Portal نباید به این‌ها تبدیل شود:

- Store Dashboard
- Product Manager
- Order Manager
- Sync Center
- Conflict Manager
- Inventory Manager
