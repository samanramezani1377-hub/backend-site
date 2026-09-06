#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN="$ROOT/plugin/woogit-backend"
while IFS= read -r -d '' file; do php -l "$file" >/dev/null; done < <(find "$PLUGIN" -type f -name '*.php' -print0)
controller="$PLUGIN/src/RestController.php"; policy="$PLUGIN/src/ProxyPolicy.php"; proxy="$PLUGIN/src/WooCommerceProxy.php"; idempotency="$PLUGIN/src/IdempotencyService.php"; operations="$PLUGIN/src/OperationService.php"; database="$PLUGIN/src/Database.php"; account="$PLUGIN/src/AccountService.php"; bootstrap="$PLUGIN/woogit-backend.php"; rate="$PLUGIN/src/RateLimitService.php"; version="$PLUGIN/src/VersionGate.php"

grep -q "getOwned" "$controller"; grep -q "X-WooGit-Session" "$controller"; grep -q "APP_VERSION_DEPRECATED" "$controller"; grep -q "RATE_LIMITED" "$controller"; grep -q "request_body_too_large" "$controller"
grep -q "validateMethod" "$controller"; grep -q "operation_status_unknown" "$controller"; grep -q "requires_reconciliation" "$controller"; grep -q "state.*unknown" "$idempotency"
grep -q "wp_safe_remote_request" "$proxy"; grep -q "publicDestination" "$proxy"; grep -q "NO_PRIV_RANGE" "$proxy"; grep -q "https" "$policy"
if grep -Eq 'consumer_(key|secret).*query|query.*consumer_(key|secret)' "$proxy"; then echo 'FAIL: credentials must not be query parameters' >&2; exit 1; fi
grep -q "UNIQUE KEY host (host)" "$database"
grep -q "function create" "$account"; grep -q "function updateContactEmail" "$account"
if grep -Eq 'findOrCreate\([^)]*email|findOrCreate\(\$email' "$account" "$controller"; then echo 'FAIL: email must never resolve Account identity' >&2; exit 1; fi
# Unknown/pending idempotency records must survive retention; deleting them can re-enable duplicate mutations.
grep -Eq "woogit_idempotency WHERE updated_at < .*AND state IN \('succeeded','failed'\)" "$bootstrap"
grep -Eq "woogit_operations WHERE expires_at IS NOT NULL .*status IN \('succeeded','failed'\)" "$bootstrap"
grep -q "state.*pending" "$idempotency"; grep -q "markUnknown" "$operations"
grep -q "ON DUPLICATE KEY UPDATE" "$rate"; grep -q "allowed.*false" "$rate"
grep -q "minimum_supported_version" "$version"; grep -q "deprecated_versions" "$version"

echo "security invariants: PASS"
