#!/usr/bin/env bash
set -uo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"; THEME="$ROOT/theme/woogit"; FAIL=0
pass(){ printf 'PASS: %s\n' "$1"; }; fail(){ printf 'FAIL: %s\n' "$1"; FAIL=1; }
for dir in "$THEME/templates/public" "$THEME/templates/auth" "$THEME/template-parts"; do
  if [[ -d "$dir" ]]; then pass "required template directory exists: ${dir#$THEME/}"; else fail "missing required template directory: ${dir#$THEME/}"; fi
done
for file in "$THEME/templates/public/home.php" "$THEME/templates/auth/login.php" "$THEME/templates/auth/register.php"; do
  if [[ -f "$file" ]]; then pass "required template exists: ${file#$THEME/}"; else fail "missing required template: ${file#$THEME/}"; fi
done
if grep -RInE 'wp_remote_(get|post|request)\(|\$wpdb|WC_[A-Za-z_]+' "$THEME/templates" "$THEME/template-parts"; then fail 'templates/template-parts contain direct infrastructure access'; else pass 'templates/template-parts stay presentation-only'; fi
if grep -RInE 'activate-session|X-WooGit-Session' "$THEME" --include='*.php' --include='*.js' --include='*.css'; then fail 'executable Theme code references App session or activate-session'; else pass 'executable Theme code does not consume App session'; fi
for forbidden in 'update_option.*consumer_secret' 'set_transient.*consumer_secret' 'setcookie.*consumer_secret' 'wp_localize_script.*consumer_secret' 'localStorage.*consumer_secret' 'sessionStorage.*consumer_secret'; do
  if grep -RInE "$forbidden" "$THEME" --include='*.php' --include='*.js'; then fail "credential persistence pattern found: $forbidden"; else pass "no credential persistence pattern: $forbidden"; fi
done
for page in page-features.php page-how-it-works.php page-faq.php page-documentation.php page-support.php page-service-status.php page-privacy.php page-terms.php; do
  if [[ -f "$THEME/$page" ]] && grep -q "template-parts/public-page.php" "$THEME/$page"; then pass "$page uses shared public renderer"; else fail "$page is missing or does not use shared public renderer"; fi
done
if grep -RInE "billing/activate-session|['\"]activate-session['\"]" "$THEME" --include='*.php' --include='*.js'; then fail 'App-only billing activation endpoint referenced'; else pass 'Theme billing surface excludes App-only activation'; fi
if grep -q 'data-multistep' "$THEME/templates/auth/register.php" && grep -q 'data-step="4"' "$THEME/templates/auth/register.php"; then pass 'register is a four-step flow'; else fail 'register is not a four-step flow'; fi
if grep -q 'wg-app-preview' "$THEME/templates/public/home.php"; then pass 'home contains app preview markup'; else fail 'home app preview missing'; fi
exit "$FAIL"
