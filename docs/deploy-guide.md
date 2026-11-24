# Staging setup guide (first deploy)

Goal: **https://staging.nursery.wisdomcapital.co.ke** live for the client's preview, deployed
automatically on every push to the `staging` branch. About 45 minutes, done once.

> This repo is public. Keep real values (host, port, username, passwords, keys) out of it:
> they go in cPanel, in `shared/.env` on the server, or in GitHub secrets only.
> Below, `<user>` is the cPanel username, `<host>` the server and `<port>` the SSH port
> Truehost gave you.

Already done: DNS record for `staging.nursery.wisdomcapital.co.ke`, SSH enabled on the
account.

---

## Part A: cPanel

### 1. Generate the deploy key on your laptop

The key is made on your own machine, so the private half never passes through anyone else.
It is used only by GitHub Actions to deploy; keep your own cPanel login separate.

**macOS / Linux:**
```bash
mkdir -p ~/.ssh && ssh-keygen -t ed25519 -C "github-actions-wisdom-nursery-deploy" -f ~/.ssh/wisdom_nursery_deploy
```

**Windows (PowerShell):** `ssh-keygen` does not understand `~`, and `.ssh` may not exist yet:
```powershell
New-Item -ItemType Directory -Force "$HOME\.ssh" | Out-Null
ssh-keygen -t ed25519 -C "github-actions-wisdom-nursery-deploy" -f "$HOME\.ssh\wisdom_nursery_deploy"
```
Press **Enter twice** when asked for a passphrase (GitHub Actions cannot type one).
On Windows, use `"$HOME\.ssh\wisdom_nursery_deploy"` wherever this guide writes
`~/.ssh/wisdom_nursery_deploy`.

This makes two files:
- `wisdom_nursery_deploy.pub`: the **public** key → the server (step 1b)
- `wisdom_nursery_deploy`: the **private** key → GitHub secret `SSH_PRIVATE_KEY` (step 10) and nowhere else

Show the public key to copy it:
```bash
cat ~/.ssh/wisdom_nursery_deploy.pub                      # macOS / Linux
Get-Content "$HOME\.ssh\wisdom_nursery_deploy.pub"         # Windows PowerShell
```

### 1b. Add the public key to `authorized_keys` on the server

cPanel → **Terminal** (or **File Manager** with "Show Hidden Files" on). In Terminal:
```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
```
Add **one new line** at the end (keep any lines already there), made of these options, a
space, then the whole public key:
```
no-port-forwarding,no-agent-forwarding,no-X11-forwarding,no-pty ssh-ed25519 AAAA…your key… github-actions-wisdom-nursery-deploy
```
Save (Ctrl+O, Enter, Ctrl+X), then:
```bash
chmod 600 ~/.ssh/authorized_keys
```
The options limit this key to running commands: it cannot open tunnels or an interactive
terminal, which is all the deploy needs.

(File Manager instead: open `/home/<user>/.ssh/authorized_keys`, or create it, add the same
line, then set permissions to 600 and the `.ssh` folder to 700.)

### 2. Test the key and find the PHP binary (from your laptop)
```bash
ssh -i ~/.ssh/wisdom_nursery_deploy -p <port> <user>@<host> 'php -v; ls -d /opt/cpanel/ea-php* /opt/alt/php* 2>/dev/null'
```
- It should run without asking for a password and print the PHP version. (A note that no
  terminal was allocated is expected: that is the `no-pty` option.)
- If `php -v` shows **8.2 or newer**, nothing more to do.
- If it shows an older version, pick a newer binary from the list it printed, e.g.
  `/opt/cpanel/ea-php83/root/usr/bin/php` or `/opt/alt/php83/usr/bin/php`, and note it as
  **PHP_BIN** (used in steps 9, 11 and 14).

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
In cPanel → **Terminal** (the deploy key has no interactive terminal, by design):
```bash
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
| `SSH_PRIVATE_KEY` | the whole private key file `~/.ssh/wisdom_nursery_deploy`, including the `-----BEGIN…` and `-----END…` lines |
| `SSH_KNOWN_HOSTS` | the output of `ssh-keyscan -p <port> <host>` run on your laptop (all lines) |

Show the private key to copy it: `cat ~/.ssh/wisdom_nursery_deploy` (PowerShell:
`Get-Content ~/.ssh/wisdom_nursery_deploy -Raw`). Paste it straight into the secret box and
don't save it anywhere else. With the GitHub CLI you can skip copy-paste entirely:
```bash
# macOS / Linux
gh secret set SSH_PRIVATE_KEY --repo Juniortambo2628/wisdom-nursery < ~/.ssh/wisdom_nursery_deploy
ssh-keyscan -p <port> <host> | gh secret set SSH_KNOWN_HOSTS --repo Juniortambo2628/wisdom-nursery
```
```powershell
# Windows PowerShell (no "<" redirection: pipe instead)
Get-Content "$HOME\.ssh\wisdom_nursery_deploy" -Raw | gh secret set SSH_PRIVATE_KEY --repo Juniortambo2628/wisdom-nursery
ssh-keyscan -p <port> <host> | gh secret set SSH_KNOWN_HOSTS --repo Juniortambo2628/wisdom-nursery
```

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
In cPanel → **Terminal**:
```bash
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
| `Permission denied (publickey)` | The public key line is missing or broken in `~/.ssh/authorized_keys` (one line, no line breaks), permissions are not `700` on `~/.ssh` / `600` on the file (step 1b), or `SSH_USER` is wrong. |
| `Host key verification failed` | `SSH_KNOWN_HOSTS` missing or for another port: rerun `ssh-keyscan -p <port> <host>`. |
| `Set the DEPLOY_PATH variable on this environment` | Step 11: the variable is on the `staging` **environment**, not a repository variable. |
| `Missing …/shared/.env` | Step 8. |
| `Your PHP version … does not satisfy` / syntax errors | CLI PHP is older than 8.2: set `PHP_BIN` (step 2, 11). |
| Browser shows the cPanel default page | Document root not `…/current/public` (step 3). |
| 500 error | Read `~/staging.nursery.wisdomcapital.co.ke/shared/storage/logs/laravel-*.log`; most often DB credentials in `.env`. After editing `.env`: `cd …/current && php artisan optimize`. |
| Dashboard login keeps returning to the login page | HTTPS not active yet (step 5). |
| Alerts or backups never run | Cron job (step 9). |

Replacing the key later: generate a new one (step 1), add its line to `authorized_keys`,
update `SSH_PRIVATE_KEY`, then delete the old line.

Rollback: `ln -sfn ~/staging.nursery.wisdomcapital.co.ke/releases/<older-sha> ~/staging.nursery.wisdomcapital.co.ke/current`.
