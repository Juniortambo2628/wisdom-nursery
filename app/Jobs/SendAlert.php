<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\Alerts\AfricasTalkingSms;
use App\Services\Alerts\WhatsApp\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * One message to one recipient on one channel. Every attempt is written to
 * notification_logs so a missed alert can be traced in the dashboard.
 */
class SendAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(
        public string $channel,          // sms | whatsapp | email
        public string $recipient,        // phone (2547…) or email
        public string $event,            // order_paid | quote_requested | customer_paid | order_ready | order_dispatched
        public string $body,
        public ?Model $subject = null,
        public ?string $template = null, // WhatsApp template name
        public array $params = [],       // WhatsApp template parameters
        public ?string $emailSubject = null,
    ) {
        $this->afterCommit();
    }

    public function handle(AfricasTalkingSms $sms, WhatsAppChannel $whatsapp): void
    {
        $log = fn (string $status, ?string $id = null, ?string $error = null) => NotificationLog::create([
            'channel' => $this->channel,
            'recipient' => $this->recipient,
            'event' => $this->event,
            'subject_type' => $this->subject?->getMorphClass(),
            'subject_id' => $this->subject?->getKey(),
            'body' => $this->body,
            'status' => $status,
            'provider_message_id' => $id,
            'error' => $error ? mb_substr($error, 0, 1000) : null,
        ]);

        if ($this->channel === 'sms' && ! $sms->isConfigured()) {
            $log('skipped', null, 'SMS is not configured (AT_USERNAME / AT_API_KEY).');

            return; // a missing setting is not worth retrying
        }

        try {
            match ($this->channel) {
                'sms' => $log('sent', $sms->send($this->recipient, $this->body)),
                'whatsapp' => (function () use ($whatsapp, $log) {
                    $id = $whatsapp->sendTemplate($this->recipient, (string) $this->template, $this->params);
                    $log($whatsapp->name() === 'log' ? 'logged' : 'sent', $id);
                })(),
                'email' => (function () use ($log) {
                    Mail::raw($this->body, fn ($m) => $m->to($this->recipient)->subject($this->emailSubject ?? 'Wisdom Propagation Nursery alert'));
                    $log('sent');
                })(),
            };
        } catch (Throwable $e) {
            $log('failed', null, $e->getMessage());
            throw $e; // retried, then left in failed_jobs
        }
    }
}
