# Theme API Map

## Rule

Every browser/server request made by Theme code must be traceable to its source file/function and its backend contract. Search the Theme source for `wp_remote_`, AJAX/fetch calls, REST paths and form actions when expanding this map.

## Known shared billing contracts

| ID | Contract | Theme role |
|---|---|---|
| API-003 | `/wp-json/woogit/v1/billing/plans` | pricing/subscription data |
| API-004 | `/wp-json/woogit/v1/billing/status` | portal billing state |
| API-005 | `/wp-json/woogit/v1/billing/checkout` | checkout/payment initiation where web flow uses it |

## Theme-only web contracts

Authentication, web bootstrap, web password setup, portal navigation and payment-result handling are owned by the Theme/web controllers and must be indexed here from their actual source symbols. Do not copy an App endpoint into this section merely because the business feature has the same name.

## Security boundary

Theme must not send App `X-WooGit-Session` and must not call App `/billing/activate-session`.

## Change rule

Method/path, form field, query/body field, header, response field, redirect, cookie/session behavior or error mapping changes require this map and `JSON_CONTRACTS.md` to be updated in the same code change.
