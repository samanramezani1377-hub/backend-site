# سیاست نسخه Client ووگیت

> وضعیت: **V1 Contract — Locked / Backend enforcement implemented**

Backend policy شامل `latest_version`، `recommended_version`، `minimum_supported_version` و `deprecated_versions[]` است و روی همه مسیرهای Backend اعمال می‌شود.

## قرارداد Header

```http
X-WooGit-App-Version: 5.0.0
```

Backend نسخه را در هر Request بررسی می‌کند. نسخه‌ای که پایین‌تر از minimum باشد یا دقیقاً در `deprecated_versions[]` قرار داشته باشد با `APP_VERSION_DEPRECATED` و HTTP 426 رد می‌شود و هیچ outbound Customer request انجام نمی‌شود.

`deprecated_versions[]` مستقل از minimum است؛ بنابراین می‌توان مثلاً `4.0.0` را Block کرد ولی `3.0.0` و `5.0.0` را مجاز نگه داشت.

## Migration Mode

تا زمانی که Android Client Header را ارسال نمی‌کند، نبودن Header در Backend مجاز است تا compatibility شکسته نشود. این **تنها استثنای migration** است و به معنی معتبر بودن نسخه نامعلوم برای همیشه نیست.

پس از انتشار نسخه‌ای از App که Header را ارسال می‌کند، policy production باید به حالت اجباری منتقل شود و نبود Header با `APP_VERSION_REQUIRED` رد شود.

## Response استاندارد

```json
{
  "code": "APP_VERSION_DEPRECATED",
  "minimum_supported_version": "3.0.0",
  "latest_version": "5.0.0",
  "recommended_version": "5.0.0",
  "update_required": true,
  "retryable": false
}
```

## Enforcement Order

```text
App Request
    ↓
Client Version Gate
    ├── deprecated/unsupported → REJECT
    └── allowed
          ↓
Authentication / Session
          ↓
Account + Site Ownership
          ↓
Subscription / Entitlement
          ↓
Rate Limit / Security / Body Size
          ↓
Controlled Proxy
          ↓
Customer WordPress / WooCommerce
```

## Policy Ownership

Policy فقط از Backend option معتبر خوانده می‌شود و Client نمی‌تواند آن را تغییر دهد. مقادیر پیش‌فرض با release فعلی Backend مقداردهی می‌شوند.

## Definition of Done

- [x] Client Version در Backend قابل دریافت است.
- [x] Version Policy سمت Backend authoritative است.
- [x] `latest_version` وجود دارد.
- [x] `recommended_version` وجود دارد.
- [x] `minimum_supported_version` وجود دارد.
- [x] `deprecated_versions[]` وجود دارد.
- [x] نسخه مشخص می‌تواند مستقل از نسخه قبل/بعد Block شود.
- [x] Version Gate قبل از outbound Customer request اجرا می‌شود.
- [x] `APP_VERSION_DEPRECATED` استاندارد است.
- [x] نسخه Block شده هیچ درخواست Customer دریافت نمی‌کند.
- [ ] Android Client باید Header و handling خطا را migrate کند.
- [ ] پس از migration، `APP_VERSION_REQUIRED` برای Header مفقود فعال شود.
