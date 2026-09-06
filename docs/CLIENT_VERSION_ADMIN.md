# WooGit App Version Policy Administration

WooGit Backend enforces client compatibility through the `woogit_backend_version_policy` WordPress option and `VersionGate`.

## Admin UI

WordPress administrators with the `manage_options` capability can open:

`Settings → WooGit App Versions`

The page manages:

- `latest_version`: newest released client version.
- `recommended_version`: version users should be encouraged to run.
- `minimum_supported_version`: versions below this are rejected.
- `deprecated_versions`: exact client versions that are rejected.

Saving the policy takes effect immediately for API requests that send `X-WooGit-App-Version`.

## Deprecating a version

To obsolete an exact version, add it to **Deprecated versions**, one version per line, then save.

Example:

```text
1.0.0
1.1.0
```

A deprecated client receives `426 APP_VERSION_DEPRECATED`, with `update_required=true`, `minimum_supported_version`, `latest_version`, and `recommended_version` in the response.

## Safety rules

- Only users with `manage_options` can change the policy.
- Changes require a WordPress admin nonce.
- Version syntax is validated before persistence.
- `minimum_supported_version` cannot exceed `latest_version`.
- `recommended_version` must be between minimum supported and latest.
- `latest_version` and `recommended_version` cannot be listed as deprecated.
- The policy is stored with WordPress's options API and is not exposed through a public management endpoint.
