# مدل امنیتی WooGit

> وضعیت: V1 — Locked

## ۱. اهداف امنیتی

- Client نتواند Subscription و Entitlement را دور بزند.
- Customer Credentialها به Account یا Site دیگری افشا نشوند.
- Account بسته/غیرفعال یا Trial/Subscription منقضی نتواند به Customer Site درخواست بفرستد.
- Retry پس از Timeout باعث CREATE تکراری نشود.
- Proxy به SSRF یا Proxy عمومی تبدیل نشود.

## ۲. دو لایه اعتبار

```text
WooGit Session
    → احراز و مجوز Client در WooGit Backend

WP Username + WP Application Password
WC Consumer Key + WC Consumer Secret
    → احراز نزد Customer WordPress/WooCommerce
```

## ۳. Customer Credential Storage — ممنوع در V1

Customer Credentialها در V1 فقط Request-scoped هستند.

Backend **هرگز** آن‌ها را در DB، Vault، Cache پایدار، Log، Analytics، Telemetry، Crash Report یا Audit نگهداری نمی‌کند و در Response برنمی‌گرداند.

هیچ `site_credentials` table یا Credential Vault برای Customer Credential در V1 وجود ندارد.

## ۴. کنترل‌های قبل از Forward

```text
WooGit Session
  ↓
Account active
  ↓
Trial / Subscription valid
  ↓
Site ownership
  ↓
Entitlement
  ↓
Version / Security / Rate Limit
  ↓
Controlled Forward
```

هیچ Flag سمت Client به‌تنهایی مجوز محسوب نمی‌شود.

## ۵. Site Isolation و SSRF

`site_id` باید به Site Identity ثبت‌شده resolve و مالکیت آن نسبت به Account بررسی شود. Credential ارسالی Client نباید مقصد را تغییر دهد.

URL دلخواه Client ممنوع است و مقصد فقط از Site Identity مجاز تعیین می‌شود.

## ۶. Session Security

Session منقضی‌شده معتبر نیست و Client نمی‌تواند آن را محلی revive یا extend کند. Automatic re-login یک Authentication/Session Creation جدید است و Backend باید در آن دوباره Account، Site Ownership و Entitlement را بررسی کند.

## ۷. Idempotency و Timeout-after-success

CREATE mutationها و سایر عملیات non-idempotent لازم باید Idempotency داشته باشند. Timeout به‌تنهایی به معنی شکست عملیات نیست و retry باید همان operation identity را دنبال کند.

## ۸. Logging و Privacy

Body و Headerهای دارای Secret نباید Log شوند. Request ID برای عیب‌یابی کافی است و Secret باید پیش از logging redaction شود.

## ۹. امنیت عملیاتی

- HTTPS در Production؛
- Secretهای عملیاتی خارج از Git؛
- Backup امن؛
- Dependency scanning؛
- Audit عملیات حساس؛
- Fail closed در احراز هویت و authorization.
