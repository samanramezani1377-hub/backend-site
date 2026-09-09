# WooGit Main Plugin — Freeze Lock

> **Status: OPEN / UNLOCKED for independent Plugin work**
>
> Unlock date: 2026-09-09

## Scope

The WooGit Main Plugin is located at:

```text
plugin/woogit-backend/
```

Plugin development is currently open and is separated from Theme V1 implementation work.

## Current Rules

- Plugin source changes are allowed on the dedicated Plugin work branch.
- Plugin changes must be tested independently from Theme implementation work.
- Plugin migrations and schema changes require explicit review before merge.
- Plugin version and package changes must be covered by Plugin CI.
- Theme work must not silently modify Plugin code to solve Theme-only problems.

## CI

Plugin CI autoruns on pushes that change `plugin/**`, `tests/plugin/**`, or the Plugin CI workflow itself. It also runs for pull requests targeting `main` when those paths change.

The previous Plugin Freeze Guard is currently disabled while independent Plugin work is active.

## Re-freeze

Before returning to Theme-only implementation, explicitly close the Plugin Freeze and restore the Freeze Guard while keeping Plugin CI active.
