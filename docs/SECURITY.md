# مدل امنیتی WooGit

## ۱. اهداف امنیتی

- Client نتواند Subscription و Entitlement را دور بزند.
- Customer Credentialها به Account یا Site دیگری افشا نشوند.
- Account بسته/غیرفعال یا Trial/Subscription منقضی نتواند از Backend به Customer Site درخواست بفرستد.
- Retry پس از Timeout باعث CREATE تکراری نشود.
- Proxy به SSRF یا Proxy عمومی تبدیل نشود.

## ۲. دو لایه اعتبار

در درخواست عادی دو دسته Credential وجود دارد:

```text
WooGit Session
    → احراز و مجوز مصرف‌کننده در WooGit Backend

WP Username
+ WP Application Password
+ WC Consumer Key
+ WC Consumer Secret
    → احراز Backend نزد Customer WordPress/WooCommerce
```

Application Password یک Credential برنامه‌ای WordPress برای API است و با رمز اصلی `wp-admin` متفاوت است. استفاده از آن برای REST API باید روی HTTPS باشد. citeturn0search1turn0search2

## ۳. Credential در Client

Customer credentials may exist in the existing Client because the current App already owns the direct-connection flow.

این موضوع در V1 عمداً پذیرفته شده تا تغییرات Android و پردازش Backend حداقلی بماند.

Backend باید:

- هرگز آن‌ها را Log نکند؛
- بی‌دلیل آن‌ها را در Response برنگرداند؛
- به Account/Site دیگری افشا نکند؛
- فقط برای مقصد مجاز همان Request مصرف کند؛
- در Error Response یا Audit Metadata مقدار خام Secret را قرار ندهد.

## ۴. Credential Storage

Credential Vault برای مسیر عادی Lightweight Proxy اجباری نیست.

```text
Client
 ↓ HTTPS
WooGit Session + Customer Credentials
 ↓
Backend checks
 ↓
Lightweight Proxy
 ↓
Customer Site
```

در صورت نیاز آینده به background job، webhook یا عملیات بدون حضور Client، ذخیره‌سازی امن Credential می‌تواند به‌عنوان تصمیم جداگانه اضافه شود. این موضوع نباید مسیر عادی را به Vault lookup وابسته کند.

## ۵. کنترل‌های قبل از Forward

```text
WooGit Session
  ↓
Account active / not closed
  ↓
Trial / Subscription valid
  ↓
Site ownership
  ↓
Entitlement
  ↓
Version / Security / Rate Limit
  ↓
Forward
```

هیچ Flag سمت Client به‌تنهایی مجوز محسوب نمی‌شود.

## ۶. Site Isolation

`site_id` باید در Backend به Site Identity ثبت‌شده resolve و مالکیت آن نسبت به Account بررسی شود.

```text
request.account_id owns site_id
AND
request.account_id has required entitlement
```

Customer Credential ارسالی Client نباید برای تغییر مقصد یا دور زدن Site ownership قابل استفاده باشد.

## ۷. Proxy و SSRF

URL دلخواه Client ممنوع است:

```text
/proxy?url=https://anything.com   ❌
```

مقصد از Site Identity مجاز تعیین می‌شود و Backend فقط مسیرهای مجاز WordPress/WooCommerce را Forward می‌کند.

## ۸. Idempotency

همه CREATE mutationهای موردنیاز و سایر عملیات non-idempotent باید Idempotency داشته باشند:

```http
Idempotency-Key: <stable-client-key>
```

رکورد عملیات حداقل Account، Site، operation، request fingerprint، state و نتیجه/مرجع remote لازم را نگه می‌دارد. Retry با همان کلید نباید CREATE دوم ایجاد کند.

## ۹. Timeout-after-success

```text
Client → Backend → Customer: CREATE
Customer → SUCCESS
Response lost / timeout
Client → retry
Backend → same operation identity
Backend → previous result / reconciliation
```

Timeout به‌تنهایی نباید به معنی «عملیات انجام نشده» تلقی شود.

## ۱۰. Rate Limit و Abuse Protection

حداقل کنترل‌ها:

- Rate Limit برای Account؛
- Rate Limit برای Site؛
- محدودیت IP در نقاط مناسب؛
- حداکثر اندازه Request/Response؛
- Timeout مقصد؛
- Circuit Breaker در صورت نیاز؛
- Audit Event برای عملیات حساس.

## ۱۱. Logging و Privacy

Body و Headerهای دارای Secret نباید Log شوند. Request ID برای عیب‌یابی کافی است؛ Log نباید امکان بازیابی Customer Credential را فراهم کند.

## ۱۲. امنیت عملیاتی

- HTTPS در Production؛
- مدیریت امن Secretهای عملیاتی؛
- Backup امن؛
- Dependency scanning؛
- هشدار برای احراز هویت ناموفق؛
- Audit برای تغییرات حساس؛
- نبود Secret عملیاتی در Git.
