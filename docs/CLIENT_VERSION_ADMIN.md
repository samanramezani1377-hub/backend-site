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

A deprecated client receives `426 APP_VERSION_DEPRECATED` from normal protected API endpoints. The response includes `update_required=true`, `minimum_supported_version`, `latest_version`, and `recommended_version`.

The dedicated in-app announcements endpoint intentionally remains available to deprecated clients so the app can retrieve and render an update banner before normal API access is blocked.

## In-app announcements

WooGit Backend exposes:

`GET /wp-json/woogit/v1/announcements`

The endpoint returns active announcements for the supplied `X-WooGit-App-Version`. If a valid WooGit session is supplied, account/site-targeted announcements can also be selected.

Announcement records contain:

- `id`
- `type`
- `title`
- `message`
- `priority`
- `display_type`
- `action`
- `dismissible`
- `starts_at`
- `expires_at`

`display_type` is intentionally an **opaque numeric contract**. The Backend only sends the number; it does not define the UI. The app owns the mapping, for example `1 = full banner`, `2 = top expandable notice`, and can change its visual implementation without changing the Backend contract.

The endpoint also emits system announcements for:

- deprecated/unsupported app versions;
- authenticated entitlements expiring within seven days.

These system records use the same contract as manually managed announcements.

## Safety rules

- Only users with `manage_options` can change the policy or announcements.
- Admin changes require WordPress CSRF nonces.
- Version syntax is validated before persistence.
- `minimum_supported_version` cannot exceed `latest_version`.
- `recommended_version` must be between minimum supported and latest.
- `latest_version` and `recommended_version` cannot be listed as deprecated.
- The announcement endpoint is rate limited.
- Account/site targeting is derived from a valid WooGit session; unauthenticated requests cannot select another account/site.
- The policy and announcement data are stored through WordPress's options API and are not exposed through public management endpoints.
