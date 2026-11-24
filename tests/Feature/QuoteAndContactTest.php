<?php

use App\Models\ContactMessage;
use App\Models\Plant;
use App\Models\QuoteRequest;
use App\Models\StockAlert;
use Database\Seeders\DemoPlantSeeder;

beforeEach(function () {
    $this->seed();
    $this->seed(DemoPlantSeeder::class);
});

it('sends a quote request from the quote list', function () {
    $grevillea = Plant::where('slug', 'grevillea-robusta')->first();
    $this->post('/quote/items', ['plant_id' => $grevillea->id, 'qty' => 2000]);
    $this->get('/plants')->assertSee('Added to quote');

    $this->post('/quote', ['name' => "St. Mary's School", 'phone' => '0722000000', 'location' => 'Kitui', '_ts' => time() - 10])
        ->assertSessionHas('quote_sent');

    $quote = QuoteRequest::with('items')->sole();
    expect($quote->phone)->toBe('254722000000')
        ->and($quote->items->sole())->plant_name->toBe('Grevillea Robusta')->quantity->toBe(2000)
        ->and(session('quote_list', []))->toBeEmpty();
});

it('sends a quote request from the home band without a list', function () {
    $this->post('/quote', ['name' => 'Farm', 'phone' => '0712345678', 'plants' => 'Neem seedlings', 'quantity' => 1500, '_ts' => time() - 10])
        ->assertSessionHas('quote_sent');
    expect(QuoteRequest::sole()->items->sole())->plant_name->toBe('Neem seedlings')->quantity->toBe(1500);
});

it('needs plants for a quote', function () {
    $this->post('/quote', ['name' => 'X', 'phone' => '0712345678', '_ts' => time() - 10])->assertSessionHasErrors('plants');
});

it('stores contact messages', function () {
    $this->post('/contact', ['name' => 'Peter', 'phone' => '0712345678', 'message' => 'Do you deliver to Mwingi?', '_ts' => time() - 10])
        ->assertSessionHas('contact_sent');
    expect(ContactMessage::sole()->phone)->toBe('254712345678');
});

it('records notify-me requests once per phone', function () {
    $zebra = Plant::where('slug', 'zebra-plant')->first();
    $this->post("/plants/{$zebra->slug}/notify", ['phone' => '0712345678']);
    $this->post("/plants/{$zebra->slug}/notify", ['phone' => '+254 712 345 678']);
    expect(StockAlert::count())->toBe(1);
});

it('normalises Kenyan phone numbers', function (string $in, ?string $out) {
    expect(\App\Support\Phone::normalise($in))->toBe($out);
})->with([
    ['0712 345 678', '254712345678'],
    ['+254 712 345 678', '254712345678'],
    ['0110 123 456', '254110123456'],
    ['712345678', '254712345678'],
    ['0612345678', null],
    ['12345', null],
]);

it('texts "Notify me" customers once when a plant is back in stock', function () {
    \Illuminate\Support\Facades\Queue::fake([\App\Jobs\SendAlert::class]);
    $zebra = Plant::where('slug', 'zebra-plant')->first();
    $zebra->update(['stock_qty' => 0, 'availability' => \App\Enums\Availability::OutOfStock]);
    $this->post("/plants/{$zebra->slug}/notify", ['phone' => '0712345678']);

    $zebra->update(['stock_qty' => 10, 'availability' => \App\Enums\Availability::Ready]);
    $zebra->update(['stock_qty' => 12]); // already buyable: no second text

    \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendAlert::class, 1);
    \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendAlert::class, fn ($j) => $j->event === 'back_in_stock' && $j->recipient === '254712345678' && str_contains($j->body, 'Zebra Plant is back in stock'));
    expect(StockAlert::sole()->notified_at)->not->toBeNull();
});

it('exports the plant list for the WhatsApp catalogue', function () {
    $csv = \App\Support\CatalogueExport::csv();
    $rows = array_map('str_getcsv', array_filter(explode("\n", $csv)));
    expect($rows[0])->toBe(\App\Support\CatalogueExport::COLUMNS)
        ->and($csv)->toContain('swiss-cheese-plant')
        ->and($csv)->toContain('1600.00 KES')
        ->and($csv)->toContain('in stock');
});
