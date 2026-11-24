<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Plant;
use App\Models\Post;
use App\Models\Setting;

it('runs every content seeder once and is safe to re-run on each deploy', function () {
    $this->seed();
    Setting::put('phone', '+254 700 000 000'); // team edits a value in the dashboard

    $this->seed();

    expect(Category::count())->toBe(4)
        ->and(Post::count())->toBe(6)
        ->and(Post::published()->count())->toBe(0) // launch guides are drafts
        ->and(Setting::get('phone'))->toBe('+254 700 000 000')
        ->and(Setting::get('seeded:CategorySeeder'))->not->toBeNull()
        ->and(Setting::get('seeded:PlantImportSeeder'))->toBeNull(); // no CSV yet: retried next deploy
});

it('numbers orders from WN-1001', function () {
    $first = Order::factory()->create();
    $second = Order::factory()->create();

    expect($first->fresh()->number)->toBe('WN-'.($first->id + 1000))
        ->and($second->fresh()->number)->toBe('WN-'.($second->id + 1000))
        ->and($first->public_token)->toHaveLength(40);
});

it('stamps the status timestamp when an order changes status', function () {
    $order = Order::factory()->create();

    $order->transitionTo(OrderStatus::Ready);

    expect($order->fresh()->status)->toBe(OrderStatus::Ready)
        ->and($order->fresh()->ready_at)->not->toBeNull();
});

it('labels plant availability for the shop list', function () {
    expect(Plant::factory()->create()->availabilityLabel())->toBe('Ready now')
        ->and(Plant::factory()->readyInWeeks(3)->create()->availabilityLabel())->toBe('Ready in 3 weeks')
        ->and(Plant::factory()->outOfStock()->create()->availabilityLabel())->toBe('Out of stock')
        ->and(Plant::factory()->create(['stock_qty' => 0])->isBuyable())->toBeFalse();
});
