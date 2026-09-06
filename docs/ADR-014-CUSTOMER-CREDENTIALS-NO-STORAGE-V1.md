# ADR-014 — عدم ذخیره‌سازی Customer Credentials در V1

**وضعیت:** Accepted / Locked for V1

## تصمیم

در Backend V1، Credentialهای سایت مشتری در WooGit Backend ذخیره نمی‌شوند.

Client باید Credentialهای موردنیاز برای دسترسی به Customer WordPress/WooCommerce را همراه هر Request عملیاتی که به آن دسترسی نیاز دارد ارسال کند.

```text
Client Request
 ├─ WooGit Session
 ├─ Site Context / URL
 └─ Customer Credentials
          ↓
       WooGit Backend
          ↓
 Customer WordPress/WooCommerce
```

## الزامات امنیتی

- Credential خام در DB یا Credential Vault ذخیره نشود.
- Credential فقط برای پردازش همان Request مصرف شود.
- Credential در response عادی برنگردد.
- Credential در application log، error response، audit، telemetry، analytics یا trace ثبت نشود.
- Logging باید پیش از ثبت، Secretها را redaction/masking کند.
- Background Job و Webhook نباید فرض کنند Credential ذخیره‌شده‌ای در Backend وجود دارد.

## دلیل

این مدل با هدف سبک نگه داشتن Backend V1 و کمینه کردن تغییرات Client انتخاب شده است و با مدل Client → Backend → Customer Site سازگار است.

## پیامد

هر قابلیت آینده که به دسترسی به Customer Site بدون حضور Client نیاز داشته باشد، در صورت نیاز به Credential پایدار، باید با یک تصمیم معماری جدید و صریح طراحی شود. این قابلیت در V1 نباید به Credential ذخیره‌شده متکی باشد.

## رابطه با ADR-013

این تصمیم، برای V1، بخش مربوط به مدل Credential در ADR-013 را صریح و نهایی می‌کند: Vault برای عملیات عادی V1 الزامی نیست و Credentialهای مشتری در Backend نگهداری نمی‌شوند.
