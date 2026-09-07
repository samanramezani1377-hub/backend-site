# قرارداد خطاهای API WooGit

> وضعیت: V1 — قرارداد مشترک App و Web
>
> این سند رجیستری canonical کدهای خطای عمومی API است. Backend مرجع نهایی است و Clientها نباید بر اساس متن پیام، stack trace یا جزئیات داخلی تصمیم‌گیری کنند.

## ۱. ساختار خطا

پاسخ خطا باید حداقل شامل این مفهوم باشد:

```json
{
  "code": "rate_limited",
  "message": "درخواست‌های شما بیش از حد مجاز است."
}
```

فیلدهای اختیاری فقط در صورت قراردادی بودن:

```text
retry_after
retryable
operation_id
```

هرگز SQL، stack trace، secret، credential یا جزئیات داخلی در پاسخ عمومی قرار نگیرد.

## ۲. کدهای canonical

| HTTP | Code | کاربرد |
|---|---|---|
| 400 | `validation_error` | ورودی نامعتبر یا ناقص |
| 401 | `invalid_web_credentials` | اطلاعات ورود وب نامعتبر |
| 401 | `invalid_web_session` | Web Session نامعتبر/منقضی |
| 401 | `invalid_session` | App Session نامعتبر |
| 403 | `account_inactive` | حساب غیرفعال |
| 403 | `site_not_owned` | سایت متعلق به Account جاری نیست |
| 403 | `not_entitled` | Entitlement لازم وجود ندارد |
| 409 | `idempotency_conflict` | همان Idempotency-Key با درخواست متفاوت استفاده شده |
| 409 | `operation_pending` | عملیات قبلی هنوز در حال انجام است |
| 409 | `operation_unknown` | وضعیت عملیات قبلی قابل تعیین نیست |
| 429 | `rate_limited` | محدودیت نرخ درخواست |
| 500 | `server_error` | خطای عمومی سرور |

در صورت وجود error codeهای تخصصی دیگر، باید به همین رجیستری اضافه و مستند شوند؛ Client نباید enum جدید را حدس بزند.

## ۳. Rate Limit

برای `429`، در صورت وجود مقدار قابل اتکا، `retry_after` و هدر استاندارد `Retry-After` ارسال شود. Client باید از retry تهاجمی خودداری کند.

## ۴. رفتار Theme

Theme باید بر اساس HTTP status + `code` رفتار کند. متن UI می‌تواند محلی‌سازی شود، اما معنی خطا باید از code قرارداد گرفته شود.

کد ناشناخته → نمایش خطای عمومی امن.

## ۵. یکنواختی

کد canonical مربوط به rate limit در قرارداد مشترک `rate_limited` است. Backend و مستندات نباید شکل‌های متناقض مانند `RATE_LIMITED` و `rate_limited` را برای یک خطا منتشر کنند.
