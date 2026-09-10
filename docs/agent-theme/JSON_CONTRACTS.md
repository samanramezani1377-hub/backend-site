# Theme JSON Contract Map

Theme contracts include JSON, form POST fields, query parameters, hidden inputs, cookies/session values and backend response fields.

## Shared billing JSON

Theme may consume billing plan/status/checkout data only through the documented web/API contract actually used by its source.

Important searchable fields include:

`account_id`, `site_id`, `status`, `starts_at`, `expires_at`, `capabilities`, `trial_used`, `code`, `message`, `data`.

## Web form contract

Forms are contracts too. When a form field is added/removed/renamed or its validation/redirect behavior changes, document it here and in the API map.

## Rule

Never infer Theme JSON from App DTOs. Verify the producing controller/client and the Theme consumer in source before editing.
