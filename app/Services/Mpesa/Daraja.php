<?php

namespace App\Services\Mpesa;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Safaricom Daraja: OAuth token (cached), STK push and STK status query.
 * Sandbox or production is chosen by MPESA_ENV; credentials live in .env only.
 */
class Daraja
{
    public function __construct(private array $config) {}

    public static function fromConfig(): self
    {
        return new self(config('nursery.mpesa'));
    }

    public function isConfigured(): bool
    {
        return filled($this->config['consumer_key'])
            && filled($this->config['consumer_secret'])
            && filled($this->config['shortcode'])
            && filled($this->config['passkey'])
            && filled($this->config['callback_secret']);
    }

    /**
     * Sends the "enter your M-Pesa PIN" prompt to the customer's phone.
     *
     * @return array{MerchantRequestID:string, CheckoutRequestID:string, CustomerMessage?:string}
     */
    public function stkPush(string $phone, int $amount, string $reference, string $description): array
    {
        $timestamp = $this->timestamp();
        $response = $this->client()->post('/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $this->config['shortcode'],
            'Password' => $this->password($timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => $this->config['type'] === 'till' ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->config['party_b'] ?: $this->config['shortcode'],
            'PhoneNumber' => $phone,
            'CallBackURL' => route('mpesa.callback', $this->config['callback_secret']),
            'AccountReference' => mb_substr($reference, 0, 12),
            'TransactionDesc' => mb_substr($description, 0, 13),
        ]);

        $data = $response->json() ?? [];
        if (! $response->successful() || ($data['ResponseCode'] ?? null) !== '0') {
            throw new MpesaException($data['errorMessage'] ?? $data['ResponseDescription'] ?? 'M-Pesa did not accept the request (HTTP '.$response->status().')');
        }

        return $data;
    }

    /**
     * @return array{ResultCode?:string|int, ResultDesc?:string, errorCode?:string, errorMessage?:string}
     */
    public function query(string $checkoutRequestId): array
    {
        $timestamp = $this->timestamp();

        return $this->client()->post('/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $this->config['shortcode'],
            'Password' => $this->password($timestamp),
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ])->json() ?? [];
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->token())
            ->acceptJson()
            ->timeout(30);
    }

    private function token(): string
    {
        $key = 'mpesa.token.'.$this->config['env'].'.'.md5((string) $this->config['consumer_key']);

        return Cache::remember($key, now()->addMinutes(50), function () {
            $response = Http::baseUrl($this->baseUrl())
                ->withBasicAuth($this->config['consumer_key'], $this->config['consumer_secret'])
                ->timeout(30)
                ->get('/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            $token = $response->json('access_token');
            if (! $token) {
                throw new MpesaException('Could not get an M-Pesa access token (HTTP '.$response->status().')');
            }

            return $token;
        });
    }

    private function baseUrl(): string
    {
        return $this->config['base_urls'][$this->config['env'] === 'production' ? 'production' : 'sandbox'];
    }

    private function timestamp(): string
    {
        return now('Africa/Nairobi')->format('YmdHis');
    }

    private function password(string $timestamp): string
    {
        return base64_encode($this->config['shortcode'].$this->config['passkey'].$timestamp);
    }
}
