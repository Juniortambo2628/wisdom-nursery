<?php

namespace App\Services\Alerts;

use App\Jobs\SendAlert;
use App\Models\AlertRecipient;
use Illuminate\Database\Eloquent\Model;

/** Fans one team alert out to every active recipient on each channel they chose. */
class AlertDispatcher
{
    public function toTeam(string $event, Model $subject, string $text, string $emailSubject, string $template, array $params): int
    {
        $count = 0;
        foreach (AlertRecipient::active()->get() as $r) {
            if ($r->wants('sms') && $r->phone) {
                SendAlert::dispatch('sms', $r->phone, $event, $text, $subject);
                $count++;
            }
            if ($r->wants('whatsapp') && $r->phone) {
                SendAlert::dispatch('whatsapp', $r->phone, $event, $text, $subject, $template, $params);
                $count++;
            }
            if ($r->wants('email') && $r->email) {
                SendAlert::dispatch('email', $r->email, $event, $text, $subject, emailSubject: $emailSubject);
                $count++;
            }
        }

        return $count;
    }

    public function smsToCustomer(string $phone, string $event, Model $subject, string $text): void
    {
        SendAlert::dispatch('sms', $phone, $event, $text, $subject);
    }
}
