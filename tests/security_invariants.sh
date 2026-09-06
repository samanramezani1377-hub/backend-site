#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN="$ROOT/plugin/woogit-backend"
while IFS= read -r -d '' file; do php -l "$file" >/dev/null; done < <(find "$PLUGIN" -type f -name '*.php' -print0)
controller="$PLUGIN/src/RestController.php"; policy="$PLUGIN/src/ProxyPolicy.php"; proxy="$PLUGIN/src/WooCommerceProxy.php"; idempotency="$PLUGIN/src/IdempotencyService.php"; database="$PLUGIN/src/Database.php"
grep -q "getOwned" "$controller"; grep -q "X-WooGit-Session" "$controller"; grep -q "APP_VERSION_DEPRECATED" "$controller"; grep -q "RATE_LIMITED" "$controller"; grep -q "request_body_too_large" "$controller"
grep -q "validateMethod" "$controller"; grep -q "operation_status_unknown" "$controller"; grep -q "requires_reconciliation" "$controller"; grep -q "state.*unknown" "$idempotency"
grep -q "wp_safe_remote_request" "$proxy"; grep -q "publicDestination" "$proxy"; grep -q "NO_PRIV_RANGE" "$proxy"; grep -q "https" "$policy"
if grep -Eq 'consumer_(key|secret).*query|query.*consumer_(key|secret)' "$proxy"; then echo 'FAIL: credentials must not be query parameters' >&2; exit 1; fi
grep -q "UNIQUE KEY host (host)" "$database"
grep -q "woogit_backend_cleanup" "$ROOT/plugin/woogit-backend/woogit-backend.php"; grep -q "DELETE FROM.*woogit_sessions" "$ROOT/plugin/woogit-backend/woogit-backend.php"
echo "security invariants: PASS"
