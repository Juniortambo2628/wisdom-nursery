<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\PlantResource\Pages\ListPlants;
use App\Jobs\SendAlert;
use App\Models\Order;
use App\Models\Plant;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\DemoPlantSeeder;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    $this->seed(DemoPlantSeeder::class);
    $this->actingAs(User::factory()->create());
});

it('keeps the dashboard behind a login', function () {
    auth()->logout();
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('opens every dashboard page', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Paid, 'paid_at' => now()]);
    $quote = QuoteRequest::create(['name' => 'School', 'phone' => '254712345678']);

    foreach (['/admin', '/admin/orders', "/admin/orders/{$order->id}", '/admin/plants', '/admin/plants/create',
        '/admin/plants/'.Plant::first()->slug.'/edit', '/admin/quote-requests', "/admin/quote-requests/{$quote->id}/edit",
        '/admin/contact-messages', '/admin/categories', '/admin/delivery-areas', '/admin/posts', '/admin/team-members',
        '/admin/gallery-images', '/admin/testimonials', '/admin/partners', '/admin/alert-recipients',
        '/admin/notification-logs', '/admin/site-settings'] as $url) {
        $this->get($url)->assertOk();
    }
});

it('marks an order ready in one tap and texts the customer', function () {
    Queue::fake();
    $order = Order::factory()->create(['status' => OrderStatus::Paid, 'paid_at' => now()]);

    Livewire::test(ListOrders::class)->callTableAction('mark_ready', $order);

    expect($order->fresh()->status)->toBe(OrderStatus::Ready)->and($order->fresh()->ready_at)->not->toBeNull();
    Queue::assertPushed(SendAlert::class, fn ($j) => $j->event === 'order_ready');
});

it('confirms a customer-entered M-Pesa code', function () {
    Queue::fake();
    $order = Order::factory()->create(['total' => 1600]);
    $payment = $order->payments()->create(['method' => 'manual_code', 'amount' => 1600, 'mpesa_receipt' => 'SKL4H7ABCD', 'status' => PaymentStatus::PendingVerification]);

    Livewire::test(PaymentsRelationManager::class, ['ownerRecord' => $order, 'pageClass' => ViewOrder::class])
        ->callTableAction('confirm', $payment);

    expect($order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Success)
        ->and($payment->fresh()->verified_by)->toBe(auth()->id());
});

it('edits price and stock from the plant list', function () {
    $plant = Plant::first();
    Livewire::test(ListPlants::class)
        ->call('updateTableColumnState', 'price', (string) $plant->id, 1800)
        ->call('updateTableColumnState', 'stock_qty', (string) $plant->id, 7);
    expect($plant->fresh())->price->toBe(1800)->stock_qty->toBe(7);
});

it('prints QR labels for selected plants', function () {
    $plants = Plant::limit(4)->get();
    Livewire::test(ListPlants::class)->callTableBulkAction('qr_labels', $plants)->assertFileDownloaded('wisdom-nursery-qr-labels.pdf');
});
