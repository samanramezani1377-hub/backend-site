# Theme Cross-Repository Map

## Surfaces

- App: `samanramezani1377-hub/woogit` / `main`.
- Backend: `plugin/woogit-backend` in this repository / `main`.
- Theme: `theme/woogit` in this repository / Theme implementation work may be staged on `theme-v1-implementation`, but this map is committed on `main`.

## Shared business concepts

Account, Site, Entitlement and Billing are backend-owned concepts. App and Theme consume explicit contracts; neither client may invent authorization state.

## Session boundary

`App Operational Session != App Billing Session != Theme Web Session`.

## Billing boundary

App billing APIs and Theme web billing APIs may implement the same business operation through different authentication surfaces. Do not copy App headers/session tokens into Theme code.

## Update rule

Any cross-surface contract change must update the affected App/Backend/Theme map(s) in the same code change. A documentation-only map update never requires a code change.
