#!/usr/bin/env bash
set -euo pipefail

ROOT="$PWD"
SERVICE="$ROOT/plugin/woogit-backend/src/BazaarBillingService.php"
CONTROLLER="$ROOT/plugin/woogit-backend/src/BillingController.php"
SETTINGS="$ROOT/plugin/woogit-backend/src/SettingsAdmin.php"

fail(){ echo "FAIL: $1"; exit 1; }

grep -Fq "devapi/v2/auth/authorize/" "$SERVICE" || fail "OAuth authorization endpoint missing"
grep -Fq "devapi/v2/auth/token/" "$SERVICE" || fail "OAuth token endpoint missing"
grep -Fq "response_type' => 'code'" "$SERVICE" || fail "authorization code flow missing"
grep -Fq "access_type' => 'offline'" "$SERVICE" || fail "offline authorization missing"
grep -Fq "OAUTH_STATE_PREFIX" "$SERVICE" || fail "OAuth state storage missing"
grep -Fq "hash('sha256', \$state)" "$SERVICE" || fail "OAuth state must be hashed before storage"
grep -Fq "delete_transient(\$key)" "$SERVICE" || fail "OAuth state must be single-use"
grep -Fq "user_can(\$user, 'manage_options')" "$SERVICE" || fail "OAuth callback must bind state to an admin"
grep -Fq "hash_equals(\$this->oauthRedirectUri(), \$redirectUri)" "$SERVICE" || fail "OAuth callback redirect URI binding missing"
grep -Fq "client_secret" "$SERVICE" || fail "OAuth token exchange must use client secret server-side"
grep -Fq "register_rest_route('woogit/v1', '/billing/bazaar/oauth/authorize'" "$CONTROLLER" || fail "OAuth authorize route missing"
grep -Fq "current_user_can('manage_options')" "$CONTROLLER" || fail "OAuth authorize route must require admin capability"
grep -Fq "register_rest_route('woogit/v1', '/billing/bazaar/oauth/callback'" "$CONTROLLER" || fail "OAuth callback route missing"
grep -Fq "rest_url('woogit/v1/billing/bazaar/oauth/callback')" "$SERVICE" || fail "OAuth redirect URI must be generated from the actual REST route"
grep -Fq "if (empty(\$current['client_secret']))" "$SETTINGS" || fail "Client Secret must be saved before OAuth"
grep -Fq "Refresh Token باید" "$SETTINGS" && fail "Settings must not require a refresh token before OAuth"

echo "PASS: Bazaar OAuth security contract"
