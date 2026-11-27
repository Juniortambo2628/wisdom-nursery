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

it('downloads the WhatsApp catalogue from the plant list', function () {
    Livewire::test(ListPlants::class)->callAction('whatsapp_catalogue')->assertFileDownloaded('wisdom-propagation-nursery-catalogue.csv');
});

it('saves several phone numbers from Site settings, the first as the main one', function () {
    Livewire::test(\App\Filament\Pages\SiteSettings::class)
        ->assertFormSet(['phones' => fn ($rows) => collect($rows)->pluck('number')->values()->all() === ['[+254 7XX XXX XXX]']])
        ->set('data.phones', [
            ['number' => '0712 345 678', 'label' => 'Orders'],
            ['number' => ' 0733 111 222 ', 'label' => ''],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(json_decode(\App\Models\Setting::get('phones'), true))->toBe([
        ['number' => '0712 345 678', 'label' => 'Orders'],
        ['number' => '0733 111 222', 'label' => null],
    ])->and(\App\Models\Setting::get('phone'))->toBe('0712 345 678');
});

it('has an upload slot in Site settings for every photo the pages show', function () {
    $used = collect(\Illuminate\Support\Facades\File::allFiles(resource_path('views')))
        ->flatMap(fn ($f) => preg_match_all("/site_image\\('([a-z0-9_]+)'\\)/", $f->getContents(), $m) ? $m[1] : [])
        ->merge(['image_step_2', 'service_1_image', 'service_5_image'])->unique();

    $component = Livewire::test(\App\Filament\Pages\SiteSettings::class);
    foreach ($used as $key) {
        $component->assertFormFieldExists($key);
    }
});

it('lists every uploaded photo in the photo library and protects the ones in use', function () {
    \Illuminate\Support\Facades\Storage::disk('public')->put('library/unused.webp', 'x');
    \Illuminate\Support\Facades\Storage::disk('public')->put('site/in-use.webp', 'x');
    \App\Models\Setting::put('image_hero', 'site/in-use.webp');

    $this->get('/admin/photo-library')->assertOk()->assertSee('unused.webp')->assertSee('In use: Site settings: image_hero');

    Livewire::test(\App\Filament\Pages\MediaLibraryPage::class)
        ->call('deletePhoto', 'site/in-use.webp')
        ->call('deletePhoto', 'library/unused.webp');

    \Illuminate\Support\Facades\Storage::disk('public')->assertExists('site/in-use.webp');
    \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('library/unused.webp');
});

it('offers Choose from library on every photo box', function () {
    Livewire::test(\App\Filament\Pages\SiteSettings::class)
        ->assertFormComponentActionExists('image_hero', 'chooseFromLibrary');
});

it('compresses photos: resized, converted to WebP, with phone and thumbnail copies, references updated', function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $img = imagecreatetruecolor(3000, 2000);
    imagefill($img, 0, 0, imagecolorallocate($img, 40, 120, 60));
    ob_start(); imagejpeg($img, null, 95); $disk->put('site/big-photo.jpg', ob_get_clean());
    \App\Models\Setting::put('image_hero', 'site/big-photo.jpg');

    $this->artisan('nursery:optimize-images')->assertSuccessful();

    $disk->assertMissing('site/big-photo.jpg');
    $disk->assertExists(['site/big-photo.webp', 'site/big-photo-sm.webp', 'site/big-photo-thumb.webp']);
    expect(getimagesizefromstring($disk->get('site/big-photo.webp'))[0])->toBe(1920)
        ->and(getimagesizefromstring($disk->get('site/big-photo-thumb.webp'))[0])->toBe(400)
        ->and(\App\Models\Setting::get('image_hero'))->toBe('site/big-photo.webp')
        ->and(media_srcset('site/big-photo.webp'))->toContain('big-photo-thumb.webp 400w');
});

it('picks a library photo with one tap', function () {
    \Illuminate\Support\Facades\Storage::disk('public')->put('library/pick-me.webp', 'x');

    Livewire::test(\App\Filament\Pages\SiteSettings::class)
        ->mountFormComponentAction('image_contact_banner', 'chooseFromLibrary')
        ->callMountedFormComponentAction(['path' => 'library/pick-me.webp'])
        ->assertHasNoFormComponentActionErrors()
        ->call('save');

    expect(\App\Models\Setting::get('image_contact_banner'))->toBe('library/pick-me.webp');
});
