#!/usr/bin/env bash
# Thin wrapper around wp/seed-script.mjs.
#
# Loads env vars from .env if present, then invokes the seed script.
# Pass --dry-run to preview the payload without hitting WP.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ -f "${ROOT}/.env" ]]; then
  set -a
  # shellcheck disable=SC1091
  source "${ROOT}/.env"
  set +a
fi

node "${ROOT}/wp/seed-script.mjs" "$@"
