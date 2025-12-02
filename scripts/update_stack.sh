#!/usr/bin/env bash
set -euo pipefail

# Simple updater: pulls latest code and rebuilds frontend/backend containers.

REPO_DIR="/data/nginx/team6-hackathon"
SERVICES="backend frontend"
NGINX_RELOAD=true # set to false to skip nginx reload check
NGINX_CONFIG_DIR="etc/nginx"

log() { printf '[%s] %s\n' "$(date '+%Y-%m-%dT%H:%M:%S%z')" "$*"; }

# Require running as root (e.g., via sudo) so docker and nginx commands work without inline sudo.
if [ "$(id -u)" -ne 0 ]; then
  echo "This script must be run as root (e.g., via sudo)." >&2
  exit 1
fi

# Ensure repo exists and is clean so scheduled runs don't clobber local edits.
if [ ! -d "$REPO_DIR/.git" ]; then
  echo "Repo not found at $REPO_DIR" >&2
  exit 1
fi

cd "$REPO_DIR"

if ! git diff --quiet --ignore-submodules --stat; then
  echo "Working tree has local changes; aborting update." >&2
  exit 1
fi

OLD_REF="$(git rev-parse HEAD)"

log "Fetching latest changes"
git fetch --all --prune

log "Fast-forwarding to latest"
git pull --ff-only

log "Rebuilding and restarting services: $SERVICES"
docker compose up -d --build $SERVICES

if [ "$NGINX_RELOAD" = true ]; then
  CHANGED_NGINX=$(git diff --name-only "$OLD_REF"...HEAD -- "$NGINX_CONFIG_DIR" || true)
  if [ -n "$CHANGED_NGINX" ]; then
    log "Nginx config changed; reloading"
    if nginx -t; then
      nginx -s reload
    else
      log "Nginx config test failed; not reloading"
      exit 1
    fi
  else
    log "No Nginx config changes detected; skipping reload"
  fi
fi

log "Update complete"
