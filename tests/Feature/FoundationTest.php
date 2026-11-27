<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Plant;
use App\Models\Post;
use App\Models\Setting;

it('runs every content seeder once and is safe to re-run on each deploy', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->seed();
    Setting::put('phone', '+254 700 000 000'); // team edits a value in the dashboard

    $this->seed();

    expect(Category::count())->toBe(5)
        ->and(Post::count())->toBe(6)
        ->and(Post::published()->count())->toBe(0) // launch guides are drafts
        ->and(Setting::get('phone'))->toBe('+254 700 000 000')
        ->and(Setting::get('seeded:CategorySeeder'))->not->toBeNull()
        ->and(Setting::get('seeded:PlantImportSeeder'))->not->toBeNull()
        ->and(Plant::count())->toBe(52)                       // 33 from Wisdom Capital + 4 grafted fruit + 13 vegetable trays + 2 cypress (hidden until priced)
        ->and(Plant::where('is_featured', true)->count())->toBe(4)
        ->and(Plant::has('images')->count())->toBe(35)
        ->and(Setting::get('image_hero'))->toBe('site/nursery-45.webp') // current photos (27 Nov), not the old site's
        ->and(\App\Models\GalleryImage::whereNull('path')->count())->toBe(0);
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

it('fills the launch guides as drafts without overwriting edited ones', function () {
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\PostSeeder::class);
    Post::where('slug', 'repotting-without-stress')->update(['body' => '<p>Written by the team.</p>']);

    $this->seed(\Database\Seeders\GuideDraftsSeeder::class);

    expect(Post::where('slug', 'how-to-plant-a-tree-seedling')->value('title'))->toBe('How to plant a tree seedling')
        ->and(Post::where('slug', 'how-to-plant-a-tree-seedling')->value('body'))->toContain('graft joint')
        ->and(Post::where('slug', 'repotting-without-stress')->value('body'))->toBe('<p>Written by the team.</p>')
        ->and(Post::published()->count())->toBe(0);
});

it('adds the vegetable trays with the client\'s prices, not yet in stock', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->seed();

    $veg = Plant::whereHas('category', fn ($q) => $q->where('slug', 'vegetable-seedlings'))->get();
    expect($veg)->toHaveCount(13)
        ->and($veg->firstWhere('name', 'Tomato Ansal F1')->price)->toBe(1900)
        ->and($veg->firstWhere('name', 'Onion')->price)->toBe(1600)
        ->and($veg->every(fn ($p) => $p->sold_as === \App\Enums\SoldAs::Tray && $p->stock_qty === 0))->toBeTrue();
});

it('fills the client\'s details without overwriting what staff changed', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->seed(\Database\Seeders\SettingsSeeder::class);
    Setting::put('address', 'Edited by staff');
    $this->seed();

    expect(Setting::get('address'))->toBe('Edited by staff')
        ->and(Setting::get('about_text'))->toContain('farmer cooperatives, agrovets') // client's About text, reworded for the Kitui-advantage story
        ->and(Setting::get('till_number'))->toBe('303030')
        ->and(Setting::get('mpesa_account'))->toBe('2043089423')
        ->and(Setting::get('image_story_main'))->toBe('site/nursery-48.webp')
        ->and(\App\Models\TeamMember::where('name', '[Name]')->count())->toBe(0)
        ->and(\App\Models\TeamMember::where('role', 'like', 'In-charge, %')->count())->toBe(5)
        ->and(\App\Models\TeamMember::count())->toBe(20) // 14 from the staff list + 6 more in the team photos
        ->and(\App\Models\TeamMember::whereNull('photo_path')->pluck('name')->all())->toBe(['Jackline Nduku']);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists('site/nursery-36.webp');
});

it('swaps the old site photos for the current ones but keeps staff uploads and product photos', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\SettingsSeeder::class);
    $this->seed(\Database\Seeders\SitePhotosSeeder::class);
    Setting::put('image_story_small', 'site/01JSTAFFUPLOAD.jpg'); // uploaded in Site settings → Photos
    $this->seed(\Database\Seeders\PlantImportSeeder::class);
    $productPhotos = \App\Models\PlantImage::pluck('path')->all();

    $this->seed(\Database\Seeders\PhotoRefreshSeeder::class);

    expect(Setting::get('image_hero'))->toBe('site/nursery-45.webp')
        ->and(Setting::get('image_story_small'))->toBe('site/01JSTAFFUPLOAD.jpg')
        ->and(\App\Models\Category::where('slug', 'tree-seedlings')->value('image_path'))->toBe('site/nursery-43.webp')
        ->and(\App\Models\GalleryImage::where('path', 'site/nursery-24.webp')->exists())->toBeFalse()
        ->and(\App\Models\GalleryImage::where('path', 'site/nursery-48.webp')->exists())->toBeTrue()
        ->and(\App\Models\PlantImage::pluck('path')->all())->toBe($productPhotos);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists(['site/nursery-45.webp', 'site/nursery-45-sm.webp']);
});

it('tells why Kitui-grown plants do better, without overwriting staff edits', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->seed(\Database\Seeders\SettingsSeeder::class);
    Setting::put('how_we_grow_1', 'Written by staff');
    $this->seed();

    expect(Setting::get('how_we_grow_1'))->toBe('Written by staff')
        ->and(Setting::get('about_text'))->toContain('in Kitui, Kenya, raises plants in a semi-arid climate')
        ->and(Setting::get('story_title'))->toBe('Toughened by the climate, ready for your land.')
        ->and(Setting::get('advantage_1_title'))->toBe('Deeper, denser roots');
    $this->get('/')->assertSee('Why our plants last')->assertSee('If it thrives in our climate, it is ready for your land.');
    $this->get('/about')->assertSee('Raised tough in a semi-arid climate.');

    // Kitui is named only where the location matters: the home page names the climate, not the place.
    $text = str_replace(Setting::get('address'), '', strip_tags($this->get('/')->getContent())); // the address itself is fine
    expect(substr_count($text, 'Kitui'))->toBe(0);
});

it('replaces the first, Kitui-heavy wording on sites where it already ran', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->seed();
    Setting::put('advantage_title', 'Raised tough in semi-arid Kitui.');   // as the first version left it
    Setting::put('story_title', 'Toughened in Kitui, ready for your land.');
    Setting::put('seeded:NarrativeClimateSeeder', null);

    $this->seed();

    expect(Setting::get('advantage_title'))->toBe('Raised tough in a semi-arid climate.')
        ->and(Setting::get('story_title'))->toBe('Toughened by the climate, ready for your land.');
});
