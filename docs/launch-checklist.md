# Launch checklist: nursery.wisdomcapital.co.ke

Production goes live from the `main` branch, on the same server and with the same pipeline as
staging. Work top to bottom; each block depends on the one before. Values like `<user>` are
the same as in [`deploy-guide.md`](deploy-guide.md).

Acceptance (SLA 6.2–6.3): the client has 3 working days on the final staging preview to report
anything that does not work as described; launch follows written acceptance (WhatsApp or email
is enough) or the client's request to go live.

---

## 1. Content the client must confirm (proposal 06)

Everything below shows as a [bracketed] placeholder until filled in Dashboard → Settings →
Site settings (or Content), on **staging first** so the client can check it there.

- [ ] Address, opening hours, phone, email, Google Maps embed and directions link
- [ ] Founding year, the four home-page stats, "Our story" heading and text, About text
- [ ] How we grow: the four step descriptions; facilities text and items
- [ ] Team: names, roles, portraits (Content → Team)
- [ ] Delivery areas and fees (Plants → Delivery areas); delivery and care text
- [ ] Stock counts for every plant, especially the four grafted fruit varieties (currently 0)
- [ ] Remaining catalogue items: the rest of the grafted fruit list, orange Pixies, vegetable seedlings, with prices
- [ ] Real customer testimonials (the old database's ones were test data and are not used)
- [ ] The original logo file (the header shows a text placeholder)
- [ ] Six grower guides: final copy (Content → Grower tips; they are drafts until "Publish on" is set)
- [ ] M-Pesa till or paybill number (Site settings → Ordering) and the alert recipients' phones and emails (Settings → Alert recipients)

## 2. Accounts and keys (client's name, SLA 5.4)

- [ ] **Safaricom Daraja** production app approved for the till/paybill; consumer key, consumer secret, passkey
- [ ] **Africa's Talking** live account with credit; sender ID approved (e.g. `WISDOMNRSY`)
- [ ] **WhatsApp**: the two templates in [`whatsapp-templates.md`](whatsapp-templates.md) approved, system-user token and phone number ID (or launch on the `log` driver: SMS and email still go out)
- [ ] **Email**: a cPanel mailbox for sending (e.g. `orders@wisdomcapital.co.ke`); Email Deliverability shows SPF and DKIM valid
- [ ] **Google Analytics 4** property for the nursery; measurement ID `G-…`

## 3. Server: production domain (cPanel)

- [ ] Zone Editor: delete the hand-made `nursery` A and `www.nursery` CNAME records (cPanel re-creates them, as with staging)
- [ ] Domains → Create: `nursery.wisdomcapital.co.ke`, untick "Share document root", document root `nursery.wisdomcapital.co.ke/current/public`
- [ ] Select PHP Version: **8.4** for the domain, same extensions as staging
- [ ] SSL/TLS Status → Run AutoSSL; `nursery.` and `www.nursery.` both valid
- [ ] MySQL: new database `<user>_nursery` and user `<user>_nurseryprd` (separate from staging and from Wisdom Capital), ALL PRIVILEGES
- [ ] `~/nursery.wisdomcapital.co.ke/shared/.env` from the staging one, changing:
  `APP_ENV=production`, new `APP_KEY`, `APP_URL=https://nursery.wisdomcapital.co.ke`, the production database, `CACHE_PREFIX=wn_`,
  `MPESA_ENV=production` + live keys + `MPESA_TYPE`/`MPESA_SHORTCODE`/`MPESA_PARTY_B`, a **new** `MPESA_CALLBACK_SECRET`,
  `AT_ENV=production` + live username/key/sender ID, real `MAIL_*`, `NURSERY_WHATSAPP`, `GA4_MEASUREMENT_ID`, `BACKUP_NOTIFY_EMAIL`. Then `chmod 600 .env`
- [ ] Cron Jobs: a second entry, every minute:
  `cd /home/<user>/nursery.wisdomcapital.co.ke/current && php artisan schedule:run >> /dev/null 2>&1`

## 4. GitHub

- [ ] Settings → Environments → **production**: variable `DEPLOY_PATH=/home/<user>/nursery.wisdomcapital.co.ke`; tick **Required reviewers** (you) so launches wait for approval
- [ ] Create the `main` branch from the accepted `staging` commit (or ask Claude to push it). Approve the waiting deploy in Actions
- [ ] Watch "Test and deploy" go green

## 5. First production run

- [ ] `cd ~/nursery.wisdomcapital.co.ke/current && php artisan make:filament-user` for each staff member
- [ ] Copy the content confirmed on staging: Site settings, delivery areas, alert recipients, team, testimonials, stock counts
  (production starts from the same seeded catalogue and photos; only staff edits need re-entering)
- [ ] Settings → Alert recipients → **Send test** for each person; check Settings → Alert log shows `sent`
- [ ] Mail test: `php artisan tinker --execute="Mail::raw('Production mail test', fn (\$m) => \$m->to('you@example.com')->subject('Test'));"`

## 6. Live M-Pesa test (proposal 07, week 3)

- [ ] Temporarily set one plant to KSh 10 (or add a "Test plant", hidden after)
- [ ] Buy it on a phone with collection: STK prompt arrives → enter PIN → page switches to **Payment received** with the receipt
- [ ] Check: order is **Paid** in the dashboard, stock went down by one, team got SMS/WhatsApp/email, customer got the confirmation SMS
- [ ] Mark it ready → customer gets the "ready for collection" SMS
- [ ] Cancel a second prompt on the phone → page offers **Send prompt again**
- [ ] Pay a third order by till and enter the code → it shows under **Payments to check**; confirm it
- [ ] Reverse the test payments from the M-Pesa portal; restore the plant's price; delete or cancel the test orders

## 7. Go-live checks

- [ ] `https://nursery.wisdomcapital.co.ke/robots.txt` **allows** crawling and lists the sitemap (it blocks everything on staging)
- [ ] Page source has the GA4 tag (production only); no `noindex`
- [ ] `/sitemap.xml` lists the plants; Google Search Console: add the domain property, submit the sitemap
- [ ] Every page at 375px on a real Android phone and an iPhone: home, plants, a plant, cart, checkout, contact
- [ ] Dashboard → Settings → **Site health** is all green (cron, queue, alerts, M-Pesa live, SMS live, email, backups)
- [ ] Backups: next day, `ls ~/nursery.wisdomcapital.co.ke/shared/storage/app/private/*/` shows a database backup

## 8. Link from wisdomcapital.co.ke (proposal 08, "included")

The old site lists the nursery on one page, `https://wisdomcapital.co.ke/wisdom-nursery.html`
(plants load inline from its own API; there are no separate plant pages to map). **Do not edit
the Wisdom Capital site's code**; ask its maintainer, or with the client's go-ahead add to its
`public_html/.htaccess`:

```apache
# Wisdom Propagation Nursery moved to its own site
RewriteEngine On
RewriteRule ^wisdom-nursery\.html$ https://nursery.wisdomcapital.co.ke/ [R=301,L]
```

- [ ] 301 redirect live: `curl -I https://wisdomcapital.co.ke/wisdom-nursery.html` shows `301` and the new address
- [ ] The Wisdom Capital menu item and home-page nursery card point to `https://nursery.wisdomcapital.co.ke/`
- [ ] Google Search Console for wisdomcapital.co.ke: request re-indexing of the nursery page

## 9. Google Business Profile and WhatsApp catalogue (proposal 08, "included")

- [ ] Google Business Profile "Wisdom Propagation Nursery": category *Plant nursery*, address and pin, hours, phone,
  website `https://nursery.wisdomcapital.co.ke`, photos from the site; verify (postcard, phone or video)
- [ ] WhatsApp Business catalogue: Dashboard → Plants → **Export for WhatsApp catalogue** → upload the CSV in
  Meta Commerce Manager → Catalogue → Data sources; link the catalogue to the WhatsApp Business number.
  Re-export after price changes

## 10. After launch

- [ ] Issue the balance invoice (KES 37,500, SLA 8)
- [ ] Training session with the nursery team using [`handover.md`](handover.md)
- [ ] Support period (3 months) starts on launch day; first monthly visitor report (GA4) at month end
- [ ] Hand over logins (SLA 10.2) once paid in full: cPanel, GitHub repository access, Daraja, Africa's Talking, Meta, GA4

Rollback: `ln -sfn ~/nursery.wisdomcapital.co.ke/releases/<previous-sha> ~/nursery.wisdomcapital.co.ke/current`
(the last five releases are kept; database changes are not rolled back automatically).
