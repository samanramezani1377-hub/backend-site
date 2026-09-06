# WooGit In-App Announcements

WooGit Backend exposes server-controlled announcements for the app UI. The backend sends data and an opaque numeric `display_type`; the app owns all visual rendering.

## Contract

`GET /wp-json/woogit/v1/announcements`

Optional request header: `X-WooGit-App-Version`.

Response:

```json
{
  "announcements": [
    {
      "id": "billing-expiry-7d",
      "type": "warning",
      "title": "اعتبار شما رو به اتمام است",
      "message": "اعتبار شما ۷ روز دیگر تمام می‌شود.",
      "priority": 80,
      "display_type": 1,
      "action": {"type":"billing","label":"تمدید اعتبار"},
      "dismissible": true,
      "starts_at": null,
      "expires_at": "2026-10-01 00:00:00"
    }
  ]
}
```

`display_type` is intentionally numeric and opaque. Backend does not define UI behavior. Example app convention may be `1 = full banner`, `2 = top expanding notice`, but the app is the source of truth for rendering and may assign additional numbers later.

Announcements are managed in WordPress admin under **Settings → WooGit Announcements**. They can be targeted to exact app versions and scheduled with start/expiry timestamps. Results are sorted by descending priority.

The endpoint is rate limited and passes through the existing app version gate. A deprecated/unsupported version receives the existing `426 APP_VERSION_DEPRECATED` response instead of announcements.
