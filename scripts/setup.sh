#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

set_env_var() {
    local env_file="$1"
    local key="$2"
    local value="$3"

    touch "$env_file"

    if grep -q "^${key}=" "$env_file" 2>/dev/null; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$env_file"
    else
        printf '%s=%s\n' "$key" "$value" >> "$env_file"
    fi
}

ENV_LOCAL="$REPO_ROOT/.env.local"

set_env_var "$ENV_LOCAL" "APP_ENV" "prod"
set_env_var "$ENV_LOCAL" "APP_DEBUG" "0"
set_env_var "$ENV_LOCAL" "APP_SECRET" "$(openssl rand -hex 32)"

echo "Wrote APP_ENV=prod, APP_DEBUG=0 and a fresh APP_SECRET to .env.local."
