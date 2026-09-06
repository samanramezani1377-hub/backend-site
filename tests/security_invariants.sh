#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN="$ROOT/plugin/woogit-backend"
fail(){ echo "FAIL: $1" >&2; exit 1; }
contains(){ local file="$1" pattern="$2" label="$3"; grep -Eq "$pattern" "$file" || fail "$label"; }

while IFS= read -r -d '' file; do
  echo "CHECK PHP: $file"
  php -l "$file" >/dev/null || fail "PHP syntax: $file"
done < <(find "$PLUGIN" -type f -name '*.php' -print0)
controller="$PLUGIN/src/RestController.php"; policy="$PLUGIN/src/ProxyPolicy.php"; proxy="$PLUGIN/src/WooCommerceProxy.php"; idempotency="$PLUGIN/src/IdempotencyService.php"; operations="$PLUGIN/src/OperationService.php"; database="$PLUGIN/src/Database.php"; account="$PLUGIN/src/AccountService.php"; bootstrap="$PLUGIN/woogit-backend.php"; rate="$PLUGIN/src/RateLimitService.php"; version="$PLUGIN/src/VersionGate.php"

echo "CHECK controller ownership"; contains "$controller" 'getOwned' 'controller must enforce Site ownership'
echo "CHECK controller session"; contains "$controller" 'X-WooGit-Session' 'controller must require WooGit Session'
echo "CHECK controller version gate"; contains "$controller" 'APP_VERSION_DEPRECATED' 'controller must enforce deprecated-version gate'
echo "CHECK controller rate limit"; contains "$controller" 'RATE_LIMITED' 'controller must enforce rate limiting'
echo "CHECK controller body limit"; contains "$controller" 'request_body_too_large' 'controller must enforce request body size'
echo "CHECK controller mutation validation"; contains "$controller" 'validateMethod' 'controller must validate mutation method/idempotency'
echo "CHECK controller unknown operation"; contains "$controller" 'operation_status_unknown' 'controller must expose unknown operation state'
echo "CHECK controller reconciliation"; contains "$controller" 'requires_reconciliation' 'controller must mark unknown operations as requiring reconciliation'
echo "CHECK idempotency states"; contains "$idempotency" 'state.*unknown' 'idempotency must support unknown state'
echo "CHECK safe HTTP"; contains "$proxy" 'wp_safe_remote_request' 'proxy must use safe WordPress HTTP request'
echo "CHECK resolved destination"; contains "$proxy" 'publicDestination' 'proxy must validate resolved public destination'
echo "CHECK private ranges"; contains "$proxy" 'NO_PRIV_RANGE' 'proxy must reject private/reserved destinations'
echo "CHECK HTTPS"; contains "$policy" 'https' 'policy must enforce HTTPS'
if grep -Eq 'consumer_(key|secret).*query|query.*consumer_(key|secret)' "$proxy"; then fail 'credentials must not be query parameters'; fi
echo "CHECK global site host uniqueness"; contains "$database" 'UNIQUE KEY host \(host\)' 'Site host must be globally unique'
echo "CHECK explicit account creation"; contains "$account" 'function create' 'Account must be created explicitly'
echo "CHECK contact email update"; contains "$account" 'function updateContactEmail' 'Account contact email update must be explicit'
if grep -Eq 'findOrCreate\([^)]*email|findOrCreate\(\$email' "$account" "$controller"; then fail 'email must never resolve Account identity'; fi
echo "CHECK idempotency retention"; contains "$bootstrap" "woogit_idempotency WHERE updated_at < .*AND state IN \('succeeded','failed'\)" 'retention must never delete pending/unknown idempotency rows'
echo "CHECK operation retention"; contains "$bootstrap" "woogit_operations WHERE expires_at IS NOT NULL .*status IN \('succeeded','failed'\)" 'retention must preserve unknown operations'
echo "CHECK pending state"; contains "$idempotency" 'state.*pending' 'idempotency must support pending state'
echo "CHECK unknown persistence"; contains "$operations" 'markUnknown' 'operation service must persist unknown state'
echo "CHECK atomic rate limit"; contains "$rate" 'ON DUPLICATE KEY UPDATE' 'rate limit counter must be atomic'
echo "CHECK fail-closed rate limit"; contains "$rate" 'allowed.*false' 'rate limit must fail closed on storage error'
echo "CHECK minimum version"; contains "$version" 'minimum_supported_version' 'version gate must enforce minimum supported version'
echo "CHECK deprecated versions"; contains "$version" 'deprecated_versions' 'version gate must support explicit deprecated versions'

echo "security invariants: PASS"
