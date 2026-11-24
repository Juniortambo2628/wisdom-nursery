# WhatsApp alert templates

Team alerts on WhatsApp are business-initiated messages, so Meta only delivers them
through **approved templates**. Until both are approved, `WHATSAPP_DRIVER=log` keeps the
site running: WhatsApp alerts are written to the alert log, and SMS and email still go out.

Create these in WhatsApp Manager → Message templates, category **Utility**, language
**English** (`en`). The names must match `.env` (`WHATSAPP_TEMPLATE_ORDER`,
`WHATSAPP_TEMPLATE_QUOTE`).

## `new_order_alert`

```
🌱 New order {{1}} · PAID
{{2}} via M-Pesa {{3}}
Customer: {{4}}
Items: {{5}}
{{6}}
Open order: {{7}}
```

Sample values for review: `WN-1042`, `KSh 5,100`, `SKL4H7XXXX`, `Jane Wanjiru 0712 345 678`,
`1 × Swiss Cheese Plant, 2 × Golden Palm`, `Collect at nursery.`,
`https://nursery.wisdomcapital.co.ke/admin/orders/42`

## `new_quote_alert`

```
📋 New quote request {{1}}
From: {{2}}
Plants: {{3}}
Deliver to: {{4}}
Open quote: {{5}}
```

Sample values: `Q-207`, `St. Mary's School 0722 000 000`, `2,000 × Grevillea Robusta`, `Kitui`,
`https://nursery.wisdomcapital.co.ke/admin/quote-requests/207/edit`

## Switching on

1. Meta Business: a WhatsApp Business Account with the nursery's number, and a permanent
   system-user token with `whatsapp_business_messaging`.
2. In `shared/.env`: `WHATSAPP_DRIVER=cloud`, `WHATSAPP_CLOUD_TOKEN=…`,
   `WHATSAPP_CLOUD_PHONE_NUMBER_ID=…`.
3. Dashboard → Settings → Alert recipients → **Send test**, then check Settings → Alert log.

Recipients must be reachable on WhatsApp; template messages do not need them to have
messaged the business first.
