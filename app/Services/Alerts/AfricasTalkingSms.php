<?php

namespace App\Services\Alerts;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** SMS through Africa's Talking (sandbox first; AT_ENV=production to go live). */
class AfricasTalkingSms
{
    public function __construct(private array $config) {}

    public static function fromConfig(): self
    {
        return new self(config('nursery.sms'));
    }

    public function isConfigured(): bool
    {
        return filled($this->config['api_key']) && filled($this->config['username']);
    }

    /** @return string|null the provider's message id */
    public function send(string $phone, string $message): ?string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('SMS is not configured (AT_USERNAME / AT_API_KEY).');
        }

        $base = $this->config['base_urls'][$this->config['env'] === 'production' ? 'production' : 'sandbox'];
        $response = Http::asForm()->acceptJson()->timeout(20)
            ->withHeaders(['apiKey' => $this->config['api_key']])
            ->post($base.'/version1/messaging', array_filter([
                'username' => $this->config['username'],
                'to' => '+'.ltrim($phone, '+'),
                'message' => $message,
                'from' => $this->config['sender_id'] ?: null,
            ]));

        $recipient = $response->json('SMSMessageData.Recipients.0');
        if (! $response->successful() || ! in_array($recipient['status'] ?? null, ['Success', 'Sent', 'Queued'], true)) {
            throw new RuntimeException('SMS not sent: '.($recipient['status'] ?? $response->json('SMSMessageData.Message') ?? 'HTTP '.$response->status()));
        }

        return $recipient['messageId'] ?? null;
    }
}
