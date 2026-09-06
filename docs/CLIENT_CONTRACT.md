# قرارداد Client ↔ Backend ووگیت

> وضعیت این سند: **Baseline / Locked Reference**
>
> این سند بر اساس پیاده‌سازی فعلی اپ Android WooGit تهیه شده است. هرجا رفتار فعلی اپ با معماری هدف Backend تفاوت دارد، تفاوت صریحاً با برچسب «فعلی» و «هدف» مشخص شده است.

## ۱. اصل مهم معماری

اپ Android ووگیت از قبل ساخته شده و Client مستقل است. Backend این repository نباید UI، Navigation، State Management یا معماری داخلی اپ را بازسازی کند.

مرجع Client:

- `samanramezani1377-hub/woogit`

مرجع Backend:

- `samanramezani1377-hub/backend-site`

قرارداد نهایی API باید با پیاده‌سازی واقعی Client تطبیق داده شود و صرفاً بر اساس معماری پیشنهادی نوشته نشود.

## ۲. توپولوژی فعلی اپ

در وضعیت فعلی، Backend هنوز در مسیر ارتباطی اپ قرار نگرفته است:

```text
WooGit Android
      │
      │ HTTPS / HTTP
      │ WooCommerce REST
      │ WordPress REST
      ▼
Customer WordPress / WooCommerce
```

بنابراین در کد فعلی Client:

- App-to-Backend authentication وجود ندارد.
- Access Token / Refresh Token برای Backend وجود ندارد.
- Session متعلق به WooGit Backend وجود ندارد.
- اعتبارهای WooCommerce/WordPress در Client برای اتصال مستقیم استفاده می‌شوند.

این موارد **نباید به‌عنوان قابلیت موجود گزارش شوند**؛ آن‌ها بخشی از Migration Target هستند.

## ۳. اتصال فعلی Client

صفحه Connection فعلی این ورودی‌ها را می‌گیرد:

1. پروتکل `HTTP/HTTPS`
2. دامنه/آدرس فروشگاه
3. WooCommerce Consumer Key
4. WooCommerce Consumer Secret
5. WordPress Username
6. WordPress Application Password

`WordPress Application Password` با رمز معمول ورود به `wp-admin` یکی نیست و باید در تمام قراردادهای Backend همین معنا را داشته باشد.

Client فعلی این اطلاعات را به لایه اتصال فروشگاه می‌دهد و Repository مستقیماً endpoint زیر را روی سایت مشتری صدا می‌زند:

```text
/wp-json/wc/v3/system_status
```

پس موفقیت اتصال فعلی بر اساس دسترسی واقعی به WordPress/WooCommerce سنجیده می‌شود.

## ۴. Store ID فعلی Client

Client فعلی برای Store یک شناسه محلی بر اساس URL فروشگاه تولید می‌کند؛ الگوی آن به‌صورت مفهومی چنین است:

```text
store-{hash(canonicalized-url)}
```

این شناسه:

- Local Client ID است.
- نباید بدون Migration Mapping به‌عنوان Backend `site_id` پذیرفته شود.
- نباید منبع حقیقت مالکیت سایت باشد.

Backend باید Site Identity مستقل و authoritative خود را داشته باشد و در Migration یک mapping صریح بین Client Store ID و Backend Site Identity ایجاد شود.

## ۵. توپولوژی هدف

پس از ورود Backend به مسیر ارتباطی، معماری هدف چنین است:

```text
WooGit Android
      │
      │ WooGit API
      ▼
WooGit Backend
      │
      │ authenticated outbound request
      ▼
Customer WordPress / WooCommerce
```

در این مرحله Backend مسئول نگهداری Credential سایت و اجرای عملیات مجاز است.

## ۶. Flow اتصال هدف

### ۶.۱ اتصال ناموفق

```text
Client credentials
    -> Backend
    -> verify WordPress/WooCommerce
    -> failure
    -> safe error
```

در failure:

- Account ساخته نمی‌شود.
- Trial ساخته نمی‌شود.
- Session ساخته نمی‌شود.
- Dashboard unlock نمی‌شود.
- Secret یا جزئیات داخلی زیرساخت در error response افشا نمی‌شود.

### ۶.۲ Site Identity موجود

```text
Client credentials
    -> Backend verification
    -> existing Site Identity
    -> existing site account
    -> WooGit session
    -> Dashboard
```

اتصال موفق به همان سایت، اثبات دسترسی به Site Identity است. Email/Google login نباید به‌عنوان پیش‌شرط login عادی این flow اضافه شود.

### ۶.۳ Site Identity جدید

```text
Client credentials
    -> Backend verification
    -> new Site Identity
    -> second in-app page
    -> email + first name + last name
    -> account creation
    -> trial eligibility check
    -> WooGit session
    -> Dashboard
```

صفحه دوم فقط بعد از verification موفق فعال می‌شود.

## ۷. Session و Token — قرارداد هدف، نه وضعیت فعلی

پس از Migration، Client برای عملیات عادی نباید Credential خام WordPress/WooCommerce را حمل کند.

معماری هدف:

```text
Access Token  -> کوتاه‌عمر
Refresh Token -> چرخشی / قابل ابطال
```

Backend باید Session و Token lifecycle را مدیریت کند، از جمله:

- expiry
- revocation
- rotation
- device/session tracking
- logout
- suspicious-session handling

**مهم:** تا زمانی که Client واقعاً به این قرارداد مهاجرت نکرده است، Backend نباید فرض کند Access/Refresh Token در اپ وجود دارد.

## ۸. Credential Vault

Backend پس از verification موفق، در صورت نیاز Credential سایت را در Vault امن نگهداری می‌کند.

قواعد:

