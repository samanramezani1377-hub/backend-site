# Theme Tests

Theme-specific automated checks live here and are independent from Plugin CI.

## Current checks

- `test_theme_static.sh` — presentation/infrastructure boundary, App-session exclusion, credential-persistence patterns, public-page wrappers, and App-only billing endpoint exclusion.

These checks are intentionally static and deterministic. They do not fake authentication, entitlement, billing, or payment success.
