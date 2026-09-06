# قرارداد API ووگیت

> وضعیت: V1 Target Contract — Locked

## ۱. معماری

Backend در V1 یک **secure transparent gateway/proxy** است، نه یک WooCommerce business API.

```text
Android WooGit App
        ↓
   WooGit Backend
   Auth / Ownership / Entitlement / Security / Proxy
        ↓
Customer WordPress / WooCommerce
```

Backend نباید resource model، Product API، Order API یا response model مستقل از Customer site بسازد. App درخواست واقعی خود را به Backend می‌دهد و Backend همان request را، با مقصدی که از Site Identity resolve شده، به Customer site forward می‌کند و response upstream را تا حد ممکن بدون تغییر برمی‌گرداند.

## ۲. مدل احراز هویت

```text
WooGit Session
    → Authentication / Authorization در Backend

WP Username
WP Application Password
WC Consumer Key
WC Consumer Secret
    → Authentication نزد Customer WordPress/WooCommerce
```

Access Token + Refresh Token جزو قرارداد V1 نیست.

Customer Credentials برای Request لازم، همراه همان Request ارسال می‌شوند و Backend آن‌ها را در V1 ذخیره نمی‌کند.

## ۳. Bootstrap / Verification

`POST /api/v1/sites/verify`

Request مفهومی شامل URL و چهار Customer Credential است.

ترتیب:

```text
Network / HTTPS
 ↓
WordPress reachability/authentication
 ↓
WooCommerce availability/authentication
 ↓
Site Identity
 ↓
Account / Trial lifecycle
 ↓
WooGit Session
```

Verification read-only است و قبل از استفاده از gateway انجام می‌شود.

### تعریف Site Ownership در WooGit

در V1، **Site Ownership به معنی مالکیت حقوقی دامنه یا مالکیت قانونی سایت نیست.** مالکیت در WooGit به معنی **کنترل معتبر سایت از طریق credentialهای مدیریتی ارائه‌شده** است؛ یعنی Backend با credentialهای ارائه‌شده می‌تواند دسترسی مدیریتی/مجاز موردنیاز WooGit به WordPress/WooCommerce همان Site را با موفقیت تأیید کند.

بنابراین موفقیت `verify` به‌تنهایی هیچ ادعایی درباره مالک قانونی دامنه، برند یا شرکت ایجاد نمی‌کند. نتیجه verification فقط نشان می‌دهد Account ارائه‌دهنده credential، در چارچوب قرارداد WooGit، **کنترل معتبر آن Site** را اثبات کرده است.

پس از verification موفق، Backend رابطه Account و Site را بر همین مبنای کنترل معتبر ثبت می‌کند و authorizationهای بعدی بر اساس **Account + Site Ownership + Entitlement + Session** انجام می‌شوند. این تعریف، credential verification را از ادعای مالکیت حقوقی دامنه جدا می‌کند.

Verification موفق حتی وقتی Entitlement منقضی است، Account + Site را resolve کرده و Session صادر می‌کند؛ اما در این حالت Session فقط برای Account/Billing UI است و مجوز gateway ایجاد نمی‌کند. Response شامل `scope=billing`, `access_enabled=false` و `billing_required=true` خواهد بود.

## ۴. WooGit Session

Session یک زیرساخت واحد دارد اما دو Scope صریح دارد:

```text
billing      → ورود به Account و Billing
operational  → قابلیت‌های Customer-site / Commerce
```

Scope در DB ذخیره می‌شود و Client نمی‌تواند آن را تغییر دهد.

### Billing Session

Billing Session برای Account بدون Entitlement نیز قابل ایجاد است و TTL مستقل دارد. این Session برای Plans/Status/Checkout است و هرگز مجوز `/forward` یا سایر عملیات Customer-site ایجاد نمی‌کند.

### Operational Session

