# WooGit Theme UX — Flows

> قرارداد جریان‌های اصلی کاربر در Theme. Backend authority است.

## Login

```text
Login → Site URL + Web Password → Loading → POST /web/login
Success → Web Session → Portal Overview
Error   → Login Error State
```

## Register / Web Bootstrap

```text
Register Wizard
  → Client validation
  → POST /account/web-bootstrap
  → Validate + verify site
  → Resolve/Create Account + Site
  → Ownership validation
  → Create Web Credential
  → Create Web Session
  → Portal
```

`App Session` نباید جعل شود و `/sites/verify` App-only نباید توسط Theme دور زده شود.

## Portal Entry

```text
Portal URL → GET /web/me
Valid → Portal
401   → Login / new Web Session
```

وجود token محلی به‌تنهایی authenticated بودن را ثابت نمی‌کند.

## Subscription

Backend authoritative status را نمایش می‌دهد؛ Theme نمی‌تواند Premium/Active را جعل کند. Upgrade/Renew/Cancel/Change Plan فقط طبق capability/eligibility Backend.

## Checkout / Billing

```text
Pricing / Subscription
  → Select Plan
  → Backend eligibility
  → POST /billing/checkout + Idempotency-Key
  → Gateway
  → Payment Return
  → GET /billing/status
```

Timeout-after-success باید به Unknown → re-query تبدیل شود. برای همان logical operation همان Idempotency-Key حفظ می‌شود و Checkout دوم با key جدید ممنوع است مگر عملیات جدید صریحاً آغاز شود.

## Payment Return

`success=1` یا query مشابه proof نیست. ابتدا Checking/Pending UI و سپس نتیجه authoritative Backend.

```text
Payment Return → Checking → GET /billing/status
Paid/Success → Success
Pending      → bounded re-query / Pending
Failed       → Failure + safe retry
```

Polling باید bounded و rate-limit-aware باشد.

## Payments / History

```text
Payments → GET /web/billing/history
records → list
no records → Empty
401 → Re-auth
403 → Forbidden
5xx → Error + Retry
```

## Connected Site

فقط داده‌ای نمایش داده می‌شود که Backend برای Portal منتشر کند؛ اتصال مستقیم به WooCommerce مشتری ممنوع است. در این بخش action جداگانه‌ای برای Verify Again یا Disconnect Site وجود ندارد؛ خروج از Portal با `Logout` انجام می‌شود.

## Password Change / Logout

پس از تغییر موفق رمز، Backend همه Web Sessionها را revoke می‌کند؛ Theme state محلی را پاک کرده و Login می‌خواهد. Logout نیز با `POST /web/logout` انجام می‌شود، Web Session را در Backend revoke می‌کند و state موقت محلی را پاک می‌کند.
