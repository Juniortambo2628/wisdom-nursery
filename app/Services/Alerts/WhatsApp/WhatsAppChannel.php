<?php

namespace App\Services\Alerts\WhatsApp;

interface WhatsAppChannel
{
    /** Driver name stored in the notification log. */
    public function name(): string;

    /**
     * Sends an approved template message.
     *
     * @param  list<string>  $params  body parameters {{1}}, {{2}}…
     * @return string|null provider message id
     */
    public function sendTemplate(string $to, string $template, array $params): ?string;
}
