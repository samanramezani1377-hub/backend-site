# Backend Plugin Test Map

## VerifySite billing fallback regression

`tests/plugin/VerifySiteBillingFallbackTest.php` protects the cross-repository contract for a valid store whose WooGit commerce entitlement does not grant operational access.

The test asserts that `verifySite`:
- keeps store/account verification successful with HTTP 200;
- issues a Billing Session;
- uses the Billing Session as the returned primary session when operational access is disabled;
- returns `scope=billing`;
- returns `access_enabled=false` and `billing_required=true`;
- exposes the dedicated `billing_session` for subsequent billing requests.

This is a source-level regression test for the Backend contract; it does not contact a live WordPress/WooCommerce site or payment provider.
