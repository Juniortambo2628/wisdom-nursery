<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Plant;
use Database\Seeders\DemoPlantSeeder;

beforeEach(function () {
    $this->seed();
    $this->seed(DemoPlantSeeder::class);
    $this->swiss = Plant::where('slug', 'swiss-cheese-plant')->first();
    $this->palm = Plant::where('slug', 'golden-palm')->first();
});

function checkoutData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Wanjiru',
        'phone' => '0712 345 678',
        'fulfilment' => 'collect',
        'mpesa_phone' => '0712345678',
        '_ts' => time() - 10,
    ], $overrides);
}

it('adds plants to the cart and totals them', function () {
    $this->post('/cart', ['plant_id' => $this->swiss->id])->assertRedirect();
    $this->post('/cart', ['plant_id' => $this->palm->id, 'qty' => 2])->assertRedirect();
    $this->post('/cart', ['plant_id' => $this->swiss->id, 'buy_as' => 'set'])->assertRedirect();

    $this->get('/cart')
        ->assertSee('Swiss Cheese Plant (set of 5)')
        ->assertSee('KSh 11,000'); // 1,600 + 2 × 950 + 7,500
});

it('refuses out-of-stock plants', function () {
    $zebra = Plant::where('slug', 'zebra-plant')->first();
    $this->post('/cart', ['plant_id' => $zebra->id])->assertSessionHas('error');
    expect(session('cart', []))->toBeEmpty();
});

it('creates an order awaiting payment from the cart', function () {
    $this->post('/cart', ['plant_id' => $this->swiss->id]);
    $this->post('/cart', ['plant_id' => $this->palm->id, 'qty' => 2]);

    $response = $this->post('/checkout', checkoutData());

    $order = Order::with('items')->sole();
    $response->assertRedirect(route('orders.show', $order));
    expect($order->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->number)->toBe('WN-'.($order->id + 1000))
        ->and($order->customer_phone)->toBe('254712345678')
        ->and($order->total)->toBe(3500)
        ->and($order->items)->toHaveCount(2)
        ->and(session('cart', []))->toBeEmpty();

    $this->get(route('orders.show', $order))->assertSee('Check your phone')->assertSee('0712 345 678');
    $this->getJson(route('orders.status', $order))->assertJson(['paid' => false, 'status' => 'pending_payment']);
});

it('charges the delivery fee for the chosen area', function () {
    $area = \App\Models\DeliveryArea::create(['name' => 'Kitui town', 'fee' => 300]);
    $this->post('/cart', ['plant_id' => $this->swiss->id]);

    $this->post('/checkout', checkoutData(['fulfilment' => 'delivery', 'delivery_area_id' => $area->id, 'delivery_address' => 'Near the market']));

    expect(Order::sole())->delivery_fee->toBe(300)->total->toBe(1900)->delivery_area_name->toBe('Kitui town');
});

it('validates checkout details', function () {
    $this->post('/cart', ['plant_id' => $this->swiss->id]);

    $this->post('/checkout', checkoutData(['phone' => '12345', 'fulfilment' => 'delivery']))
        ->assertSessionHasErrors(['phone', 'delivery_area_id', 'delivery_address']);
    expect(Order::count())->toBe(0);
});

it('rejects bots via the honeypot', function () {
    $this->post('/cart', ['plant_id' => $this->swiss->id]);
    $this->post('/checkout', checkoutData(['website' => 'spam']))->assertSessionHasErrors('website');
    expect(Order::count())->toBe(0);
});

it('takes a manual M-Pesa code for the team to verify', function () {
    $order = Order::factory()->create(['total' => 1600]);

    $this->post(route('orders.mpesa-code', $order), ['mpesa_code' => 'skl4h7abcd'])->assertSessionHas('status');

    $payment = $order->payments()->sole();
    expect($payment->status)->toBe(PaymentStatus::PendingVerification)
        ->and($payment->mpesa_receipt)->toBe('SKL4H7ABCD')
        ->and($payment->method)->toBe('manual_code');
    $this->post(route('orders.mpesa-code', $order), ['mpesa_code' => 'short'])->assertSessionHasErrors('mpesa_code');
});