Operational Session فقط وقتی ایجاد می‌شود که Entitlement معتبر و capability موردنیاز فعال باشد. `expires_at` آن هرگز نباید بعد از `Entitlement.expires_at` باشد.

Session منقضی‌شده معتبر نیست و locally revive نمی‌شود. Automatic re-login یک Session Creation جدید است و Backend باید دوباره Account + Site Ownership + Entitlement را بررسی کند.

بعد از پرداخت موفق، Billing Session به Operational Session تبدیل نمی‌شود؛ Backend با endpoint اختصاصی Session جدید با Scope عملیاتی صادر می‌کند و Billing Sessionهای همان Account/Site را revoke می‌کند. این rotation مرز افزایش privilege است.

در تمدید Plan، Entitlement مرجع اصلی است و Operational Sessionهای فعال می‌توانند تا `Entitlement.expires_at` جدید reconcile شوند. Session هرگز نباید بعد از Entitlement معتبر بماند.

## ۵. درخواست عادی Gateway

`POST/GET/PUT/PATCH/DELETE /api/v1/forward` با Session و مشخصات request.

```text
App
 ↓
X-WooGit-Session
site_id از Session
request path + query + raw body
request-scoped Customer Credentials
 ↓
Session scope=operational / Account / Site Ownership / Entitlement / Security
 ↓
resolve destination from registered Site Identity
 ↓
Customer WordPress/WooCommerce
 ↓
upstream status + body + relevant headers
 ↓
App
```

`path` تنها مسیر درخواست است و هرگز URL مقصد نیست. Client حق تعیین host/scheme مقصد را ندارد.

در صورت شکست authorization هیچ outbound request ارسال نمی‌شود.

Gateway body را به مدل تجاری Backend تبدیل نمی‌کند؛ JSON، multipart/binary و سایر payloadهای مورد نیاز باید به‌صورت request body عبور داده شوند.

## ۶. Controlled Gateway Surface

Client URL دلخواه تعیین نمی‌کند. Backend فقط pathهایی را قبول می‌کند که بخشی از network surface فعلی WooGit App هستند. این کنترل یک **security boundary** است و نباید به مجموعه‌ای از endpointهای business-domain در Backend تبدیل شود.

در V1 مسیرهای WooCommerce REST و WordPress Media که App استفاده می‌کند قابل forward هستند. مقصد همیشه از Site Identity ثبت‌شده resolve می‌شود.

SSRF protection شامل HTTPS-only، رد localhost/private/reserved IP، نبود credential در URL و جلوگیری از path traversal است.

## ۷. Idempotency و Timeout-after-success

برای mutationها:

```http
Idempotency-Key: <stable-client-operation-key>
```

Fingerprint شامل method + path + query + hash بدنه خام request است. Retry با همان Key و همان Request نباید عملیات دوم ایجاد کند. استفاده از همان Key برای Request متفاوت باید Conflict باشد.

وضعیت authoritative هر کلید یکی از این موارد است:

```text
pending   → عملیات در حال اجراست
succeeded → پاسخ موفق upstream ثبت شده است
failed    → پاسخ ناموفق upstream ثبت شده است
unknown   → request ارسال شده ولی نتیجه نهایی upstream اثبات نشده است
```

در صورت timeout پس از ارسال request، Backend نباید موفقیت یا شکست عملیات Customer site را جعل کند؛ operation و idempotency record به `unknown` می‌روند و همان `operation_id` حفظ می‌شود.

**Retry یک mutation با همان Key هرگز نباید صرفاً به دلیل timeout دوباره به upstream forward شود.** این کار برای CREATE می‌تواند duplicate resource بسازد.

تا زمانی که یک reconciliation اختصاصی و قابل‌اعتماد برای همان resource وجود نداشته باشد، `unknown` یک وضعیت indeterminate است و Backend generic آن را خودکار به success/failure تبدیل یا mutation را دوباره اجرا نمی‌کند. Client باید `operation_id` را برای مشاهده state استفاده کند و برای عملیات ناشناخته از retry کور خودداری کند.

