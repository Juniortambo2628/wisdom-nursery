# Wisdom Nursery website: build plan

Reference: proposal OKJ-WN-2026-01 Rev 3 (agreed 20 Nov 2026), SLA OKJ-WN-2026-02, Design Concepts Rev 1 (Concept B, green palette).
The proposal is the source of truth for scope. Anything not listed here needs Kevin's OK first (SLA 7: change requests).

Legend: `[ ]` to do · `[x]` done · `[~]` blocked (reason given)

## Blockers / inputs needed

| Needed | From | Blocks |
|---|---|---|
| ~~Wisdom Capital repo~~ (never on GitHub; deploy set up fresh) | — | — |
| ~~SSH access + port~~ (enabled 24 Nov); deploy key to authorise; CLI PHP version | Kevin | First deploy |
| ~~Design Concepts (Rev 1)~~ received 24 Nov | — | — |
| `docs/data/wisdom-capital-products.csv` + `docs/data/images/` | Kevin | Plant importer (33 plants) |
| `{{NURSERY_PATH}}`, `{{STAGING_PATH}}`, `{{DOMAIN}}`, `{{STAGING_DOMAIN}}` | Kevin | deploy.yml |
| Logo (original file), photos, hours, delivery areas/fees | Client (proposal 06) | Content swap-in |
| Till/paybill + Daraja access | Client (proposal 06, SLA 5.2) | Live M-Pesa |
| Alert phone numbers / emails | Client (proposal 06) | Alert recipients seed |
| Africa's Talking account + sender ID; WhatsApp Cloud API approval | Kevin / client | Live SMS / WhatsApp alerts |

