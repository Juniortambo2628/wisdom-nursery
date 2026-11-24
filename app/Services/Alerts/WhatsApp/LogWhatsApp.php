<?php

namespace App\Services\Alerts\WhatsApp;

use Illuminate\Support\Facades\Log;

/** Used until Meta approves the template: the alert is only written to the log. */
class LogWhatsApp implements WhatsAppChannel
{
    public function name(): string
    {
        return 'log';
    }

    public function sendTemplate(string $to, string $template, array $params): ?string
    {
        Log::info('WhatsApp alert (log driver)', compact('to', 'template', 'params'));

        return null;
    }
}
