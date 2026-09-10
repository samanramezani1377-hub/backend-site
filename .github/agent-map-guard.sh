#!/usr/bin/env bash
set -euo pipefail

BASE="${1:-HEAD^}"
HEAD="${2:-HEAD}"
mapfile -t changed < <(git diff --name-only "$BASE" "$HEAD")

backend_code=0
theme_code=0
backend_map=0
theme_map=0
for path in "${changed[@]}"; do
  case "$path" in
    docs/agent-backend/*) backend_map=1 ;;
    docs/agent-theme/*) theme_map=1 ;;
    plugin/**|tests/plugin/**) backend_code=1 ;;
    theme/woogit/**|tests/theme/**|tools/preview/**) theme_code=1 ;;
  esac
done

failed=0
if (( backend_code == 1 && backend_map == 0 )); then
  echo "AGENT MAP GUARD FAILED: Backend/plugin code changed without docs/agent-backend/** update."
  failed=1
fi
if (( theme_code == 1 && theme_map == 0 )); then
  echo "AGENT MAP GUARD FAILED: Theme code changed without docs/agent-theme/** update."
  failed=1
fi
if (( failed == 1 )); then
  echo 'Changed paths:'
  printf '%s\n' "${changed[@]}"
  exit 1
fi

echo 'AGENT MAP GUARD PASSED: every changed Backend/Theme code surface has its corresponding map update, or this is a map-only/non-runtime change.'