Observed (from Kevin's cPanel screenshot, 24 Nov): server `lim107.truehost.cloud` (Truehost shared cPanel), DNS records
`nursery.wisdomcapital.co.ke` (A → shared IP) and `www.nursery.wisdomcapital.co.ke` (CNAME) already created.
Likely `{{DOMAIN}} = nursery.wisdomcapital.co.ke`; to be confirmed.

## Notes

- **Week 3 (24 Nov):** back-in-stock SMS for "Notify me", WhatsApp catalogue export, 375px QA fixes, `docs/launch-checklist.md`, `docs/handover.md`. Waiting on client content and live keys for the live M-Pesa test and launch.

- **Renamed to Wisdom Propagation Nursery** (Aldo, 24 Nov). Domain unchanged.
- **Real photos** from the old nursery page are in place (plants, hero, story, steps, facilities, gallery); env-4 (children) deliberately excluded. Staff can swap any of them in Site settings → Photos.

- **Pages built 24 Nov** from the concepts deck (Concept B). Decisions agreed with Kevin: shared Blade
  components in `resources/views/components`; coming-soon plants with stock can be pre-ordered (as the
  deck shows). Local preview data: `php artisan db:seed --class=DemoPlantSeeder` (never run by deploy).

- **Laravel 12 instead of 11** (24 Nov): Laravel 11 no longer receives security fixes; `composer audit`
  flagged a high-severity CRLF injection in the email validation rule, fixed only in 12.60+. Same
  skeleton, Filament 3 supports it. Laravel 12 security support runs to Feb 2027; plan the move to 13
  (needs PHP 8.3 and Filament 4) inside the support period.
- **Design tokens** are in `tailwind.config.js`; Fraunces + Inter are self-hosted via `@fontsource-variable`.

## Step 0: deployment (before app code)

- [x] `docs/deploy-notes.md` (no Wisdom Capital workflow exists; fresh SSH + tar design for the same server)
- [x] SSH available (Truehost enabled it 24 Nov); deploy key generated for Kevin to authorise
- [x] `.github/workflows/deploy.yml`: `main` → production, `staging` → staging, tests gate the deploy
- [x] Build in Actions (`composer install --no-dev -o`, `npm ci && npm run build`), upload release, run migrate/storage:link/optimize
- [x] Seeding guarded per seeder (`seeded:<Class>` in settings): runs once, never overwrites edits, late seeders still run
- [x] Guard rails: explicit target paths, no deletes outside nursery dirs, never touch Wisdom Capital files/DB
- [x] README "Deploy" section (secrets, paths, cron)

## Scope: pages (proposal 05)

- [x] Home (hero, stats, story, 5 categories, popular this season, how we grow, bulk quote band, gallery preview, testimonial + partners, visit us + map + contact form)
- [x] Our plants: availability list and shop (filters, search, availability states, sticky cart + quote list)
- [x] Individual plant pages (gallery, buy-as options, stepper, WhatsApp order, care facts, goes well with)
- [x] Cart and M-Pesa checkout (Cart → Details → Pay → Done; collect/delivery; STK push, waiting state, retry, till/code fallback)
- [x] About, team and "How we grow"
- [x] Gallery (filters: Team, Plants, Nursery, Deliveries)
- [x] Grower tips (posts)
- [x] Contact and visit (address, hours, map, form, WhatsApp, phone)
- [x] Designed for phones and computers: automated 375px QA of every page, the buying flow, forms and dashboard (no overflow, 40px tap targets, labels, alt text); real-phone check is in the launch checklist

## Scope: "also included" (proposal 05)

- [x] Order dashboard for the team, works on a phone (Filament: Today stats, orders to prepare, status chips, one-tap Mark ready/dispatched/collected/delivered, Call)
- [x] Order alerts by SMS, WhatsApp and email (OrderPaid / QuoteRequested → queued SendAlert jobs → `notification_logs`); WhatsApp on `log` driver until Meta approves the templates
- [x] WhatsApp ordering (floating button, plant-page "Order on WhatsApp", cart handoff `wa.me` link)
- [x] Quote request for bulk orders (quote list + form + team alerts + dashboard resource with status, call, WhatsApp reply)
- [x] Map and directions (embed + directions link, from settings `map_embed_url` / `map_link`)
- [x] Basic search engine setup (titles/meta, OG, sitemap.xml, robots.txt, Product + LocalBusiness schema)
- [x] Training guide → `docs/handover.md` (session itself after launch)

## Scope: investment line items (proposal 08)

- [x] Website design and development, The Grower's Story, green palette
- [x] Online shop: cart, M-Pesa checkout (Daraja STK + status query + idempotent callback + manual code), stock levels, customer SMS confirmations — sandbox tested with fakes; live test week 3
- [x] Order alerts by SMS, WhatsApp, email, with order dashboard

## Scope: included at no extra cost (proposal 08)

- [x] All 33 plants moved across with photos, descriptions and prices (from the Wisdom Capital database dump and the old site's photos, 24 Nov), plus 4 grafted fruit varieties from the client's latest catalogue
- [x] Brand story, team and "How we grow" pages (placeholders until copy arrives)
- [x] Quote request for bulk and institutional orders
- [~] Six plant care and tree planting guides (seeded as drafts; waiting on Kevin's copy)
- [~] Google Business Profile: off-site, steps in launch checklist §9; site has LocalBusiness schema
- [x] WhatsApp Business catalogue: Plants → Export for WhatsApp catalogue (Meta CSV); upload steps in launch checklist §9
- [x] QR code labels for plants (Plants → select → Print QR labels → A4 PDF)
- [x] Link from wisdomcapital.co.ke → redirect plan in `docs/launch-checklist.md` §8 (`/wisdom-nursery.html` → new site, 301)
- [~] Search engine setup done (meta, OG, sitemap, robots, schema); GA4 ID + monthly report start at launch
- [~] Staff training guide (`docs/handover.md`) and weekly backups (4 weeks) done; training and 3-month support start at launch

## Platform

- [x] Laravel **12** (11 is out of security support; see note), PHP 8.2+ platform pin, MySQL; Blade + Vite + Tailwind + Alpine
- [x] Filament 3 dashboard in the palette (primary = leaf, self-hosted Inter); menu closed by default on phones
- [x] Queue `database` driver, run from scheduler (`queue:work --stop-when-empty --max-time=50`)
- [x] Scheduler: queue, daily DB backup (retain 4 weeks of weeklies), log pruning
- [x] Security: rate limits (forms, checkout, retry, callback), honeypot, validation, secret callback path, secrets in `.env` only, complete `.env.example`
- [x] Data model (plus plant_pairings, stock_alerts, contact_messages): categories, plants, plant_images, orders, order_items, payments, quote_requests, quote_items, alert_recipients, posts, team_members, gallery_images, testimonials, partners, delivery_areas, settings, notification_logs

## Tests (Pest)

- [x] Add to cart · checkout creates order · STK callback success / failure / duplicate · amount mismatch · reconcile · stock decrement
- [x] Quote request · alerts dispatched (faked) · SMS / WhatsApp / email drivers · status change sends customer SMS · dashboard actions

## Milestones

- [x] Week 1: pipeline + staging live; design system; public pages; importer
- [~] Week 2: cart, checkout, M-Pesa sandbox, quotes, alerts, Filament → built and tested 24 Nov; needs Daraja sandbox keys and Africa's Talking keys on staging for an end-to-end run
- [~] Week 3: tests (65), 375px QA, handover and launch checklist done; live M-Pesa test, content swap and production launch wait on client content and live keys
