# سیاست نسخه Client ووگیت

> وضعیت: **V1 Contract — Locked / Backend enforcement implemented**

Backend policy شامل `latest_version`، `recommended_version`، `minimum_supported_version` و `deprecated_versions[]` است و روی مسیرهای Backend که از Client App استفاده می‌شوند اعمال می‌شود.

## قرارداد Header

```http
X-WooGit-App-Version: 5.0.0
```

Backend نسخه را در هر Request بررسی می‌کند. **نبودن Header مجاز نیست** و با `APP_VERSION_REQUIRED` و HTTP 400 رد می‌شود. نسخه‌ای که پایین‌تر از minimum باشد یا دقیقاً در `deprecated_versions[]` قرار داشته باشد با `APP_VERSION_DEPRECATED` و HTTP 426 رد می‌شود و هیچ outbound Customer request انجام نمی‌شود.

`deprecated_versions[]` مستقل از minimum است؛ بنابراین می‌توان مثلاً `4.0.0` را Block کرد ولی `3.0.0` و `5.0.0` را مجاز نگه داشت، مشروط بر اینکه `3.0.0` زیر minimum نباشد.

## Migration Mode

حالت legacy که نبودن Header را موقتاً مجاز می‌کرد **پایان یافته است**. از این نسخه Backend، Client App باید `X-WooGit-App-Version` را ارسال کند و Request بدون آن پذیرفته نمی‌شود.

این تصمیم عمداً fail-closed است تا پس از انتشار App جدید، قرارداد نسخه واقعاً enforce شود و Client بدون نسخه نتواند از API استفاده کند.

## Response استاندارد

برای Header مفقود:

```json
{
  "code": "APP_VERSION_REQUIRED",
  "minimum_supported_version": "3.0.0",
  "latest_version": "5.0.0",
  "recommended_version": "5.0.0",
  "update_required": false,
  "retryable": false
}
```

برای نسخه deprecated/unsupported:

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
    ├── missing → REJECT (400)
    ├── invalid → REJECT (400)
    ├── deprecated/unsupported → REJECT (426)
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
- [x] نبودن `X-WooGit-App-Version` رد می‌شود.
- [x] Version Policy سمت Backend authoritative است.
- [x] `latest_version` وجود دارد.
- [x] `recommended_version` وجود دارد.
- [x] `minimum_supported_version` وجود دارد.
- [x] `deprecated_versions[]` وجود دارد.
- [x] نسخه مشخص می‌تواند مستقل از نسخه قبل/بعد Block شود.
- [x] Version Gate قبل از outbound Customer request اجرا می‌شود.
- [x] `APP_VERSION_REQUIRED` استاندارد است.
- [x] `APP_VERSION_DEPRECATED` استاندارد است.
- [x] نسخه Block شده هیچ درخواست Customer دریافت نمی‌کند.
- [ ] Android Client باید Header و handling خطا را migrate کند.
