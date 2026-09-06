# مدل امنیتی WooGit

> وضعیت: **V1 — Locked / Enforcement implemented**

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

Customer Credentialها در V1 فقط Request-scoped هستند. Backend آن‌ها را در DB، Vault، Cache پایدار، Log، Analytics، Telemetry، Crash Report یا Response نگهداری/برنمی‌گرداند.

## ۴. کنترل‌های قبل از Forward

```text
App Version Gate
  ↓
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
Rate Limit / Security / Body Size
  ↓
Controlled Forward
```

هیچ Flag سمت Client به‌تنهایی مجوز محسوب نمی‌شود.

## ۵. Site Isolation و SSRF

`site_id` فقط از Session به Site ثبت‌شده resolve می‌شود و `getOwned(account_id, site_id)` باید موفق شود. Credential ارسالی Client مقصد را تغییر نمی‌دهد.

Site host در DB یک uniqueness boundary سراسری است؛ یک host نمی‌تواند همزمان مالک چند Account باشد. مقصد فقط HTTPS است و قبل از outbound، DNS A/AAAA برای IPهای private/reserved بررسی می‌شود؛ سپس `wp_safe_remote_request` نیز استفاده می‌شود.

## ۶. Session Security

Session منقضی‌شده یا revoked معتبر نیست و Client نمی‌تواند آن را محلی revive/extend کند. ایجاد Session جدید دوباره Account، Site Ownership و Entitlement را بررسی می‌کند. Sessionهای منقضی در cleanup روزانه حذف می‌شوند.

## ۷. Idempotency و Timeout-after-success

تمام mutationهای `POST/PUT/PATCH/DELETE` در proxy به `Idempotency-Key` نیاز دارند. Idempotency state مستقل و authoritative است:

```text
pending → succeeded
        → failed
        → unknown
```

اگر upstream timeout شود، عملیات و idempotency هر دو `unknown` می‌شوند. Retry با همان key **هرگز mutation را دوباره به Customer Site ارسال نمی‌کند**؛ همان `operation_id` برگردانده می‌شود و پاسخ `operation_status_unknown` با `requires_reconciliation=true` داده می‌شود. این رفتار عمداً fail-closed است تا در حالت timeout-after-success، CREATE تکراری رخ ندهد.

برای عملیات `unknown` باید reconciliation معتبر خارج از retry کور انجام شود؛ Backend نباید صرفاً برای رفع unknown، همان CREATE را دوباره forward کند.

## ۸. Rate Limit و Request Size

- `/sites/verify`: حداکثر ۱۰ درخواست در دقیقه برای IP.
- `/forward`: حداکثر ۱۲۰ درخواست در دقیقه برای Account+Site.
- `/operations/{id}`: حداکثر ۱۲۰ درخواست در دقیقه برای Account+Site.
- پاسخ محدودیت `429` و `Retry-After` دارد.
- body عادی حداکثر ۵ MiB و media حداکثر ۱۰ MiB است.

Rate-limit state در DB با unique window و atomic upsert نگهداری می‌شود.

## ۹. Version Gate

Backend policy authoritative برای `latest_version`، `recommended_version`، `minimum_supported_version` و `deprecated_versions[]` دارد و `X-WooGit-App-Version` را روی مسیرهای API بررسی می‌کند. نسخه deprecated با `APP_VERSION_DEPRECATED` و HTTP 426 متوقف می‌شود و outbound Customer request انجام نمی‌شود.

در migration فعلی، نبودن Header هنوز برای legacy client مجاز است؛ پس از تکمیل migration اپ، policy باید به `APP_VERSION_REQUIRED` تغییر کند.

## ۱۰. Logging و Privacy

Body و Headerهای دارای Secret نباید Log شوند. Request ID برای عیب‌یابی کافی است و Secret باید پیش از logging redaction شود.

## ۱۱. Cleanup / Retention

Cleanup روزانه:

- Sessionهای منقضی؛
- Idempotencyهای بدون تغییر بیش از ۷ روز؛
- Operationهای موفق/ناموفق پس از expiry؛
- Rate-limit windowهای قدیمی.

`unknown` operation برای جلوگیری از از دست رفتن وضعیت حذف نمی‌شود تا reconciliation معتبر انجام شود.

## ۱۲. CI و تست‌های امنیتی

CI علاوه بر PHP lint، invariantهای احراز هویت، Site Isolation، Idempotency/Unknown، SSRF، body limit و cleanup را بررسی می‌کند. تست‌های واقعی شبکه/DNS و concurrency باید در محیط integration/staging نیز اجرا شوند؛ grep-based CI به‌تنهایی جایگزین تست رفتاری نیست.
