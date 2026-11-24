<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Jobs\SendAlert;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plant;
use Database\Seeders\DemoPlantSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->seed();
    $this->seed(DemoPlantSeeder::class);
    config()->set('nursery.mpesa', array_merge(config('nursery.mpesa'), [
        'consumer_key' => 'key', 'consumer_secret' => 'secret', 'shortcode' => '174379',
        'passkey' => 'pass', 'callback_secret' => 'test-secret', 'env' => 'sandbox',
    ]));
    Http::fake([
        '*/oauth/v1/generate*' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
        '*/mpesa/stkpush/v1/processrequest' => Http::response([
            'MerchantRequestID' => 'm-1', 'CheckoutRequestID' => 'ws_CO_1', 'ResponseCode' => '0', 'CustomerMessage' => 'Success',
        ]),
    ]);
    $this->swiss = Plant::where('slug', 'swiss-cheese-plant')->first(); // stock 25
});

/** Replaces the Safaricom fakes (the first matching fake wins, so beforeEach's would). */
function fakeDaraja(array $stk, int $status = 200, ?array $query = null): void
{
    Http::swap(new \Illuminate\Http\Client\Factory);
    Http::fake(array_filter([
        '*/oauth/v1/generate*' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
        '*/mpesa/stkpush/v1/processrequest' => Http::response($stk, $status),
        '*/mpesa/stkpushquery/v1/query' => $query ? Http::response($query) : null,
    ]));
}

function placeOrder($test, int $qty = 2): Order
{
    $test->post('/cart', ['plant_id' => $test->swiss->id, 'qty' => $qty]);
    $test->post('/checkout', ['name' => 'Jane Wanjiru', 'phone' => '0712345678', 'fulfilment' => 'collect', 'mpesa_phone' => '0712345678', '_ts' => time() - 10]);

    return Order::latest('id')->first();
}

function callback(string $checkoutId, int $code = 0, int $amount = 3200, string $receipt = 'SKL4H7ABCD'): array
{
    $cb = ['MerchantRequestID' => 'm-1', 'CheckoutRequestID' => $checkoutId, 'ResultCode' => $code, 'ResultDesc' => $code ? 'Request cancelled by user' : 'The service request is processed successfully.'];
    if ($code === 0) {
        $cb['CallbackMetadata'] = ['Item' => [
            ['Name' => 'Amount', 'Value' => $amount], ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
            ['Name' => 'TransactionDate', 'Value' => 20261124120000], ['Name' => 'PhoneNumber', 'Value' => 254712345678],
        ]];
    }

    return ['Body' => ['stkCallback' => $cb]];
}

it('sends an STK push at checkout with the right request', function () {
    $order = placeOrder($this);

    Http::assertSent(fn ($r) => str_ends_with($r->url(), '/mpesa/stkpush/v1/processrequest')
        && $r['Amount'] === 3200 && $r['PhoneNumber'] === '254712345678' && $r['PartyB'] === '174379'
        && $r['TransactionType'] === 'CustomerPayBillOnline' && $r['AccountReference'] === $order->number
        && str_ends_with($r['CallBackURL'], '/api/mpesa/callback/test-secret'));

    expect($order->latestPayment)->checkout_request_id->toBe('ws_CO_1')->status->toBe(PaymentStatus::Pending);
    $this->get(route('orders.show', $order))->assertSee('Check your phone');
});

it('uses Buy Goods for a till', function () {
    config()->set('nursery.mpesa.type', 'till');
    config()->set('nursery.mpesa.party_b', '5678901');
    placeOrder($this);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'processrequest') && $r['TransactionType'] === 'CustomerBuyGoodsOnline' && $r['PartyB'] === '5678901');
});

it('marks the order paid on a successful callback, reduces stock and alerts the team', function () {
    Queue::fake();
    $order = placeOrder($this, 2);

    $this->postJson('/api/mpesa/callback/test-secret', callback('ws_CO_1'))->assertOk()->assertJson(['ResultCode' => 0]);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->latestPayment->mpesa_receipt)->toBe('SKL4H7ABCD')
        ->and($order->latestPayment->raw_callback)->toHaveKey('Body')
        ->and($this->swiss->fresh()->stock_qty)->toBe(23);
    Queue::assertPushed(SendAlert::class, fn ($j) => $j->event === 'customer_paid' && $j->recipient === '254712345678');
    $this->getJson(route('orders.status', $order))->assertJson(['paid' => true, 'receipt' => 'SKL4H7ABCD']);
});

it('processes the same callback only once', function () {
    Event::fake([OrderPaid::class]);
    placeOrder($this, 2);

    $this->postJson('/api/mpesa/callback/test-secret', callback('ws_CO_1'));
    $this->postJson('/api/mpesa/callback/test-secret', callback('ws_CO_1'))->assertOk();

    Event::assertDispatchedTimes(OrderPaid::class, 1);
    expect($this->swiss->fresh()->stock_qty)->toBe(23);
});

it('records a failed or cancelled payment and lets the customer retry', function () {
    $order = placeOrder($this);

    $this->postJson('/api/mpesa/callback/test-secret', callback('ws_CO_1', 1032))->assertOk();

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->latestPayment->status)->toBe(PaymentStatus::Cancelled);
    $this->get(route('orders.show', $order))->assertSee('The payment didn')->assertSee('Send prompt again');

    fakeDaraja(['MerchantRequestID' => 'm-2', 'CheckoutRequestID' => 'ws_CO_2', 'ResponseCode' => '0']);
    $this->post(route('orders.retry', $order), ['phone' => '0712345678'])->assertRedirect();
    expect($order->payments()->count())->toBe(2)
        ->and($order->latestPayment()->first()->checkout_request_id)->toBe('ws_CO_2');
});

it('holds a payment for checking when the amount is short', function () {
    Event::fake([OrderPaid::class]);
    $order = placeOrder($this);

    $this->postJson('/api/mpesa/callback/test-secret', callback('ws_CO_1', 0, 100));

    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->latestPayment->status)->toBe(PaymentStatus::PendingVerification);
    Event::assertNotDispatched(OrderPaid::class);
});

it('rejects callbacks with the wrong secret', function () {
    $this->postJson('/api/mpesa/callback/wrong', callback('ws_CO_1'))->assertNotFound();
});

it('reconciles a payment whose callback never came', function () {
    fakeDaraja(['MerchantRequestID' => 'm-1', 'CheckoutRequestID' => 'ws_CO_1', 'ResponseCode' => '0'], 200, ['ResultCode' => '0', 'ResultDesc' => 'The service request is processed successfully.']);
    $order = placeOrder($this);
    Payment::query()->update(['created_at' => now()->subMinutes(3)]);

    $this->artisan('mpesa:reconcile')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    // a late callback still adds the receipt, without paying twice
    $this->postJson('/api/mpesa/callback/test-secret', callback('ws_CO_1'));
    expect($order->latestPayment->fresh()->mpesa_receipt)->toBe('SKL4H7ABCD');
});

it('keeps the order open when Safaricom refuses the push', function () {
    fakeDaraja(['errorMessage' => 'Invalid Access Token'], 400);
    $order = placeOrder($this);

    expect($order->latestPayment->status)->toBe(PaymentStatus::Failed)
        ->and($order->status)->toBe(OrderStatus::PendingPayment);
    $this->get(route('orders.show', $order))->assertSee('Send prompt again');
});
