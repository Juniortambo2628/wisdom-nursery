# Wisdom Nursery website: build plan

Reference: proposal OKJ-WN-2026-01 Rev 3 (agreed 20 Nov 2026), SLA OKJ-WN-2026-02, Design Concepts Rev 1 (Concept B, green palette).
The proposal is the source of truth for scope. Anything not listed here needs Kevin's OK first (SLA 7: change requests).

Legend: `[ ]` to do · `[x]` done · `[~]` blocked (reason given)

## Blockers / inputs needed

| Needed | From | Blocks |
|---|---|---|
| Wisdom Capital repo (deploy workflow to copy) | Kevin | Step 0, staging link |
| `docs/Wisdom Nursery - Design Concepts (Rev 1).pdf` | Kevin | All page layouts |
| `docs/data/wisdom-capital-products.csv` + `docs/data/images/` | Kevin | Plant importer (33 plants) |
| `{{NURSERY_PATH}}`, `{{STAGING_PATH}}`, `{{DOMAIN}}`, `{{STAGING_DOMAIN}}` | Kevin | deploy.yml |
| Logo (original file), photos, hours, delivery areas/fees | Client (proposal 06) | Content swap-in |
| Till/paybill + Daraja access | Client (proposal 06, SLA 5.2) | Live M-Pesa |
| Alert phone numbers / emails | Client (proposal 06) | Alert recipients seed |
| Africa's Talking account + sender ID; WhatsApp Cloud API approval | Kevin / client | Live SMS / WhatsApp alerts |

Observed (from Kevin's cPanel screenshot, 24 Nov): server `lim107.truehost.cloud` (Truehost shared cPanel), DNS records
`nursery.wisdomcapital.co.ke` (A → shared IP) and `www.nursery.wisdomcapital.co.ke` (CNAME) already created.
Likely `{{DOMAIN}} = nursery.wisdomcapital.co.ke`; to be confirmed.

## Step 0: deployment (before app code)

- [~] Read Wisdom Capital `.github/workflows/` + deploy scripts → `docs/deploy-notes.md` (repo not reachable yet)
- [ ] Confirm SSH is available (needed for `php artisan migrate/optimize`); if not, propose fix before building
- [ ] `.github/workflows/deploy.yml`: same connection + secret names; `main` → production, `staging` → staging
- [ ] Build in Actions (`composer install --no-dev -o`, `npm ci && npm run build`), upload release, run migrate/storage:link/optimize
- [ ] First-deploy-only seeding guarded by marker
- [ ] Guard rails: explicit target paths, no deletes outside nursery dirs, never touch Wisdom Capital files/DB
- [ ] README "Deploy" section (secrets, paths, cron)

## Scope: pages (proposal 05)

- [ ] Home (hero, stats, story, 4 categories, popular this season, how we grow, bulk quote band, gallery preview, testimonial + partners, visit us + map + contact form)
- [ ] Our plants: availability list and shop (filters, search, availability states, sticky cart + quote list)
- [ ] Individual plant pages (gallery, buy-as options, stepper, WhatsApp order, care facts, goes well with)
- [ ] Cart and M-Pesa checkout (Cart → Details → Pay → Done; collect/delivery; STK waiting state; till fallback)
- [ ] About, team and "How we grow"
- [ ] Gallery (filters: Team, Plants, Nursery, Deliveries)
- [ ] Grower tips (posts)
- [ ] Contact and visit (address, hours, map, form, WhatsApp, phone)
- [ ] Designed for phones and computers (QA at 375px)

## Scope: "also included" (proposal 05)

- [ ] Order dashboard for the team, works on a phone (Filament: today view, status chips, one-tap actions)
- [ ] Order alerts by SMS, WhatsApp and email (events → queued notifications → `notification_logs`)
- [ ] WhatsApp ordering (floating button, plant-page "Order on WhatsApp", cart handoff `wa.me` link)
- [ ] Quote request for bulk orders (quote list + form + alerts + dashboard resource)
- [ ] Map and directions (embed + directions link)
- [ ] Basic search engine setup (titles/meta, OG, sitemap.xml, robots.txt, Product + LocalBusiness schema)
- [ ] Short training session for the nursery team → `docs/handover.md` as the training guide

## Scope: investment line items (proposal 08)

- [ ] Website design and development, The Grower's Story, green palette (design tokens, Fraunces + Inter self-hosted)
- [ ] Online shop: cart, M-Pesa checkout (Daraja STK + status query + idempotent callback + manual code), stock levels, customer SMS confirmations
- [ ] Order alerts by SMS, WhatsApp, email, with order dashboard

## Scope: included at no extra cost (proposal 08)

- [~] All 33 plants moved across with photos, descriptions and prices (idempotent seeder; needs CSV + images)
- [ ] Brand story, team and "How we grow" pages (placeholders until copy arrives)
- [ ] Quote request for bulk and institutional orders
- [ ] Six plant care and tree planting guides (seeded as drafts; Kevin supplies copy)
- [ ] Google Business Profile — off-site task for Kevin; site supplies LocalBusiness schema + NAP consistency
- [ ] WhatsApp Business catalogue — off-site task for Kevin; site can export plant list (name, price, image URL) to help
- [ ] QR code labels for plants (Filament bulk action → PDF)
- [ ] Link from wisdomcapital.co.ke → `docs/launch-checklist.md` (redirect plan only; do not edit that site)
- [ ] Search engine setup + monthly visitor report for 3 months (GA4 via `.env`)
- [ ] Staff training and 3 months support → `docs/handover.md`; weekly backups kept 4 weeks (SLA 9.2) via scheduler

## Platform

- [ ] Laravel 11, PHP per server (target 8.2+), MySQL; Blade + Vite + Tailwind + Alpine
- [ ] Filament 3 admin in palette
- [ ] Queue `database` driver, run from scheduler (`queue:work --stop-when-empty --max-time=50`)
- [ ] Scheduler: queue, daily DB backup (retain 4 weeks of weeklies), log pruning
- [ ] Security: rate limits (forms, STK), honeypot, validation, secrets in `.env` only, complete `.env.example`
- [ ] Data model: categories, plants, plant_images, orders, order_items, payments, quote_requests, quote_items, alert_recipients, posts, team_members, gallery_images, testimonials, partners, delivery_areas, settings, notification_logs

## Tests (Pest)

- [ ] Add to cart · checkout creates order · STK callback success / failure / duplicate · stock decrement
- [ ] Quote request · alerts dispatched (faked) · status change sends customer SMS

## Milestones

- [ ] Week 1: pipeline + staging live; design system; public pages with seeded content; importer → report to Kevin
- [ ] Week 2: cart, checkout, M-Pesa sandbox, quotes, alerts, Filament → report to Kevin
- [ ] Week 3: tests, 375px QA, live M-Pesa test, content swap, production launch, `docs/handover.md` → report to Kevin
