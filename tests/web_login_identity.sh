#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WEB_AUTH="$ROOT/plugin/woogit-backend/src/WebAuthController.php"

fail(){ echo "FAIL: $1" >&2; exit 1; }

# Extract only the login method so unrelated email/contact-email code cannot
# satisfy the assertions accidentally.
login_block="$(perl -0777 -ne 'if (/public function login\(.*?(?=    public function logout\()/s) { print $&; }' "$WEB_AUTH")"
test -n "$login_block" || fail 'could not isolate Web login method'

grep -Eq "\$input\['site_url'\]" <<<"$login_block" || fail 'web login must read Site URL'
grep -Eq '\$this->policy->resolveSiteUrl\(\$url\)' <<<"$login_block" || fail 'web login must normalize/validate Site URL through ProxyPolicy'
grep -Eq '\$this->sites->findByHost\(\$host\)' <<<"$login_block" || fail 'web login must resolve identity from Site host'
grep -Eq '\$site\?\$this->accounts->get\(\(int\)\$site\['"'"'account_id'"'"'\]\)' <<<"$login_block" || fail 'web login must resolve Account through the matched Site'
grep -Eq '\$this->accounts->verifyWebPassword\(\(int\)\$account\['"'"'id'"'"'\],\$password\)' <<<"$login_block" || fail 'web login must verify the Web password for the Site Account'
grep -Eq '\$this->webSessions->issue\(\(int\)\$account\['"'"'id'"'"'\],\(int\)\$site\['"'"'id'"'"'\]\)' <<<"$login_block" || fail 'web login must issue a Web Session bound to the resolved Account and Site'

# The customer-facing login identity must not fall back to contact email or
# WordPress's internal user_login.
if grep -Eq 'email|user_login|findByEmail|email_exists' <<<"$login_block"; then
  fail 'web login must not use contact email or WordPress user_login as identity'
fi

# Site URL must be the source of the host rate-limit bucket as well.
grep -Eq '\$this->rateLimits->check\('\''web_login_site'\'',\$host' <<<"$login_block" || fail 'web login must rate-limit by Site host'

echo 'PASS: Web login uses Site URL/host -> Site -> Account -> Web Password -> Web Session'
