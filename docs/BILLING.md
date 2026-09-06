# WooGit Billing and Entitlements

## 1. Commercial model

Initial proposal:

- 15-day free trial;
- time-based paid plans;
- optional AI credit packs;
- optional site-count or feature limits.

## 2. Trial

Trial is created server-side when the account becomes eligible.

```text
trial_started_at
trial_ends_at
status = trial
```

The mobile app may display the remaining time but cannot extend it.

## 3. Paid duration

A purchase extends the entitlement period according to the product rule.

Example:

```text
Current expiry: 2026-09-21
Buy 30 days
New expiry:     2026-10-21
```

If the account is already expired, the new period starts according to the configured billing rule.

## 4. Entitlement evaluation

Pseudo-policy:

```text
isAllowed(account, site, capability):
    account.status == active
    AND subscription.status in {trial, active}
    AND now < subscription.expires_at
    AND site belongs to account
    AND capability is included
    AND usage limits are not exceeded
```

The result is calculated by the backend for every protected request.

## 5. Grace period

If a payment provider is used, a configurable grace period may exist. It must be explicit and server-side; never assume payment success from a client callback.

## 6. AI credits

AI credits are separate from subscription time when commercially useful.

Ledger examples:

```text
+1,000,000 purchase
-12,400 inference usage
-8,000 inference usage
+500 promotional credit
```

Never allow the client to submit “remaining balance”.

## 7. Payment provider boundary

Payment providers should communicate with WooGit backend through signed/webhook-verified events. The app is not the payment authority.

The backend reconciles:

```text
payment event
 -> verify authenticity
 -> idempotently record transaction
 -> update subscription/credits
 -> audit
```

## 8. Plan configuration

Plans should be data-driven. Avoid hard-coding prices in Android or the Gateway.

A plan can define:

- name;
- duration;
- price;
- currency;
- site limit;
- feature set;
- AI credit allocation;
- analytics retention;
- chat limits.

## 9. Expiration behavior

When expired:

- protected API requests are rejected;
- outbound customer-site calls are blocked;
- existing cached UI data may be shown if policy allows;
- background jobs requiring entitlement stop or are marked paused;
- customer data is retained/deleted according to the retention policy.

## 10. Security invariant

A user must never be able to restore service by modifying:

- APK flags;
- local subscription timestamps;
- cached plan data;
- local “premium” state.

Only the backend can grant an entitlement.
