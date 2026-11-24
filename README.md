# Wisdom Nursery website

The website, online shop and order dashboard for Wisdom Nursery (Wisdom Capital Limited).
Built by OKJTechnologies under proposal OKJ-WN-2026-01 Rev 3 and agreement OKJ-WN-2026-02 (both in `docs/`).
Progress against the agreed scope is tracked in [`docs/plan.md`](docs/plan.md).

**Stack:** Laravel 12 · PHP 8.2+ · MySQL · Blade + Vite + Tailwind CSS + Alpine.js · Filament 3 (dashboard at `/admin`) · Pest.

## Local setup

```bash
composer install
npm ci
cp .env.example .env    # then set DB_CONNECTION=sqlite, APP_ENV=local, APP_DEBUG=true for local work
php artisan key:generate
php artisan migrate --seed
php artisan make:filament-user
npm run dev              # in one terminal
php artisan serve        # in another
php artisan test         # Pest
```

Content (categories, settings, launch guides, placeholders, the plant import) comes from seeders in
`database/seeders`. Each seeder runs **once per database** and is recorded in `settings` as
`seeded:<Class>`, so `php artisan db:seed` is safe to run on every deploy and never overwrites the
team's edits. To re-run one deliberately: delete its `seeded:` row, then `php artisan db:seed`.

The plant import reads `database/seeders/data/wisdom-capital-products.csv` and
`database/seeders/data/images/`. Until that CSV is committed the importer skips and retries on the
next deploy.

## Deploy

**First time? Follow [`docs/deploy-guide.md`](docs/deploy-guide.md)** (cPanel + GitHub, step by step).

Builds run in GitHub Actions; the server only receives a ready-to-run release over SSH.
Workflow: [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml). Server-side steps:
[`deploy/remote.sh`](deploy/remote.sh). Background and decisions: [`docs/deploy-notes.md`](docs/deploy-notes.md).

| Branch | Deploys to | GitHub Environment |
|---|---|---|
| `staging` | staging subdomain (client preview link) | `staging` |
| `main` | production | `production` |

Every push and pull request runs the test suite first; a failing test blocks the deploy.

### Each deploy

1. Actions: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`.
2. Upload as `DEPLOY_PATH/releases/<commit-sha>/` (tar over SSH; no rsync or Composer needed on the server).
3. On the server: link the shared `.env` and `storage/`, `php artisan migrate --force`,
   `storage:link` (first time), `db:seed --force` (each seeder once), `optimize`.
4. Switch `DEPLOY_PATH/current` to the new release (atomic symlink swap), `queue:restart`,
   and keep the last 5 releases.

Rollback: on the server, `ln -sfn DEPLOY_PATH/releases/<older-sha> DEPLOY_PATH/current`
(migrations are not rolled back automatically).

### Safety

The deploy only ever writes inside `DEPLOY_PATH`. Both the workflow and `remote.sh` refuse a path
that is not absolute, is the account home or `public_html`, or does not contain `nursery`. Nothing
of the Wisdom Capital site (files, database, cron) is touched. The nursery uses its own MySQL
database and user.

### Repository secrets (Settings → Secrets and variables → Actions)

| Secret | Value |
|---|---|
| `SSH_HOST` | cPanel server hostname |
| `SSH_PORT` | SSH port given by the host (often not 22 on shared hosting) |
| `SSH_USER` | cPanel username |
| `SSH_PRIVATE_KEY` | private half of a deploy key made for this repo (public half added in cPanel → SSH Access → Manage SSH Keys, then **Authorize**) |
| `SSH_KNOWN_HOSTS` | output of `ssh-keyscan -p <port> <host>` |

### Environment variables (Settings → Environments → `production` / `staging`)

| Variable | Example | Notes |
|---|---|---|
| `DEPLOY_PATH` | `/home/<cpanel-user>/nursery.wisdomcapital.co.ke` | `{{NURSERY_PATH}}` / `{{STAGING_PATH}}`. Outside `public_html`. Must contain `nursery`. |
| `PHP_BIN` | `/opt/cpanel/ea-php82/root/usr/bin/php` | Optional. CLI PHP matching the site's PHP version; defaults to `php`. |

Add a required reviewer on the `production` environment if launches should need a manual approval.

### One-time server setup (per environment)

1. **Database:** cPanel → MySQL Databases: create a database and user for the nursery and grant all privileges.
2. **Domain:** cPanel → Domains: create the (sub)domain with document root
   `DEPLOY_PATH/current/public`. Run AutoSSL so it gets HTTPS.
3. **PHP:** cPanel → MultiPHP Manager: set the domain to PHP 8.2 or newer, with extensions
   `pdo_mysql, mbstring, intl, gd, zip, bcmath, fileinfo` (MultiPHP INI / Select PHP Version).
4. **Shared `.env`:** create `DEPLOY_PATH/shared/.env` from `.env.example`, fill in the database,
   mail, M-Pesa and SMS values, then generate the key with
   `php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"` and paste it as `APP_KEY`.
   For staging, also set `APP_ENV=staging` and `MPESA_ENV=sandbox`.
