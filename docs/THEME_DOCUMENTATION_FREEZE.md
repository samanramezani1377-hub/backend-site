# WooGit Theme — Documentation Freeze

> **Status: FROZEN — V1 / Implementation Ready**
>
> Freeze date: 2026-09-08

این سند مرجع وضعیت Freeze مستندات Theme است. هدف آن این است که از این نقطه به بعد implementation بتواند بر اساس قراردادهای مشخص جلو برود، بدون اینکه در حین اجرا معماری یا business boundary به‌صورت ضمنی تغییر کند.

## 1. Frozen Scope

موارد زیر برای V1 فریز هستند:

- مرز Theme با Main Plugin/Backend؛
- مرز Theme با Customer WooCommerce؛
- Source of Truth هر domain؛
- معماری و dependency boundaries؛
- Web Session در برابر App Session؛
- Page Inventory و Portal Information Architecture؛
- Public و Portal navigation model؛
- UX state model؛
- Liquid Glass / Modern + Technological visual direction؛
- Light/Dark mode از V1؛
- component hierarchy و semantic state model؛
- Theme Management responsibility؛
- Enamad data/presentation boundary؛
- Payment Return به‌عنوان non-proof؛
- Timeout-after-success به‌عنوان Unknown و حفظ همان Idempotency-Key؛
- عدم وجود Store Operations در Theme؛
- عدم نگهداری persistent از Customer WooCommerce credentials.

## 2. Implementation-flexible Details

موارد زیر در زمان implementation قابل tuning هستند، بدون بازکردن Freeze، مشروط بر عدم تغییر contract:

- مقدار دقیق blur و opacity؛
- shadow tuning؛
- spacingهای خاص component؛
- breakpointهای دقیق؛
- typography tuning و font fallback؛
- micro-interactions و motion tuning؛
- جزئیات CSS و responsive layout که با contract سازگار باشند؛
- جزئیات presentation در Preview/Media Library.

## 3. Change Control

تغییر هرکدام از موارد Frozen فقط با این روند مجاز است:

```text
Need identified
   ↓
Documentation change proposal
   ↓
Impact review
   ↓
Explicit decision
   ↓
Update authoritative document(s)
   ↓
Close/re-freeze documentation
   ↓
Implementation
```

تغییر صرفاً برای حل یک مشکل implementation نباید باعث تغییر silent در contract شود.

## 4. Authoritative Document Map

```text
THEME.md
├── THEME_ARCHITECTURE_CONTRACT.md
├── THEME_API_CONTRACT.md
├── THEME_AUTH_FLOW.md
├── THEME_UX_FLOW.md
├── theme/README.md
│   ├── theme/ARCHITECTURE.md
│   ├── theme/PAGES.md
│   ├── theme/PORTAL.md
│   ├── theme/DESIGN-SYSTEM.md
│   ├── theme/RESPONSIVE.md
│   ├── theme/PERFORMANCE.md
│   └── theme/ACCESSIBILITY.md
└── THEME_MANAGEMENT.md
    └── theme-management/*
```

مسیرهای قدیمی مانند `THEME_DESIGN_SYSTEM.md` و `THEME_RESPONSIVE_SPEC.md` مرجع نیستند؛ مسیرهای موضوعی زیر `docs/theme/` مرجع هستند.

## 5. Backend Dependency Boundary

Theme در زمان implementation حق ندارد برای gapهای Backend business logic مستقل بسازد. اگر endpoint یا semantics لازم در Backend هنوز contract رسمی ندارد، implementation آن بخش باید متوقف بماند تا قرارداد رسمی شود.

## 6. Plugin Freeze

Main Plugin در زمان ساخت Theme باید در وضعیت Freeze باقی بماند. قواعد آن در `docs/PLUGIN_FREEZE.md` ثبت شده است.
