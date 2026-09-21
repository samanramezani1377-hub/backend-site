# WooGit CafeBazaar Billing

WooGit's Bazaar build uses Poolakey for the in-app subscription flow. The backend verifies the returned purchase token against CafeBazaar's Developer API before changing the WooGit entitlement.

## Server configuration

Do **not** commit Bazaar credentials to Git. Define these constants in `wp-config.php` (or the server's equivalent bootstrap configuration):

```php
define('WOOGIT_BAZAAR_CLIENT_ID', '...');
define('WOOGIT_BAZAAR_CLIENT_SECRET', '...');
define('WOOGIT_BAZAAR_REFRESH_TOKEN', '...');
define('WOOGIT_BAZAAR_PACKAGE_NAME', 'com.samanramezani1377.woogit');
```

The RSA public key used by Poolakey is separate from these server credentials and remains part of the Bazaar Android build.

## Plan mapping

For every WooGit subscription product, open the WooCommerce product editor and set:

- **WooGit Plan**: enabled
- **WooGit Plan Key**: stable WooGit plan key
- **CafeBazaar Product ID**: the exact subscription SKU configured in CafeBazaar

For variable subscriptions, set the CafeBazaar Product ID on each variation that has its own Bazaar SKU.

`GET /wp-json/woogit/v1/billing/plans` exposes these values as `bazaar_product_id`, and the Android Bazaar build selects the SKU from the selected variation first and the parent plan second.

## Verification flow

```text
WooGit Bazaar app
  -> Poolakey subscribeProduct()
  -> CafeBazaar
  -> purchaseToken
  -> POST /wp-json/woogit/v1/billing/bazaar/verify
  -> CafeBazaar Developer API
  -> verify subscription
  -> WooGit entitlement
```

The verification endpoint requires a valid WooGit billing session. The backend validates the package name, resolves the submitted SKU to an enabled WooGit subscription product, verifies the purchase server-side, rejects expired/refunded/non-active purchases, prevents a purchase token from being claimed by another Account/Site, and stores a hashed purchase token for replay protection.

## CafeBazaar Developer API credentials

Create a Developer API client in the CafeBazaar developer panel and obtain its refresh token using the offline authorization flow. The backend stores no access token in Git; access tokens are cached temporarily in WordPress transients and refreshed from the configured refresh token.

The server integration uses CafeBazaar Developer API v2 subscription validation.

## Important

Adding the RSA public key to the Android app is **not** enough to enable server verification. The server credentials above are required for the backend to call CafeBazaar's Developer API.

The app does not receive or store the client secret or refresh token.
