# WooGit Bridge Plugin

## 1. Purpose

WooGit Bridge is the integration layer installed on the customer's WordPress site. It should be small, headless and capability-oriented.

The plugin is not the commercial backend and is not the primary UX.

## 2. WordPress admin UX

Default design:

```text
WordPress Plugins
  WooGit Bridge — Active
```

No required WooGit settings page.

All product configuration is controlled by WooGit.

The plugin may expose a minimal diagnostics/status link only if operationally necessary, but no feature should depend on manual wp-admin configuration.

## 3. Responsibilities

### REST

Register only WooGit-specific routes under a versioned namespace.

### Hooks

Use WordPress/WooCommerce hooks for events such as:

- order created/updated;
- order status changed;
- customer registration/login where appropriate;
- product viewed or commerce events where client-side tracking is enabled.

### Frontend

When enabled, inject a small, versioned widget/collector asset.

Do not inject code when the feature is disabled.

### Health

Expose protocol/plugin version and health state.

## 4. Provisioning

Preferred lifecycle:

```text
Install
  -> Activate
  -> Bridge boot
  -> WooGit provisioning request
  -> register site/bridge instance
  -> receive scoped bridge credential
  -> verify discovery
  -> Ready
```

The provisioning process must be idempotent.

## 5. No arbitrary proxy

The Bridge must not expose an endpoint like:

```text
POST /woogit/v1/proxy
{
  "url": "https://anything.com/evil"
}
```

Instead it should execute known commands or expose narrowly scoped resources.

## 6. Capability discovery

Example:

```json
{
  "protocol_version": 1,
  "plugin_version": "1.0.0",
  "capabilities": {
    "commerce": true,
    "analytics": true,
    "chat": true,
    "ai": false
  }
}
```

The capability list is informational. Server-side WooGit entitlements remain authoritative.

## 7. Chat widget

The Bridge can enqueue/inject a WooGit chat widget only when the backend says the site is entitled and the site configuration enables it.

The browser receives a short-lived, site-scoped chat session token, never a privileged Bridge token.

## 8. User tracking

Tracking should be event-based and privacy-minimized.

Example event:

```json
{
  "event_id": "uuid",
  "type": "product_view",
  "occurred_at": "2026-09-06T12:00:00Z",
  "page": "/product/example",
  "product_id": 123
}
```

Do not send WordPress passwords, payment data or arbitrary page HTML as event properties.

## 9. Plugin management

WooGit can manage standard WordPress plugins when the authenticated WordPress user has the required capabilities. WordPress's REST API documents plugin listing, activation/deactivation and deletion, while plugin creation is based on a WordPress.org plugin directory slug. Therefore the standard endpoint should not be treated as a generic private ZIP upload API.

For a private WooGit Bridge, installation strategy must be one of:

1. WordPress.org distribution;
2. one-time manual installation;
3. hosting-level installation access;
4. another explicitly supported provisioning channel.

Do not silently depend on an undocumented admin upload flow.

## 10. Security checklist

- Validate every parameter.
- Check capability/authorization for every privileged endpoint.
- Use nonces for same-origin browser admin interactions where applicable.
- Use HTTPS for external communication.
- Escape output.
- Sanitize input.
- Reject oversized payloads.
- Add replay/idempotency protection to mutations.
- Never log credentials.
- Keep the plugin dependency footprint small.
- Fail closed when provisioning/authentication is invalid.

## 11. Versioning

Bridge protocol and plugin version are separate:

```text
protocol_version = API compatibility contract
plugin_version   = implementation release
```

The backend must be able to reject incompatible protocol versions cleanly and guide the customer through an upgrade path.
