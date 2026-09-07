# Test Suites

## Plugin

All tests currently present in this repository are Plugin/Backend contract tests. They belong to `tests/plugin/` and validate the WooGit Main Plugin under `plugin/woogit-backend/`.

The Plugin suite covers V1 state-machine, authorization/security, billing/idempotency, proxy lifecycle, Web login identity, and related backend invariants.

## Theme

There is currently no Theme-specific test suite. Theme tests should be added under `tests/theme/` when Theme behavior has dedicated automated coverage.

## Integration

Integration tests are a future layer for exercising the actual REST controllers against WordPress/WooCommerce test doubles or a test instance. They should not be mixed into Plugin-only contract tests.

The current tests are framework-free contract tests and are not a replacement for real WordPress/WooCommerce integration tests.