`GET /api/v1/operations/{operation_id}` فقط برای بازیابی state عملیات gateway است و API محصول/سفارش محسوب نمی‌شود.

Idempotency recordهای `pending` و `unknown` نباید توسط retention job حذف شوند؛ حذف آن‌ها می‌تواند همان mutation را پس از گذشت زمان دوباره قابل‌اجرا کند. فقط stateهای نهایی `succeeded` و `failed` مشمول retention عادی هستند.

## ۸. Sites / Subscription

فقط Site مجاز Account قابل استفاده است. Customer Credentials هرگز در Response سایت نمایش داده نمی‌شوند.

Site Ownership در این سند به معنای **کنترل معتبر فنی سایت از طریق credentialهای مدیریتی تأییدشده** است و نه مالکیت حقوقی دامنه. این Ownership مبنای authorization داخلی WooGit است؛ بنابراین وجود آن به‌تنهایی ادعای مالکیت قانونی دامنه یا برند محسوب نمی‌شود.

Subscription و Entitlement مرجع Backend هستند و Account/Plan منقضی نباید outbound request داشته باشد.

## ۹. Billing API

Billing در V1 روی WordPress اصلی WooGit و WooCommerce/WooCommerce Subscriptions انجام می‌شود. App نباید وضعیت پرداخت را خودش تعیین کند.

Endpoints:

```text
GET  /api/v1/billing/plans
GET  /api/v1/billing/status
POST /api/v1/billing/checkout
POST /api/v1/billing/activate-session
```

`billing/plans` فقط پلن‌های Subscription قابل فروش و منتشرشده WooCommerce را برمی‌گرداند؛ قیمت و مدت در App hard-code نمی‌شود.

`billing/checkout` فقط با WooGit Session معتبر اجرا می‌شود و Account/Site را از Session می‌گیرد، نه از مقادیر قابل جعل Client. Backend یک WooCommerce order مرتبط با همان Account/Site می‌سازد و `payment_url` را برمی‌گرداند تا App صفحه پرداخت وب را باز کند.

پرداخت مستقیماً به Account/Site متصل به Order ثبت می‌شود. موفقیت پرداخت از Client پذیرفته نمی‌شود. WooCommerce/WooCommerce Subscriptions مرجع وضعیت پرداخت و Subscription هستند و Backend از hookهای سروری وضعیت را به Entitlement داخلی همگام می‌کند.

پس از فعال‌شدن Subscription:

```text
WooCommerce Subscription
        ↓
server-side billing event
        ↓
Account + Site from immutable order metadata
        ↓
Entitlement status=active
        ↓
POST billing/activate-session
        ↓
new operational session + billing session rotation
        ↓
forward becomes authorized
```

در حالت Plan منقضی یا Trial تمام‌شده، App همچنان می‌تواند وارد Account و Billing شود، اما `/forward` باید قبل از هر outbound request با Scope و `Entitlement` رد شود.

## ۱۰. Currency / Collections / Errors

Backend مقدار response و query semantics Customer WooCommerce را حفظ می‌کند و currency را hard-code یا بی‌دلیل تبدیل نمی‌کند.

Pagination/filter/sort متعلق به upstream WooCommerce است و gateway باید query و response headerهای مرتبط را عبور دهد؛ Backend برای این موارد collection API مستقل نمی‌سازد.

Errorها machine-readable هستند و Secret، SQL، Stack Trace یا Customer Credential در Response عمومی قرار نمی‌گیرد.

## ۱۱. Customer Credential Storage

در V1:

- DB: ممنوع؛
- Vault: ممنوع؛
- persistent cache: ممنوع؛
- Log/Telemetry/Audit/Crash: ممنوع؛
- reuse برای Request یا Site دیگر: ممنوع.

هیچ قابلیت V1 نباید فرض کند Customer Credential پایدار در Backend وجود دارد.
