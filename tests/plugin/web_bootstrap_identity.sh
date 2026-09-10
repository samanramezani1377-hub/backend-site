#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
WEB_AUTH="$ROOT/plugin/woogit-backend/src/WebAuthController.php"
fail(){ echo "FAIL: $1" >&2; exit 1; }

block="$(perl -0777 -ne 'if (/public function webBootstrap\(.*?(?=public function setupWebCredentials\()/s) { print $&; }' "$WEB_AUTH")"
test -n "$block" || fail 'could not isolate Web Bootstrap method'

grep -Fq "register_rest_route('woogit/v1','/account/web-bootstrap'" "$WEB_AUTH" || fail 'Web Bootstrap route must be registered'
grep -Fq '$request->get_header('\''Idempotency-Key'\'')' <<<"$block" || fail 'Web Bootstrap must require Idempotency-Key'
grep -Fq '$this->rateLimits->check('\''web_bootstrap_ip'\''' <<<"$block" || fail 'Web Bootstrap must have an independent IP rate limit'
grep -Fq '$this->proxy->verify($base' <<<"$block" || fail 'Web Bootstrap must verify the real WooCommerce site through the Backend proxy'
grep -Fq '$this->accounts->create()' <<<"$block" || fail 'Web Bootstrap must create the Account only after site verification'
grep -Fq '$this->sites->findOrCreate' <<<"$block" || fail 'Web Bootstrap must resolve/create the Site through SiteService'
grep -Fq '$this->webSessions->issue' <<<"$block" || fail 'Web Bootstrap must issue a Web Session, not an App Session'
grep -Fq '$this->sessions->issue' <<<"$block" && fail 'Web Bootstrap must not issue an App Session'
grep -Fq 'web_password_configured' <<<"$block" || fail 'Web Bootstrap must return Web Password configuration state'
grep -Fq '$this->accounts->hasWebPassword' <<<"$block" || fail 'Web Bootstrap must read existing Web Password configuration state'
grep -Fq '$this->idempotency->completeVerify' <<<"$block" || fail 'Web Bootstrap result must be persisted through encrypted verify idempotency storage'

if grep -Fq '$this->accounts->setWebPassword' <<<"$block"; then
  fail 'Web Bootstrap must not create or validate the Web Password; password setup belongs to the dedicated endpoint'
fi
if grep -Eq "password_confirmation|invalid_web_password|\['password'\]|\['web_password'\]" <<<"$block"; then
  fail 'Web Bootstrap must not accept or validate Web Password input fields'
fi
if grep -Eiq 'error_log|wp_json_encode\(\$input|var_dump|print_r' <<<"$block"; then
  fail 'Web Bootstrap must not log or dump customer credentials'
fi

echo 'PASS: Web Bootstrap verifies the store, resolves the account/site, issues a Web Session, and leaves Web Password setup to the dedicated post-bootstrap flow'
