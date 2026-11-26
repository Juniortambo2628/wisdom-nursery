#!/usr/bin/env bash
# Builds production's shared/.env from staging's, in cPanel Terminal:
#   bash ~/staging.nursery.wisdomcapital.co.ke/current/deploy/make-production-env.sh
# Asks for the production database details. Generates a new APP_KEY and
# MPESA_CALLBACK_SECRET. M-Pesa and SMS stay on sandbox until you put the live
# keys in (launch checklist, section 3).
set -euo pipefail

STAGING="${STAGING_PATH:-$HOME/staging.nursery.wisdomcapital.co.ke}"
PROD="${PRODUCTION_PATH:-$HOME/nursery.wisdomcapital.co.ke}"
SRC="$STAGING/shared/.env"
DEST="$PROD/shared/.env"
ME="${USER:-$(whoami)}"

[[ -f "$SRC" ]] || { echo "Staging .env not found at $SRC" >&2; exit 1; }
if [[ -f "$DEST" && "${1:-}" != "--force" ]]; then
  echo "$DEST already exists. Re-run with --force to replace it (a backup copy is kept)." >&2
  exit 1
fi

read -rp "Production database name (e.g. ${ME}_nursery): " DB_NAME
read -rp "Production database user (e.g. ${ME}_nurseryprd): " DB_USER
read -rsp "Production database password: " DB_PASS; echo
[[ -n "$DB_NAME" && -n "$DB_USER" && -n "$DB_PASS" ]] || { echo "All three are required." >&2; exit 1; }

mkdir -p "$PROD/shared"
[[ -f "$DEST" ]] && cp -p "$DEST" "$DEST.bak-$(date +%Y%m%d%H%M%S)"

APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
CALLBACK="$(php -r 'echo bin2hex(random_bytes(24));')"

# set KEY VALUE: replace the line if present, append it otherwise (values are written quoted).
set_env() {
  local key="$1" value="$2" esc
  esc="$(printf '%s' "$value" | sed -e 's/[\\"]/\\&/g')"
  if grep -qE "^${key}=" "$TMP"; then
    php -r '$f=$argv[1];$k=$argv[2];$v=$argv[3];file_put_contents($f,preg_replace("/^".preg_quote($k,"/")."=.*$/m",$k."=\"".$v."\"",file_get_contents($f)));' "$TMP" "$key" "$esc"
  else
    printf '%s="%s"\n' "$key" "$esc" >> "$TMP"
  fi
}

TMP="$(mktemp)"; trap 'rm -f "$TMP"' EXIT
cp "$SRC" "$TMP"
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_KEY "$APP_KEY"
set_env APP_URL https://nursery.wisdomcapital.co.ke
set_env DB_DATABASE "$DB_NAME"
set_env DB_USERNAME "$DB_USER"
set_env DB_PASSWORD "$DB_PASS"
set_env CACHE_PREFIX wn_
set_env SESSION_SECURE_COOKIE true
set_env MPESA_CALLBACK_SECRET "$CALLBACK"

install -m 600 "$TMP" "$DEST"
echo "Wrote $DEST (permissions 600)."
echo "Still to change for launch: MPESA_ENV=production + live Daraja keys, AT_ENV=production + live SMS keys, GA4_MEASUREMENT_ID."
