# Running the Wisdom Propagation Nursery website

A guide for the nursery team: orders, plants, prices, stock, quotes, photos and alerts.
Everything is done in the **dashboard**: **https://nursery.wisdomcapital.co.ke/admin**
(staging preview: `https://staging.nursery.wisdomcapital.co.ke/admin`). It works on a phone.

Help: WhatsApp or call OKJTechnologies on +254 705 883 227, or email kevin@okjtech.co.ke.
Website down or customers cannot pay: call or WhatsApp straight away (SLA 9: response within
4 working hours, fixed within 1 working day).

---

## 1. Logging in

- Open the dashboard address, enter your email and password.
- Forgot it? Ask OKJTechnologies to reset it. Each person has their own login; do not share.
- On a phone, the menu is behind the ☰ button at the top left.

## 2. Every day: orders

### How an order arrives

1. A customer adds plants to the cart and pays with M-Pesa on their phone.
2. When the payment goes through, the team's phones get an alert by SMS and WhatsApp (and an
   email copy), like this:

   ```
   NEW ORDER WN-1042 · PAID
   Jane Wanjiru, 0712 345 678
   1 × Swiss Cheese Plant
   KSh 1,600 via M-Pesa SKL4H7XXXX
   Collect at nursery.
   Open order: https://nursery.wisdomcapital.co.ke/admin/orders/42
   ```
3. The customer gets an SMS confirming their payment.

### Preparing an order

The **Dashboard** page shows **Sales today**, **To prepare**, new **quote requests** and
**Payments to check**, and below that the orders to prepare.

| The order is | Tap | The customer gets |
|---|---|---|
| Paid, plants being prepared | **Mark ready** | SMS: ready for collection (collection orders) |
| Ready, delivery leaving | **Mark dispatched** | SMS: on its way |
| Collected by the customer | **Mark collected** | – |
| Delivered | **Mark delivered** | – |

- The phone icon calls the customer.
- Tap an order number to see its plants, delivery address and payments.
- Stock goes down automatically when an order is paid.

### Order statuses

| Status | Meaning |
|---|---|
| Awaiting payment | Checkout started but not paid. No need to act; it stays here if they gave up |
| Paid | Money received: prepare it |
| Ready | Waiting for collection or dispatch |
| Dispatched | On the way |
| Collected / Delivered | Done |
| Cancelled | Cancelled from the order page (this does **not** refund M-Pesa; do refunds in the M-Pesa portal) |

### "Payments to check"

If a customer's phone did not show the M-Pesa prompt, they can pay to the till and type the
M-Pesa code on the website. Those orders show under **Payments to check**:

1. Open the order → **Payments**.
2. Find the code in your M-Pesa statement and check the amount.
3. Tap **Confirm payment** (the order becomes Paid, the customer and team get the usual
   messages) or **Reject** if the code is wrong.

## 3. Plants, prices and stock

Menu → **Plants**.

- **Change a price or stock count**: type straight into the *Price* or *In stock* box in the
  list and press Enter. Saved immediately.
- **Availability**: *Ready now*, *Coming soon* (set the ready date: customers can pre-order
  and see "Ready in X weeks") or *Out of stock*.
- **Visible**: switch off to hide a plant from the site without deleting it.
- **Popular**: switched-on plants show under "Popular this season" on the home page
  (choose about four).
- **Edit** (pencil) for everything else: name, description, care facts (light, water, pot
  size, grows to), photos, "buy as a set of N", "Bulk from" (shows *Add to quote* for big
  orders), and "Goes well with".
- **New plant**: top right. Give it a category, price, stock and at least one photo.
- When a plant comes back into stock, everyone who tapped **Notify me** gets one SMS.

Search and filters at the top of the list: by category, **Low stock (under 5)**, hidden plants.

### QR labels for the nursery beds

Tick the plants → **Print QR labels** → a PDF of labels (name, price, QR code). Print on A4,
cut, and put them on the beds: customers scan to open the plant's page and order.
Don't change a plant's web address ("slug") after printing its label.

### WhatsApp Business catalogue

Plants → **Export for WhatsApp catalogue** downloads a file of all plants with prices and
photos. Upload it in Meta Commerce Manager → Catalogue → Data sources. Export again whenever
prices change.

## 4. Quote requests (bulk orders)

Farms, schools and landscapers send a quote request instead of paying online. The team gets
the same kind of alert ("NEW QUOTE Q-207"). Menu → **Quote requests**:

- See who asked, which plants, how many, and where to deliver.
- Reply by phone (phone icon) or WhatsApp (chat icon, which opens WhatsApp with a greeting).
- Set the status: **New → Replied → Won / Lost**, so nothing is forgotten.

## 5. Messages

Menu → **Messages**: what people sent from the contact form. Unread ones are marked; the menu
shows how many.

## 6. The website's words, contacts and photos

Menu → **Settings → Site settings**, tabs:

- **Contact**: phone numbers (up to four, each with an optional label such as *Orders* or *Office*; tap **Add another number**, drag to reorder, the first is the main one), email, address, opening hours, Google Maps link, social media.
- **Ordering**: the M-Pesa till or paybill number shown to customers (and, for a paybill, its account number), collection note, delivery text.
- **Home page**: the story, the four numbers, the "How we grow" steps.
- **Photos**: every large photo on the site. Tap one to upload a replacement (photos from a
  phone are fine; keep them landscape for the wide ones). Change them whenever you like.
- **About**: about text, facilities text, the nursery units (one per line), and the four *What we grow* boxes.
- **Services**: the name, description and photo of each of the five services on the Our services page.

Press **Save** at the bottom.

Elsewhere:

- **Plants → Delivery areas**: where you deliver and the fee for each. Customers choose one
  at checkout.
- **Content → Gallery**: add, reorder (drag) or hide gallery photos; give each a caption and
  a group (Team, Plants, Nursery, Deliveries).
- **Content → Team**, **Testimonials**, **Partners**.
- **Content → Grower tips**: write a guide; set **Publish on** to put it on the site (empty =
  draft).

## 7. Who gets the order alerts

Menu → **Settings → Alert recipients**: each person's name, phone, email and how they want
alerts (SMS, WhatsApp, email). Switch off **Receives alerts** for someone on leave.
**Send test** sends them a test message.

**Settings → Alert log** lists every alert and SMS the site sent, with *sent*, *failed* or
*skipped*. If someone says they didn't get an alert, look here first.

## 8. Site health

Menu → **Settings → Site health** checks, every time you open it, that the website's machinery
works: the scheduled jobs (cron), the alert queue, recent failed alerts, who receives alerts,
M-Pesa, SMS, WhatsApp, email and backups. **Red** needs action now, **amber** is worth a look.
A number on the menu item means something needs attention. If something stays red, contact
OKJTechnologies.

## 9. Good habits

- Update stock when plants are sold at the nursery, so the website doesn't sell what's gone.
- Check **Payments to check** and **Quote requests** every morning.
- SMS costs about KSh 1 each, paid to Africa's Talking: keep the account topped up.
- Don't share dashboard logins or the M-Pesa details.

## 10. What OKJTechnologies looks after (SLA 9.2)

Fixing faults, up to 2 hours a month of small content updates, checking that the site,
checkout and alerts work, weekly backups (kept 4 weeks), security updates, and the monthly
visitor report, for 3 months from launch.