- Credential خام در responseهای عادی برنگردد.
- Credential در log ثبت نشود.
- دسترسی Gateway به Credential حداقلی باشد.
- نسخه کلید رمزنگاری قابل audit و rotation باشد.
- تغییر/rotation/revocation Credential قابل ردیابی باشد.

## ۹. عملیات Client که باید در Migration پوشش داده شوند

Backend API باید در نهایت عملیات واقعی Client را پوشش دهد، نه اینکه یک API انتزاعی بدون مصرف‌کننده واقعی ساخته شود.

حداقل حوزه‌ها:

| حوزه | وضعیت فعلی Client | هدف Backend |
|---|---|---|
| Connection | مستقیم به WP/WC | Backend Gateway |
| Store Identity | Local Store ID | Authoritative Site Identity |
| Products | WooCommerce REST | Typed Backend operation |
| Orders | WooCommerce REST | Typed Backend operation |
| Customers | WooCommerce REST در صورت استفاده | Typed Backend operation |
| Categories | WooCommerce REST | Typed Backend operation |
| Media | WordPress media flow | Backend upload/media operation |
| Product mutations | مستقیم | Backend + idempotency |
| Order mutations | مستقیم | Backend + idempotency |
| Sync | Client repository/sync layer | Backend-aware sync/reconciliation |
| Pending mutations | Local queue | Backend-compatible operation identity |
| Errors | HTTP/API errors از WP/WC | استاندارد Backend error contract |

این جدول باید در زمان پیاده‌سازی هر API با کد واقعی Client دوباره validate شود.

## ۱۰. Media Contract

Flow فعلی Client برای رسانه به مدل WordPress/WooCommerce وابسته است و در Migration نباید با یک abstraction ناسازگار جایگزین شود.

هدف Backend:

```text
Client image
   -> Backend upload
   -> WordPress media
   -> Media ID + URL
   -> product/media association
```

Backend باید وضعیت upload را طوری طراحی کند که retry و timeout باعث ایجاد رسانه تکراری نشود.

## ۱۱. Idempotency و Timeout-after-success

هر mutation دارای side effect که از Client به Backend منتقل می‌شود باید operation identity پایدار داشته باشد.

حداقل سناریوی اجباری:

```text
Client -> CREATE request
Backend -> remote WooCommerce success
Backend -> response lost / timeout
Client -> retry same operation
Backend -> detect previous operation
Backend -> return previous result
```

هدف این است که timeout در شبکه هرگز به معنی «عملیات انجام نشده» فرض نشود.

این قرارداد برای mutationهای CREATE و سایر عملیات non-idempotent باید بخشی از API contract باشد.

## ۱۲. Sync و Reconciliation

با توجه به Local-first بودن Client، Backend نباید صرفاً یک proxy ساده باشد.

برای عملیات ambiguous باید امکان reconciliation وجود داشته باشد:

```text
operation_id
   -> operation state
   -> remote result / known resource
   -> final canonical state
```

Client باید بتواند پس از restart، timeout یا retry وضعیت نهایی operation را دریافت کند.

## ۱۳. Error Contract

Backend باید error response استاندارد داشته باشد و Client نباید مجبور شود متن خطای خام WordPress/PHP را parse کند.

حداقل فیلدهای پیشنهادی:

```json
{
  "code": "STORE_CONNECTION_FAILED",
  "message": "اتصال به فروشگاه انجام نشد.",
  "request_id": "...",
  "operation_id": "...",
  "retryable": false
}
```

Secret، Consumer Secret، Application Password و stack trace هرگز نباید در response عمومی قرار گیرند.

## ۱۴. Currency Contract

واحد پول فروشگاه باید از داده canonical فروشگاه/Commerce به Client برسد و Backend در صورت تبدیل شدن به Gateway نباید آن را حذف کند.

برای محصولات، سفارش‌ها و سایر داده‌های مالی، response باید currency یا currency context مورد نیاز Client را حفظ کند.

Backend نباید فرض کند واحد پول همیشه یک مقدار ثابت مانند USD یا EUR است.

## ۱۵. Pagination / Filtering / Sorting

برای collection APIها قرارداد باید صریح باشد:

- page / cursor یا الگوی واحد انتخاب‌شده
- page size
- sort
- filters
- total/has-more در صورت پشتیبانی
- stable ordering

Backend باید این قرارداد را طوری ارائه کند که با listهای محصولات و سفارش‌های Client قابل sync باشد.

## ۱۶. Migration Rules

تا زمان مهاجرت کامل Client:

1. Backend API نباید به‌عنوان API موجود Client فرض شود.
2. هر endpoint جدید باید مصرف‌کننده مشخص در Client داشته باشد.
3. نام‌گذاری مدل‌های Backend نباید با مدل‌های واقعی Client بدون بررسی تطبیق فرض شود.
4. Store ID محلی Client نباید مستقیماً site identity تلقی شود.
5. Access/Refresh Token نباید در مستندات «فعلی» نوشته شود.
6. Credential handling باید از direct REST به Backend Vault منتقل شود.
7. رفتار فعلی direct WooCommerce باید قبل از cutover با contract test پوشش داده شود.
8. Migration باید امکان rollback یا coexistence کنترل‌شده داشته باشد.

## ۱۷. Definition of Done برای Client Contract

قرارداد Client ↔ Backend زمانی آماده implementation محسوب می‌شود که برای هر عملیات مورد استفاده Client موارد زیر مشخص باشند:

- endpoint
- HTTP method
- authentication
- request schema
- response schema
- error codes
- retry policy
- idempotency policy
- timeout behavior
- pagination/filter/sort
- operation identity
- reconciliation strategy
- rate-limit behavior
- required entitlement

تا آن زمان این سند **Baseline معماری و مهاجرت** است، نه ادعای کامل بودن API.
