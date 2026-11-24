# Staging setup guide (first deploy)

Goal: **https://staging.nursery.wisdomcapital.co.ke** live for the client's preview, deployed
automatically on every push to the `staging` branch. About 45 minutes, done once.

> This repo is public. Keep real values (host, port, username, passwords, keys) out of it:
> they go in cPanel, in `shared/.env` on the server, or in GitHub secrets only.
> Below, `<user>` is the cPanel username, `<host>` the server and `<port>` the SSH port
> Truehost gave you.

Already done: DNS record for `staging.nursery.wisdomcapital.co.ke`, SSH enabled on the
account, deploy key generated.

---

## Part A: cPanel

### 1. Authorise the deploy key
1. cPanel → **SSH Access** → **Manage SSH Keys** → **Import Key**.
2. Name: `wisdom-nursery-deploy`. Paste the **public** key
   (`ssh-ed25519 AAAAC3Nza… github-actions-wisdom-nursery-deploy`) into *Public Key*.
   Leave the private key and passphrase boxes empty. **Import**.
3. Back on Manage SSH Keys, under *Public Keys*, click **Manage** next to
   `wisdom-nursery-deploy` → **Authorize**.

### 2. Test the key and find the PHP binary (from your laptop)
```bash
chmod 600 wisdom_nursery_deploy
ssh -i wisdom_nursery_deploy -p <port> <user>@<host> 'php -v; ls -d /opt/cpanel/ea-php* /opt/alt/php* 2>/dev/null'
```
- It should log in without a password and print the PHP version.
- If `php -v` shows **8.2 or newer**, nothing more to do.
- If it shows an older version, pick a newer binary from the list it printed, e.g.
  `/opt/cpanel/ea-php83/root/usr/bin/php` or `/opt/alt/php83/usr/bin/php`, and note it as
  **PHP_BIN** (used in steps 9, 11 and 13).

### 3. Create the staging domain
cPanel → **Domains** → **Create A New Domain**:
- Domain: `staging.nursery.wisdomcapital.co.ke`
- **Untick** "Share document root"
- Document root: `staging.nursery.wisdomcapital.co.ke/current/public`
- **Submit**. (cPanel creates that folder; the first deploy moves it aside automatically.)

### 4. PHP version and extensions
- cPanel → **MultiPHP Manager**: tick `staging.nursery.wisdomcapital.co.ke`, set **PHP 8.3**
  (or 8.2) → Apply.
- cPanel → **Select PHP Version** (or MultiPHP INI Editor) for that version: make sure these
  extensions are on: `pdo_mysql, mbstring, intl, gd, zip, bcmath, fileinfo, sodium`.

### 5. HTTPS
cPanel → **SSL/TLS Status** → tick the staging domain → **Run AutoSSL**. Wait until it shows a
valid certificate. (The dashboard login needs HTTPS.)

### 6. Database
cPanel → **Manage My Databases**:
1. Create database `nursery_staging` (cPanel names it `<user>_nursery_staging`).
2. Create user `nurserystg` with a strong generated password (save it).
3. **Add User To Database** → that user and database → **ALL PRIVILEGES** → Make Changes.

This is a separate database: the Wisdom Capital site's database is never touched.

### 7. (Optional now) Sending email
For order alert emails: cPanel → **Email Accounts** → create e.g. `orders@wisdomcapital.co.ke`.
Then **Connect Devices** shows the SMTP server and port (usually 465/SSL). On staging you can
skip this and set `MAIL_MAILER=log` in step 8.

### 8. Create the shared `.env` on the server
```bash
ssh -i wisdom_nursery_deploy -p <port> <user>@<host>
mkdir -p ~/staging.nursery.wisdomcapital.co.ke/shared
cd ~/staging.nursery.wisdomcapital.co.ke/shared
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"   # copy the output: APP_KEY
openssl rand -hex 24                                                # copy the output: MPESA_CALLBACK_SECRET
nano .env      # paste the template below, fill in, Ctrl+O, Enter, Ctrl+X
chmod 600 .env
```

Template (staging):
```dotenv
APP_NAME="Wisdom Nursery"
APP_ENV=staging
APP_KEY=base64:PASTE_FROM_ABOVE
APP_DEBUG=false
APP_TIMEZONE=Africa/Nairobi
APP_URL=https://staging.nursery.wisdomcapital.co.ke

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<user>_nursery_staging
DB_USERNAME=<user>_nurserystg
DB_PASSWORD=PASTE_DB_PASSWORD

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=database
CACHE_PREFIX=wn_stg_

# Email: "log" on staging, or the SMTP details from step 7
MAIL_MAILER=log
MAIL_FROM_ADDRESS="orders@wisdomcapital.co.ke"
MAIL_FROM_NAME="Wisdom Nursery"

NURSERY_WHATSAPP=2547XXXXXXXX
GA4_MEASUREMENT_ID=
MPESA_CALLBACK_SECRET=PASTE_FROM_ABOVE

# M-Pesa sandbox (keys from your Daraja app; leave empty to run without the prompt)
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=174379
MPESA_TYPE=paybill
MPESA_PARTY_B=174379
MPESA_PASSKEY=
MPESA_ACCOUNT_REFERENCE="Wisdom Nursery"

# SMS sandbox (leave empty for now: SMS alerts are logged as "skipped")
AT_ENV=sandbox
AT_USERNAME=sandbox
AT_API_KEY=
AT_SENDER_ID=

WHATSAPP_DRIVER=log
BACKUP_NOTIFY_EMAIL=
```

