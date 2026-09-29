#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PROXY="$ROOT/plugin/woogit-backend/src/WooCommerceProxy.php"

fail(){ echo "FAIL: $1"; exit 1; }

grep -Fq "getRestMode(" "$PROXY" || fail "REST mode must be read per site"
grep -Fq "rememberRestMode(" "$PROXY" || fail "REST mode must be persisted per site"
grep -Fq "get_transient" "$PROXY" || fail "REST mode cache must use a bounded WordPress transient"
grep -Fq "set_transient" "$PROXY" || fail "REST mode cache must be persisted"
grep -Fq "rest_route" "$PROXY" || fail "query-route fallback must use rest_route"
grep -Fq "'/wp/v2/users/me'" "$PROXY" || fail "WordPress verification must authenticate through users/me"
grep -Fq 'if($wpStatus===404)' "$PROXY" || fail "WordPress fallback must trigger only on 404"
grep -Fq 'if($wcStatus===404)' "$PROXY" || fail "WooCommerce fallback must trigger only on 404"
grep -Fq "wordpress_rest_unavailable" "$PROXY" || fail "WordPress route failure must be distinguishable from auth failure"
grep -Fq "woocommerce_rest_unavailable" "$PROXY" || fail "WooCommerce route failure must be distinguishable from auth failure"
grep -Fq "wordpress_auth_failed" "$PROXY" || fail "WordPress authentication failure must remain distinguishable"
grep -Fq "woocommerce_auth_failed" "$PROXY" || fail "WooCommerce authentication failure must remain distinguishable"
grep -Fq "redirection'=>0" "$PROXY" || fail "REST fallback must not enable redirects"
grep -Fq "wp_safe_remote_request" "$PROXY" || fail "REST fallback must preserve safe pinned transport"

echo "REST route fallback contract passed."
