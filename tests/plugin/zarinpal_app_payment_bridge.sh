#!/usr/bin/env bash
set -euo pipefail

ROOT="$PWD"
BRIDGE="$ROOT/plugin/woogit-backend/src/ZarinPalPaymentBridge.php"
START="$ROOT/plugin/woogit-backend/src/ZarinPalPaymentStartBridge.php"
PLUGIN="$ROOT/plugin/woogit-backend/woogit-backend.php"

fail(){ echo "FAIL: $1"; exit 1; }

grep -Fq "ZarinPalPaymentStartBridge" "$BRIDGE" || fail "App checkout must use the payment-start bridge"
grep -Fq "X-WooGit-Web-Session" "$BRIDGE" || fail "Checkout client type must distinguish web from app"
grep -Fq "X-WooGit-Session" "$BRIDGE" || fail "App session header must be required for the app bridge"
grep -Fq "createStartUrl" "$BRIDGE" || fail "App checkout bridge token creation missing"
grep -Fq "public function resolvePaymentUrl" "$BRIDGE" || fail "Gateway URL resolver must remain reusable by the bridge"
grep -Fq "payment-start" "$START" || fail "Payment-start path missing"
grep -Fq "bin2hex(random_bytes(32))" "$START" || fail "Payment-start token must be cryptographically random"
grep -Fq "hash('sha256', \$token)" "$START"
grep -Fq "set_transient" "$START" || fail "Payment-start state must be short-lived"
grep -Fq "get_transient" "$START" || fail "Payment-start token validation missing"
grep -Fq "get_meta(self::ACCOUNT_META)" "$START" || fail "Order/account binding missing"
grep -Fq "get_meta(self::SITE_META)" "$START" || fail "Order/site binding missing"
grep -Fq "get_created_via" "$START" || fail "Payment-start must be bound to WooGit orders"
grep -Fq "needs_payment" "$START" || fail "Payment-start must reject non-payable orders"
grep -Fq "Referrer-Policy: origin" "$START" || fail "Intermediary page must preserve the registered origin as referrer"
grep -Fq "noindex,nofollow,noarchive" "$START" || fail "Payment-start page must not be indexed"
grep -Fq "window.location.replace" "$START" || fail "Intermediary page must navigate from woogit.ir"
grep -Fq "ZarinPalPaymentStartBridge())->register()" "$PLUGIN" || fail "Payment-start bridge must be registered"
grep -Fq "ZarinPalPaymentBridge())->register()" "$PLUGIN" || fail "Existing ZarinPal bridge registration must remain"

echo "PASS: ZarinPal app payment bridge security contract"
