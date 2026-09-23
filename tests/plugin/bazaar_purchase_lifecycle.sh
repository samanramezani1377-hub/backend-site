#!/usr/bin/env bash
set -euo pipefail

ROOT="$PWD"
SERVICE="$ROOT/plugin/woogit-backend/src/BazaarBillingService.php"
BILLING="$ROOT/plugin/woogit-backend/src/BillingService.php"
DATABASE="$ROOT/plugin/woogit-backend/src/Database.php"
CONTROLLER="$ROOT/plugin/woogit-backend/src/BillingController.php"
SETTINGS="$ROOT/plugin/woogit-backend/src/SettingsAdmin.php"

fail(){ echo "FAIL: $1"; exit 1; }

test -s "$SERVICE" || fail "Bazaar billing service missing"
test -s "$BILLING" || fail "Billing service missing"
test -s "$DATABASE" || fail "Database schema missing"
test -s "$CONTROLLER" || fail "Billing controller missing"
test -s "$SETTINGS" || fail "Bazaar settings missing"

# Purchase lifecycle: every verification must reach Bazaar before deciding that
# an existing token is already processed. This protects renewal with the same token.
grep -Fq 'if ($existing && (' "$SERVICE" || fail "existing-token ownership check missing"
grep -Fq '$validated = $this->validateSubscription' "$SERVICE" || fail "repeat verification must call Bazaar"
grep -Fq '$wpdb->update(' "$SERVICE" || fail "existing purchase must be refreshable"
grep -Fq "'expires_at' => \$record['expires_at']" "$SERVICE" || fail "renewed expiry must be persisted"
grep -Fq "'already_processed' => $existing !== null" "$SERVICE" || fail "idempotent response flag missing"

# Invalid/inactive/expired purchases must not activate entitlements.
grep -Fq "bazaar_purchase_not_active" "$SERVICE" || fail "inactive purchase guard missing"
grep -Fq "bazaar_purchase_expired" "$SERVICE" || fail "expired purchase guard missing"
grep -Fq "activateBazaarEntitlement" "$SERVICE" || fail "entitlement activation missing"
grep -Fq "entitlement_activation_failed" "$SERVICE" || fail "entitlement failure handling missing"

# Token uniqueness and concurrent insert recovery prevent cross-account token reuse.
grep -Fq "UNIQUE KEY purchase_token (purchase_token)" "$DATABASE" || fail "purchase token uniqueness missing"
grep -Fq "purchase_token=%s" "$SERVICE" || fail "purchase token lookup missing"
grep -Fq "bazaar_purchase_already_claimed" "$SERVICE" || fail "cross-account token protection missing"
grep -Fq "winner = $this->getPurchase" "$SERVICE" || fail "concurrent insert recovery missing"

# SKU lookup must cover more than the first 100 products.
grep -Fq "'paginate' => true" "$BILLING" || fail "Bazaar SKU lookup must paginate"
grep -Fq "'max_num_pages'" "$BILLING" || fail "Bazaar SKU pagination boundary missing"

# OAuth must be bound to an administrator and a WordPress REST nonce.
grep -Fq "wp_verify_nonce" "$CONTROLLER" || fail "OAuth nonce validation missing"
grep -Fq "current_user_can('manage_options')" "$CONTROLLER" || fail "OAuth admin capability check missing"

# Configured API endpoints are constrained to the official HTTPS Bazaar host.
grep -Fq "pardakht.cafebazaar.ir" "$SERVICE" || fail "official Bazaar host missing"
grep -Fq "scheme !== 'https'" "$SERVICE" || fail "HTTPS API hardening missing"
grep -Fq "host !== 'pardakht.cafebazaar.ir'" "$SERVICE" || fail "API host allowlist missing"

echo "PASS: Bazaar purchase lifecycle/security contract"
