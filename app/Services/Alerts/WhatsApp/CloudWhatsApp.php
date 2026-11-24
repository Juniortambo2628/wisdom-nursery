<?php

namespace App\Services\Alerts\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/** WhatsApp Cloud API (Meta). Business-initiated alerts must use an approved template. */
class CloudWhatsApp implements WhatsAppChannel
{
    public function __construct(private array $config) {}

    public function name(): string
    {
        return 'cloud';
    }

    public function sendTemplate(string $to, string $template, array $params): ?string
    {
        if (blank($this->config['token']) || blank($this->config['phone_number_id'])) {
            throw new RuntimeException('WhatsApp Cloud API is not configured.');
        }

        $response = Http::withToken($this->config['token'])->acceptJson()->timeout(20)
            ->post("https://graph.facebook.com/{$this->config['api_version']}/{$this->config['phone_number_id']}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => ltrim($to, '+'),
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => $this->config['language']],
                    'components' => [[
                        'type' => 'body',
                        // Template parameters may not contain new lines, tabs or long runs of spaces.
                        'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => Str::limit(trim(preg_replace('/\s+/', ' ', (string) $p)), 1000)], $params),
                    ]],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('WhatsApp not sent: '.($response->json('error.message') ?? 'HTTP '.$response->status()));
        }

        return $response->json('messages.0.id');
    }
}
