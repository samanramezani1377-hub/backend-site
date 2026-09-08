#!/usr/bin/env bash
set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
THEME="$ROOT/theme/woogit"
FAIL=0

pass() { printf 'PASS: %s\n' "$1"; }
fail() { printf 'FAIL: %s\n' "$1"; FAIL=1; }

# Theme PHP must never talk directly to the customer WooCommerce infrastructure.
if grep -RInE 'wp_remote_(get|post|request)\(|\$wpdb|WC_[A-Za-z_]+' "$THEME/templates" "$THEME/template-parts" 2>/dev/null; then
  fail 'templates/template-parts contain direct infrastructure access'
else
  pass 'templates/template-parts stay presentation-only'
fi

# App-only session and activation must never be consumed by the Theme.
if grep -RInE 'activate-session|X-WooGit-Session' "$THEME" 2>/dev/null; then
  fail 'Theme references App session or activate-session'
else
  pass 'Theme does not consume App session'
fi

# Customer WooCommerce credentials may appear only as transient request field names in the registration form/AJAX boundary.
for forbidden in 'update_option.*consumer_secret' 'set_transient.*consumer_secret' 'setcookie.*consumer_secret' 'wp_localize_script.*consumer_secret' 'localStorage.*consumer_secret' 'sessionStorage.*consumer_secret'; do
  if grep -RInE "$forbidden" "$THEME" 2>/dev/null; then
    fail "credential persistence pattern found: $forbidden"
  else
    pass "no credential persistence pattern: $forbidden"
  fi
done

# Public page wrappers must resolve to the shared renderer.
for page in page-features.php page-how-it-works.php page-faq.php page-documentation.php page-support.php page-service-status.php page-privacy.php page-terms.php; do
  if [[ -f "$THEME/$page" ]] && grep -q "template-parts/public-page.php" "$THEME/$page"; then
    pass "$page uses shared public renderer"
  else
    fail "$page is missing or does not use shared public renderer"
  fi
done

# The App-only billing endpoint must not be present in Theme API calls.
if grep -RInE "billing/activate-session|['\"]activate-session['\"]" "$THEME" 2>/dev/null; then
  fail 'App-only billing activation endpoint referenced by Theme'
else
  pass 'Theme billing surface excludes App-only activation'
fi

exit "$FAIL"
