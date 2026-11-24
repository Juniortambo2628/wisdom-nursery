#!/usr/bin/env bash
# Runs ON THE SERVER over SSH, called by .github/workflows/deploy.yml.
# Activates a release that has already been uploaded to $DEPLOY_PATH/releases/$RELEASE.
#
# Layout (everything lives under DEPLOY_PATH, nothing outside it is touched):
#   $DEPLOY_PATH/releases/<sha>/   one directory per deploy (last $KEEP_RELEASES kept)
#   $DEPLOY_PATH/shared/.env       written once by hand (see README "Deploy")
#   $DEPLOY_PATH/shared/storage/   uploads, logs, backups; survives deploys
#   $DEPLOY_PATH/current           symlink to the live release
# The cPanel (sub)domain document root must point at $DEPLOY_PATH/current/public.
set -euo pipefail

: "${DEPLOY_PATH:?DEPLOY_PATH is required}"
: "${RELEASE:?RELEASE is required}"
PHP_BIN="${PHP_BIN:-php}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"

# --- Guard rails: refuse anything that could reach the Wisdom Capital site ----
case "$DEPLOY_PATH" in
  /*) ;;
  *) echo "DEPLOY_PATH must be absolute" >&2; exit 1 ;;
esac
DEPLOY_PATH="${DEPLOY_PATH%/}"
if [[ "$DEPLOY_PATH" == "$HOME" || "$DEPLOY_PATH" == "$HOME/public_html"* || "$DEPLOY_PATH" == "/" ]]; then
  echo "Refusing to deploy into $DEPLOY_PATH (account root or the main site's public_html)" >&2
  exit 1
fi
if [[ "$DEPLOY_PATH" != *nursery* ]]; then
  echo "Refusing: DEPLOY_PATH must contain 'nursery' so it can never point at another site" >&2
  exit 1
fi
if [[ ! "$RELEASE" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "Invalid RELEASE name" >&2; exit 1
fi

RELEASE_DIR="$DEPLOY_PATH/releases/$RELEASE"
SHARED="$DEPLOY_PATH/shared"
[[ -d "$RELEASE_DIR" ]] || { echo "Release $RELEASE_DIR was not uploaded" >&2; exit 1; }

# --- Shared files --------------------------------------------------------------
mkdir -p "$SHARED"/storage/{app/public,framework/{cache/data,sessions,views},logs}
if [[ ! -f "$SHARED/.env" ]]; then
  echo "Missing $SHARED/.env. Create it from .env.example before the first deploy (README > Deploy)." >&2
  exit 1
fi

rm -rf "$RELEASE_DIR/storage"
ln -sfn "$SHARED/storage" "$RELEASE_DIR/storage"
ln -sfn "$SHARED/.env" "$RELEASE_DIR/.env"
mkdir -p "$RELEASE_DIR/bootstrap/cache"

cd "$RELEASE_DIR"

# --- Laravel -------------------------------------------------------------------
"$PHP_BIN" artisan migrate --force --no-interaction

# public/storage -> storage/app/public (uploaded photos). Relative, so it follows the release.
[[ -L public/storage ]] || ln -sfn ../storage/app/public public/storage

# Content seeders. Safe on every deploy: each seeder runs once per database and
# is recorded in `settings` (see database/seeders/DatabaseSeeder.php), so the
# team's edits are never overwritten and a seeder added later runs once.
"$PHP_BIN" artisan db:seed --force --no-interaction

"$PHP_BIN" artisan optimize --no-interaction
"$PHP_BIN" artisan filament:optimize --no-interaction 2>/dev/null || true

# --- Go live (atomic symlink swap) --------------------------------------------
# cPanel creates the document root (current/public) as a real folder when the
# domain is added. Move that placeholder aside (never delete) so the symlink fits.
if [[ -d "$DEPLOY_PATH/current" && ! -L "$DEPLOY_PATH/current" ]]; then
  mv "$DEPLOY_PATH/current" "$DEPLOY_PATH/cpanel-placeholder-$(date +%Y%m%d%H%M%S)"
fi
ln -sfn "$RELEASE_DIR" "$DEPLOY_PATH/current.tmp"
mv -Tf "$DEPLOY_PATH/current.tmp" "$DEPLOY_PATH/current"

# Restart queue workers started by the scheduler so they pick up new code.
"$PHP_BIN" artisan queue:restart --no-interaction || true

# --- Prune old releases (only inside $DEPLOY_PATH/releases) -------------------
cd "$DEPLOY_PATH/releases"
ls -1t | grep -E '^[A-Za-z0-9._-]+$' | tail -n +"$((KEEP_RELEASES + 1))" | while read -r old; do
  [[ "$old" == "$RELEASE" ]] && continue
  rm -rf -- "$DEPLOY_PATH/releases/$old"
done

echo "Deployed $RELEASE to $DEPLOY_PATH"
