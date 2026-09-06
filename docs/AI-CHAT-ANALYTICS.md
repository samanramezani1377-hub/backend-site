# AI, Chat and Analytics

## 1. Chat

The chat system has three actors:

```text
Visitor
  <-> Widget
  <-> WooGit Chat Gateway
  <-> AI Agent OR Human Operator
```

Conversation state belongs to WooGit, not to the WordPress page.

## 2. AI agent

The AI agent must operate through typed tools.

Example tools:

```text
get_site()
get_customer()
get_order()
list_orders()
get_product()
search_products()
get_inventory()
```

For mutations:

```text
create_order()
update_customer()
change_order_status()
```

require explicit authorization and, for customer-facing high-impact operations, explicit user confirmation.

The model cannot call arbitrary URLs.

## 3. Provider abstraction

```text
AI Gateway
  |
  +-- OpenAI adapter
  +-- OpenRouter adapter
  +-- Gemini adapter
  +-- future provider adapters
```

The rest of WooGit calls a stable internal interface such as:

```text
chat(model_alias, messages, tools, limits)
```

The backend chooses the concrete provider.

## 4. AI credits

Track usage in a ledger.

A request should create a usage record with:

- account_id;
- site_id if applicable;
- provider;
- model;
- input units/tokens if available;
- output units/tokens if available;
- estimated cost;
- charged WooGit credits;
- request ID;
- timestamp.

Do not rely on client-reported token usage for billing.

## 5. Human handoff

Conversation states:

```text
ai
waiting_human
human
closed
```

AI should be able to transfer a conversation to a human queue based on configured rules or an explicit customer request.

## 6. Customer context

For questions such as “Where is my order?”, the AI receives authoritative tool output:

```text
Customer: opaque-id
Order: #1234
Status: processing
Items: ...
```

The model should not infer current order state from stale chat text.

## 7. Analytics

Events should be small, typed and queued.

Recommended pipeline:

```text
Bridge/browser
    -> ingestion API
    -> validation
    -> dedupe
    -> Redis queue
    -> worker
    -> analytics DB
```

Core dashboard metrics:

- visitors;
- sessions;
- product views;
- add-to-cart;
- checkout started;
- orders;
- conversion rate;
- chat starts;
- AI conversations;
- human handoffs.

## 8. User identity

Use separate concepts:

- anonymous visitor ID;
- authenticated WordPress customer reference;
- WooGit account ID.

Do not expose the internal WooGit account ID to the browser as a secret-bearing identifier.

## 9. Retention

Define retention by event class.

Example policy to configure later:

```text
raw events       -> short retention
aggregates       -> longer retention
chat messages    -> configurable
security audit   -> longer retention
```

Exact retention must be selected based on legal, product and storage requirements.
