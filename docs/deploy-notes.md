# Deploy notes

## Background

The handoff asked us to copy the Wisdom Capital site's deploy workflow. On 24 Nov 2026 Kevin confirmed
that site was never pushed to GitHub, so there is no workflow to copy. The nursery deploy is set up
fresh, designed for the same cPanel shared server.

## What we know about the server

From the cPanel screens (24 Nov 2026). Values are kept out of this public repo; they go into GitHub
secrets only.

| Item | Status |
|---|---|
| Hosting | Shared cPanel (Jupiter theme, cPanel 138) at Truehost; same account as wisdomcapital.co.ke |
| DNS | `nursery.wisdomcapital.co.ke` (A) + `www.nursery…` (CNAME) for production; `staging.nursery.wisdomcapital.co.ke` (A) for staging, all created 24 Nov |
| SSL | AutoSSL active on the account |
| SSH | **Enabled** by Truehost support (24 Nov), Bash shell, non-standard port (in the `SSH_PORT` secret). Deploy key `github-actions-wisdom-nursery-deploy` (ed25519) to be authorised in cPanel → SSH Access. |
| CLI PHP version | **To confirm** with `php -v` over SSH. App targets 8.2+ (Composer platform pinned to 8.2). If `php` is older, use the `ea-php82`/`alt-php82` binary path as `PHP_BIN`. |
| Composer / Node / rsync on server | **Not needed.** Everything is built in GitHub Actions; upload uses `tar` over SSH. |
| MySQL | Separate database and user for the nursery (never the Wisdom Capital database). |

## Design

- **Connection:** SSH with a dedicated deploy key (secrets `SSH_HOST`, `SSH_PORT`, `SSH_USER`,
  `SSH_PRIVATE_KEY`, `SSH_KNOWN_HOSTS`). Host key is pinned (`StrictHostKeyChecking yes`).
- **Paths:** one `DEPLOY_PATH` per GitHub Environment (`production`, `staging`), each outside
  `public_html`:

  ```
  DEPLOY_PATH/
    releases/<sha>/      built release (last 5 kept)
    shared/.env          written once by hand
    shared/storage/      uploads, logs, backups
    current -> releases/<sha>
  ```

  The domain's document root is `DEPLOY_PATH/current/public`.
- **Copying files:** `tar -czf - . | ssh server "tar -xzf - -C DEPLOY_PATH/releases/<sha>"`.
  A new release directory every time, so there is no `--delete`-style sync and nothing outside
  `DEPLOY_PATH` can be removed. Old releases are pruned only inside `DEPLOY_PATH/releases`.
- **Go-live:** migrations run, then `current` is swapped atomically, so visitors never see a
  half-uploaded site.
- **Guard rails:** the deploy refuses a `DEPLOY_PATH` that is relative, the account home, inside
  `public_html`, or missing the word `nursery`.

## Fallback if SSH is not available

The Laravel app needs to run `php artisan migrate` and `optimize` after each upload. If Truehost
will not enable SSH on this account, the options in order of preference:

1. Ask Truehost support to enable SSH for the account (usual on their cPanel plans).
2. FTP upload (`lftp mirror`) plus a protected one-time deploy URL that runs the artisan steps.
   This works but is less safe; we would build it only if 1 is refused.

## Open items

- [x] SSH access and port confirmed (24 Nov)
- [ ] Kevin: authorise the deploy key in cPanel, run `php -v` over SSH to confirm the CLI PHP version
- [x] `{{STAGING_DOMAIN}}` = `staging.nursery.wisdomcapital.co.ke` (DNS created 24 Nov); create it as a domain in cPanel with docroot `{{STAGING_PATH}}/current/public` and run AutoSSL
- [ ] Kevin: create the two GitHub Environments and secrets (README → Deploy)
- [ ] Staging: block search engines (`APP_ENV=staging` → `noindex`), optionally cPanel Directory Privacy password
