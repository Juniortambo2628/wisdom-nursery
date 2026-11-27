<?php

use App\Models\Order;
use App\Models\Plant;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

it('reports health for uptime monitors', function () {
    $this->getJson('/health')->assertStatus(503)->assertJson(['status' => 'down', 'database' => 'ok', 'cron' => 'late']);

    Cache::forever('health:scheduler', now()->toIso8601String());
    $this->getJson('/health')->assertOk()->assertJson(['status' => 'ok']);
});

it('clears test orders but keeps the content', function () {
    $this->seed();
    Setting::put('phone', '+254 700 000 000');
    $order = Order::factory()->create();
    $order->payments()->create(['method' => 'stk', 'amount' => 100, 'status' => 'success', 'mpesa_receipt' => 'TEST123456']);
    QuoteRequest::create(['name' => 'Test', 'phone' => '254700000000']);

    $this->artisan('nursery:clear-test-data --force')->assertSuccessful();

    expect(Order::count())->toBe(0)->and(QuoteRequest::count())->toBe(0)
        ->and(Plant::count())->toBe(52)
        ->and(Setting::get('phone'))->toBe('+254 700 000 000');
    expect(Order::factory()->create()->fresh()->number)->toBe('WN-1001');
});