5. **Push** to the branch. The first deploy creates the tables and seeds the content.
6. **Dashboard login:** `cd DEPLOY_PATH/current && php artisan make:filament-user`.
7. **Cron** (below).

### cPanel cron entries

cPanel → Cron Jobs, one entry per environment, every minute:

```
* * * * * cd DEPLOY_PATH/current && php artisan schedule:run >> /dev/null 2>&1
```

Use the same `PHP_BIN` path as above if `php` is not the right version. The scheduler
(`routes/console.php`) then runs:

- the queue (`queue:work --stop-when-empty --max-time=50`) every minute: order alerts, SMS and emails
- the database backup daily at 02:00, and uploaded photos weekly; retention keeps a week of dailies
  and 4 weekly backups (SLA 9.2), stored in `DEPLOY_PATH/shared/storage/app/private/`
- `backup:monitor` daily (emails `BACKUP_NOTIFY_EMAIL` if backups stop)
- pruning of old notification logs, failed jobs and password resets

## Payments (M-Pesa)

Safaricom Daraja STK Push (`app/Services/Mpesa`). At checkout the customer gets the M-Pesa
prompt on their phone; the order page waits and updates by itself.

- **Callback:** `https://<domain>/api/mpesa/callback/<MPESA_CALLBACK_SECRET>`. Each
  `CheckoutRequestID` is processed once, the amount is checked against the order, then the
  order is marked paid, stock is reduced and the alerts go out. Raw callbacks are kept on the
  payment and in the daily log.
- **Missed callbacks:** `mpesa:reconcile` (scheduler, every 2 minutes) asks Safaricom about
  prompts older than 90 seconds.
- **Fallback:** "No prompt? Pay to Till … and enter the M-Pesa code". The code is stored as
  *pending verification*; staff confirm it on the order (Payments → Confirm payment) after
  checking the M-Pesa statement. Short payments land there too.
- **Without credentials** (until Daraja is approved) no prompt is sent: the order page offers
  the till / code fallback and WhatsApp.
- **Going live:** in `shared/.env` set `MPESA_ENV=production`, the production consumer
  key/secret and passkey, `MPESA_TYPE` (`paybill` or `till`), `MPESA_SHORTCODE` (the paybill,
  or the till's store number) and `MPESA_PARTY_B` (the till customers pay to). Then make a
  KSh 10 test purchase.

## Alerts

Every paid order and quote request alerts each active **alert recipient** (Dashboard →
Settings → Alert recipients) on the channels they chose; customers get SMS on payment, ready
for collection and dispatched. Messages go through the queue (`SendAlert` job) and every
attempt is recorded in **Settings → Alert log** (sent / failed / skipped / logged).

| Channel | Provider | Settings |
|---|---|---|
| SMS | Africa's Talking | `AT_ENV`, `AT_USERNAME`, `AT_API_KEY`, `AT_SENDER_ID` (sandbox first) |
| WhatsApp | WhatsApp Cloud API, or `log` until templates are approved | `WHATSAPP_*`, see [`docs/whatsapp-templates.md`](docs/whatsapp-templates.md) |
| Email | server SMTP | `MAIL_*` |

SMS without credentials is logged as *skipped* rather than retried. Use **Send test** on a
recipient to check a channel end to end.

## Order dashboard

`/admin` (Filament), built to work on a phone. Create staff logins with
`php artisan make:filament-user`.

- **Dashboard:** sales today, orders to prepare, new quotes, payments to check, and the
  orders to prepare with one-tap **Mark ready / Mark dispatched / Mark collected /
  Mark delivered** and **Call**.
- **Orders:** filters (to prepare, today, payment to verify), order view with items and
  payments, cancel.
- **Plants:** edit price, stock and availability straight from the list; photos, care facts,
  "goes well with"; select plants → **Print QR labels** (A4 PDF linking to each plant page).
- **Quote requests** (status, call, reply on WhatsApp), **Messages**, **Categories**,
  **Delivery areas**, **Grower tips**, **Team**, **Gallery**, **Testimonials**, **Partners**.
- **Settings:** site settings (contact, hours, till, home and about text), alert recipients,
  alert log.

## Placeholders

| Placeholder | Where it is set |
|---|---|
| `{{DOMAIN}}` / `{{STAGING_DOMAIN}}` | cPanel domain + `APP_URL` in each `shared/.env` |
| `{{NURSERY_PATH}}` / `{{STAGING_PATH}}` | `DEPLOY_PATH` on each GitHub Environment |
| `{{NURSERY_WHATSAPP}}` | `NURSERY_WHATSAPP` in `shared/.env` |
| `{{TILL}}` | `MPESA_PARTY_B` in `shared/.env` and `till_number` in Settings |