### 9. Cron job (queue, alerts, backups, M-Pesa checks)
cPanel → **Cron Jobs** → Add New Cron Job → Common settings: **Once Per Minute**. Command:
```
cd /home/<user>/staging.nursery.wisdomcapital.co.ke/current && php artisan schedule:run >> /dev/null 2>&1
```
(Use the PHP_BIN path instead of `php` if step 2 needed one.) It is harmless before the
first deploy.

---

## Part B: GitHub

Repository → **Settings**.

### 10. Repository secrets
**Secrets and variables → Actions → New repository secret**, five times:

| Name | Value |
|---|---|
| `SSH_HOST` | `<host>` |
| `SSH_PORT` | `<port>` |
| `SSH_USER` | `<user>` |
| `SSH_PRIVATE_KEY` | the whole private key file `wisdom_nursery_deploy`, including the `-----BEGIN…` and `-----END…` lines |
| `SSH_KNOWN_HOSTS` | the output of `ssh-keyscan -p <port> <host>` run on your laptop (all lines) |

### 11. The `staging` environment
**Environments → New environment** → `staging` → under **Environment variables** add:

| Name | Value |
|---|---|
| `DEPLOY_PATH` | `/home/<user>/staging.nursery.wisdomcapital.co.ke` |
| `PHP_BIN` | only if step 2 needed a different binary |

(Create `production` the same way at launch, with
`DEPLOY_PATH=/home/<user>/nursery.wisdomcapital.co.ke` and a **Required reviewer** so a push
to `main` waits for your approval.)

### 12. Make sure Actions can run
**Actions → General** → "Allow all actions and reusable workflows" → Save.

### 13. Create the `staging` branch: this triggers the first deploy
Code → branch menu → type `staging` → **Create branch staging from
`claude/exciting-volta-s8ipd3`**. (Or ask Claude to push it.)

Open the **Actions** tab: "Test and deploy" runs the tests (about 2 min), then deploys
(about 2 min). Green tick = live.

---

## Part C: after the first deploy

### 14. Create the dashboard login(s)
```bash
ssh -i wisdom_nursery_deploy -p <port> <user>@<host>
cd ~/staging.nursery.wisdomcapital.co.ke/current
php artisan make:filament-user        # name, email, password
```

### 15. Check it
- https://staging.nursery.wisdomcapital.co.ke: home page with the [bracketed] placeholders.
- https://staging.nursery.wisdomcapital.co.ke/admin: log in; **Settings → Site settings**:
  fill in what you already know (phone, hours, till).

### 16. (Optional) Sample plants for the client preview
Until the real plant list is imported the shop is empty. To show the client the shop with the
deck's sample plants:
```bash
php artisan db:seed --class=DemoPlantSeeder
```
Remove them before importing the real list:
```bash
php artisan tinker --execute="App\Models\Plant::whereNull('source_ref')->delete();"
```

### 17. Share with the client
Send the link and say it is a **preview with placeholder text and photos**. Search engines are
blocked on staging automatically. Do **not** add cPanel *Directory Privacy* (password) to the
staging folder: it would also block Safaricom's payment callbacks.

From now on every push to `staging` updates the preview within a few minutes.

---

## Troubleshooting

| Symptom (Actions log or browser) | Fix |
|---|---|
| `Permission denied (publickey)` | Key not **Authorized** in cPanel (step 1), or `SSH_USER` wrong. |
| `Host key verification failed` | `SSH_KNOWN_HOSTS` missing or for another port: rerun `ssh-keyscan -p <port> <host>`. |
| `Set the DEPLOY_PATH variable on this environment` | Step 11: the variable is on the `staging` **environment**, not a repository variable. |
| `Missing …/shared/.env` | Step 8. |
| `Your PHP version … does not satisfy` / syntax errors | CLI PHP is older than 8.2: set `PHP_BIN` (step 2, 11). |
| Browser shows the cPanel default page | Document root not `…/current/public` (step 3). |
| 500 error | Read `~/staging.nursery.wisdomcapital.co.ke/shared/storage/logs/laravel-*.log`; most often DB credentials in `.env`. After editing `.env`: `cd …/current && php artisan optimize`. |
| Dashboard login keeps returning to the login page | HTTPS not active yet (step 5). |
| Alerts or backups never run | Cron job (step 9). |

Rollback: `ln -sfn ~/staging.nursery.wisdomcapital.co.ke/releases/<older-sha> ~/staging.nursery.wisdomcapital.co.ke/current`.
