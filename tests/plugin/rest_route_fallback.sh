#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PROXY="$ROOT/plugin/woogit-backend/src/WooCommerceProxy.php"

fail(){ echo "FAIL: $1"; exit 1; }

grep -Fq "detectRestMode(" "$PROXY" || fail "REST mode must be detected from the public /wp/v2/ endpoint"
grep -Fq "requestRestPublic(" "$PROXY" || fail "public REST probe must not send credentials"
grep -Fq "getStoredRestMode(" "$PROXY" || fail "REST mode cache must distinguish missing cache from pretty mode"
grep -Fq "rememberRestMode(" "$PROXY" || fail "REST mode must be persisted per site"
grep -Fq "get_transient" "$PROXY" || fail "REST mode cache must use a bounded WordPress transient"
grep -Fq "set_transient" "$PROXY" || fail "REST mode cache must be persisted"
grep -Fq "rest_route" "$PROXY" || fail "query-route fallback must use rest_route"
grep -Fq "'/wp/v2/'" "$PROXY" || fail "public REST probe must target /wp/v2/"
grep -Fq "'/wp/v2/users/me'" "$PROXY" || fail "WordPress verification must authenticate through users/me"
grep -Fq "wordpress_auth_failed" "$PROXY" || fail "WordPress 401 must remain an authentication failure"
grep -Fq "wordpress_auth_forbidden" "$PROXY" || fail "WordPress 403 must remain distinct from authentication failure"
grep -Fq "wordpress_users_endpoint_unavailable" "$PROXY" || fail "WordPress users/me 404 must remain distinct from route detection"
grep -Fq "wordpress_rest_unavailable" "$PROXY" || fail "WordPress public REST route failure must be distinguishable"
grep -Fq "wordpress_rest_probe_failed" "$PROXY" || fail "WordPress public REST non-404 errors must be distinguishable"
grep -Fq "woocommerce_auth_failed" "$PROXY" || fail "WooCommerce 401 must remain an authentication failure"
grep -Fq "woocommerce_auth_forbidden" "$PROXY" || fail "WooCommerce 403 must remain distinct from authentication failure"
grep -Fq "woocommerce_rest_unavailable" "$PROXY" || fail "WooCommerce route failure must be distinguishable"
grep -Fq "woocommerce_http_error" "$PROXY" || fail "WooCommerce non-auth HTTP errors must be distinguishable"
grep -Fq "redirection'=>0" "$PROXY" || fail "REST probing must not enable redirects"
grep -Fq "wp_safe_remote_request" "$PROXY" || fail "REST probing must preserve safe pinned transport"

probe_line=$(grep -n "requestRestPublic($baseUrl,'/wp/v2/','pretty')" "$PROXY" | head -1 | cut -d: -f1)
users_line=$(grep -n "requestRest($baseUrl,'/wp/v2/users/me'" "$PROXY" | head -1 | cut -d: -f1)
[[ -n "$probe_line" && -n "$users_line" && "$probe_line" -lt "$users_line" ]] || fail "public REST mode detection must happen before users/me authentication"

if grep -Fq "if($wpStatus===404){$alternate" "$PROXY"; then
  fail "users/me must not blindly retry another REST URL style"
fi

echo "REST route detection and verification contract passed."
