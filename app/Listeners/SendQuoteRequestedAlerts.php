<?php

namespace App\Listeners;

use App\Events\QuoteRequested;
use App\Services\Alerts\AlertDispatcher;
use App\Services\Alerts\AlertMessages;

class SendQuoteRequestedAlerts
{
    public function __construct(private AlertDispatcher $alerts) {}

    public function handle(QuoteRequested $event): void
    {
        $quote = $event->quote->loadMissing('items');

        $this->alerts->toTeam('quote_requested', $quote, AlertMessages::quoteRequested($quote),
            "New quote request Q-{$quote->id} from {$quote->name}",
            config('nursery.whatsapp_alerts.templates.quote_requested'), AlertMessages::quoteRequestedParams($quote));
    }
}
