#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
for test in "$ROOT"/billing_checkout_idempotency.php "$ROOT"/security_lifecycle.php "$ROOT"/proxy_lifecycle.php; do
  php -d display_errors=1 "$test"
done
