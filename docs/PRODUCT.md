# WooGit Product and Commercial Scope

## 1. Product

WooGit is a subscription SaaS that lets a customer connect one or more WordPress/WooCommerce sites to the WooGit mobile app. The commercial platform controls access, site credentials, capabilities and optional AI services from a central backend.

The product has three surfaces:

1. **WooGit Android app** — customer-facing operational client.
2. **WooGit Cloud** — authentication, gateway, subscription, AI, chat, analytics and integration services.
3. **Customer WordPress Bridge** — a small headless integration plugin that exposes WooGit-controlled capabilities to the customer's site.

A fourth surface is the **WooGit commercial/control website**, built with WordPress. It contains public marketing pages and the internal control plane used by WooGit operators.

## 2. Customer journey

### First connection

```text
Install WooGit
  -> create WooGit account
  -> enter customer WordPress domain and credentials
  -> send credentials only over TLS to WooGit backend
  -> backend validates the site
  -> backend creates/links the WooGit account + site record
  -> backend stores the site credential encrypted
  -> app receives only connection success + safe site metadata
```

The mobile app must not receive the stored credential back from the backend.

### Trial

Default commercial proposal:

- 15-day free trial.
- Trial validity is checked only by the backend.
- Trial can have explicit limits (site count, features, AI credits, request volume).
- Trial expiration blocks gateway access server-side.

### Paid plans

The first billing model can be duration-based:

- 30 days
- 90 days
- 180 days
- 365 days

Exact pricing, limits and currency are configuration, not hard-coded application logic.

## 3. Entitlements

A plan should describe capabilities rather than only a price:

```text
Plan
- duration
- price
- currency
- site_limit
- feature flags
- request limits
- analytics retention
- chat limits
- AI credit allocation
```

Examples of feature flags:

- products
- orders
- customers
- media
- plugin management
- bridge management
- analytics
- user tracking
- chat
- AI chat
- AI agent
- automation
- exports

## 4. Plugin strategy

The Bridge should be intentionally headless.

It should not require a WooGit settings page in wp-admin. The customer controls WooGit features from the WooGit app/web control plane.

The Bridge's responsibilities are limited to:

- authenticated API endpoints;
- capability discovery;
- secure outbound communication;
- optional frontend asset injection for enabled features;
- WordPress/WooCommerce hooks;
- command execution requested by WooGit;
- health/version reporting.

## 5. Commercial AI

WooGit may offer AI as an included feature or separate credit package.

Two supported models:

### WooGit-managed credits

```text
Customer -> WooGit payment
          -> AI credits
          -> WooGit AI Gateway
          -> provider
```

### BYOK

```text
Customer provider key
        -> encrypted WooGit vault
        -> AI Gateway
        -> provider billed to customer account
```

Provider-specific commercial/resale terms must be reviewed before selling provider consumption as a WooGit package.

## 6. Chat

A customer site can expose a WooGit chat widget through the Bridge. The widget sends messages to WooGit rather than directly to an AI provider.

```text
Visitor
  -> Chat Widget
  -> Bridge
  -> WooGit Chat API
  -> Human or AI
  -> Bridge
  -> Visitor
```

This allows AI/human handoff, conversation history, customer/order context, rate limiting and subscription enforcement without placing AI credentials on the customer site.

## 7. Analytics and tracking

The Bridge should emit compact events to WooGit rather than turn the customer's WordPress database into an analytics warehouse.

Example events:

- page_view
- product_view
- search
- add_to_cart
- checkout_started
- order_completed
- chat_started
- chat_message
- user_login

Privacy rules, retention and consent requirements must be configurable per jurisdiction and product mode.

## 8. Non-goals for the first commercial release

- Running a large AI model on customer WordPress hosting.
- Making WordPress wp-admin the primary WooGit UX.
- Storing raw WordPress passwords in the mobile app.
- Making subscription enforcement client-side.
- Building an unnecessarily distributed infrastructure before traffic requires it.
