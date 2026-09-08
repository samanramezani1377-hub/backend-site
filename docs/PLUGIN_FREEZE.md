# WooGit Main Plugin — Freeze Lock

> **Status: FROZEN / LOCKED while Theme V1 is implemented**
>
> Freeze date: 2026-09-08

## Scope

The WooGit Main Plugin is located at:

```text
plugin/woogit-backend/
```

This codebase is frozen while `theme/woogit/` is being implemented.

## Freeze Rules

During the freeze:

- No source-code changes under `plugin/woogit-backend/**` are allowed.
- No plugin refactor, cleanup, dependency change, behavior change or security change is allowed through the Theme implementation work.
- Plugin tests may be inspected and run, but must not be changed as part of Theme implementation.
- Plugin migrations/schema changes are prohibited during the freeze.
- Plugin version bumps and plugin package changes are prohibited during the freeze.
- Theme work must use the already frozen public Backend/API contracts.
- A Theme implementation problem must not be solved by silently modifying Plugin code.

## Enforcement

`plugin-freeze.yml` is a CI guard for pull requests targeting `main`. If a PR changes anything under `plugin/woogit-backend/**`, the guard fails and the PR must not be treated as Theme-safe.

The guard is intentionally independent from Plugin behavioral tests: it detects the forbidden change itself.

## Unlock Procedure

Plugin changes require an explicit freeze-unlock decision. Before any Plugin change:

1. Record the reason and scope in documentation/decision history.
2. Explicitly declare the Plugin Freeze open.
3. Make and test the Plugin change independently from Theme work.
4. Re-run the complete Plugin CI.
5. Re-freeze the Plugin before continuing Theme implementation.

A failed Theme test, missing API capability or implementation inconvenience is **not** by itself permission to unlock the Plugin.

## Important GitHub Limitation

The CI guard prevents merge through a passing PR check, but repository-level branch protection/ruleset administration is controlled by GitHub repository settings. No active repository ruleset is currently present. Therefore this file + CI guard is the in-repository enforcement layer; direct pushes by an account with write permission remain governed by repository permissions until a GitHub branch rule is configured.
