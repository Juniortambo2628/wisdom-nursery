#!/usr/bin/env bash
# Launch helper, in cPanel Terminal, AFTER the first production deploy:
#   bash ~/nursery.wisdomcapital.co.ke/current/deploy/promote-staging-content.sh
# Copies the staging database (content the client approved on the preview) and
# uploaded photos to production, then removes the test orders/payments/logs.
# Staging is only read, never changed.
set -euo pipefail

STAGING="${STAGING_PATH:-$HOME/staging.nursery.wisdomcapital.co.ke}"
PROD="${PRODUCTION_PATH:-$HOME/nursery.wisdomcapital.co.ke}"
PHP_BIN="${PHP_BIN:-php}"

for d in "$STAGING/shared/.env" "$PROD/shared/.env" "$PROD/current/artisan"; do
  [[ -e "$d" ]] || { echo "Missing $d. Run the first production deploy before this." >&2; exit 1; }
done
case "$PROD" in *nursery*) ;; *) echo "Refusing: production path must contain 'nursery'." >&2; exit 1;; esac

# Read KEY from an .env file (handles quotes).
env_get() { php -r '$v=null;foreach(file($argv[1]) as $l){if(preg_match("/^".preg_quote($argv[2],"/")."=(.*)$/",trim($l),$m)){$v=trim($m[1]);$v=trim($v,"\"\x27");}}echo $v;' "$1" "$2"; }

S_DB="$(env_get "$STAGING/shared/.env" DB_DATABASE)"; S_USER="$(env_get "$STAGING/shared/.env" DB_USERNAME)"; S_PASS="$(env_get "$STAGING/shared/.env" DB_PASSWORD)"
P_DB="$(env_get "$PROD/shared/.env" DB_DATABASE)";    P_USER="$(env_get "$PROD/shared/.env" DB_USERNAME)";    P_PASS="$(env_get "$PROD/shared/.env" DB_PASSWORD)"
S_HOST="$(env_get "$STAGING/shared/.env" DB_HOST)"; P_HOST="$(env_get "$PROD/shared/.env" DB_HOST)"
[[ "$S_DB" != "$P_DB" ]] || { echo "Refusing: staging and production use the same database ($S_DB)." >&2; exit 1; }

# Refuse to overwrite a production database that already has real orders.
REAL_ORDERS="$(cd "$PROD/current" && "$PHP_BIN" artisan tinker --execute='echo \App\Models\Order::whereNotNull("paid_at")->count();' 2>/dev/null | tail -n1 || echo 0)"
if [[ "${REAL_ORDERS:-0}" =~ ^[0-9]+$ && "$REAL_ORDERS" -gt 0 && "${1:-}" != "--force" ]]; then
  echo "Production already has $REAL_ORDERS paid order(s). Refusing to overwrite it." >&2
  exit 1
fi

echo "This replaces the production database ($P_DB) with a copy of staging ($S_DB),"
echo "copies uploaded photos, then deletes test orders, payments, quotes, messages and logs."
read -rp "Type YES to continue: " ok; [[ "$ok" == "YES" ]] || { echo "Cancelled."; exit 1; }

WORK="$(mktemp -d)"; chmod 700 "$WORK"; trap 'rm -rf "$WORK"' EXIT
# Credentials go in option files (mode 600), never on the command line.
printf '[client]\nuser="%s"\npassword="%s"\nhost="%s"\n' "$S_USER" "$S_PASS" "${S_HOST:-localhost}" > "$WORK/s.cnf"
printf '[client]\nuser="%s"\npassword="%s"\nhost="%s"\n' "$P_USER" "$P_PASS" "${P_HOST:-localhost}" > "$WORK/p.cnf"
chmod 600 "$WORK"/*.cnf

echo "1/4 Backing up production database first…"
mkdir -p "$PROD/shared/storage/app/private"
mysqldump --defaults-extra-file="$WORK/p.cnf" --single-transaction --no-tablespaces "$P_DB" | gzip > "$PROD/shared/storage/app/private/before-promote-$(date +%Y%m%d%H%M%S).sql.gz"

echo "2/4 Copying staging database to production…"
mysqldump --defaults-extra-file="$WORK/s.cnf" --single-transaction --no-tablespaces "$S_DB" > "$WORK/staging.sql"
mysql --defaults-extra-file="$WORK/p.cnf" "$P_DB" < "$WORK/staging.sql"

echo "3/4 Copying uploaded photos…"
mkdir -p "$PROD/shared/storage/app/public"
cp -a "$STAGING/shared/storage/app/public/." "$PROD/shared/storage/app/public/"

echo "4/4 Clearing test data and refreshing caches…"
cd "$PROD/current"
"$PHP_BIN" artisan nursery:clear-test-data --force
"$PHP_BIN" artisan cache:clear
"$PHP_BIN" artisan optimize

echo "Done. Production has staging's content and no test orders."
echo "Now: re-check Settings → Site settings (contact details), Alert recipients, and switch M-Pesa/SMS to live keys."
