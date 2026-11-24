<?php

use App\Models\Plant;
use App\Models\Post;
use Database\Seeders\DemoPlantSeeder;

beforeEach(function () {
    $this->seed();
    $this->seed(DemoPlantSeeder::class);
});

it('renders every public page', function (string $url, string $sees) {
    $this->get($url)->assertOk()->assertSee($sees, false);
})->with([
    'home' => ['/', 'Every plant gets a'],
    'our plants' => ['/plants', "What's growing now"],
    'category filter' => ['/plants?category=palms', 'Our plants'],
    'plant page' => ['/plants/swiss-cheese-plant', 'Monstera deliciosa'],
    'about' => ['/about', 'The people behind the plants.'],
    'how we grow' => ['/how-we-grow', 'From seed to your farm in four steps.'],
    'gallery' => ['/gallery', 'Gallery'],
    'grower tips' => ['/grower-tips', 'Grow it well.'],
    'contact' => ['/contact', 'Come and see us'],
    'quote' => ['/quote', 'Request a quote'],
    'cart' => ['/cart', 'Your cart is empty.'],
]);

it('filters and searches the plant list', function () {
    $this->get('/plants?category=tree-seedlings')->assertSee('Grevillea Robusta')->assertDontSee('Swiss Cheese Plant');
    $this->get('/plants?q=palm')->assertSee('Golden Palm')->assertDontSee('Neem Tree');
});

it('shows the right action for each availability', function () {
    $this->get('/plants')
        ->assertSee('Ready in 3 weeks')   // Lemon Tree: pre-order, still Add to cart
        ->assertSee('Out of stock')       // Zebra Plant
        ->assertSee('Notify me');
});

it('hides inactive plants and draft posts', function () {
    Plant::where('slug', 'alocasia')->update(['is_active' => false]);
    $this->get('/plants/alocasia')->assertNotFound();

    $draft = Post::first();
    $this->get("/grower-tips/{$draft->slug}")->assertNotFound();
    $draft->update(['published_at' => now()->subDay()]);
    $this->get("/grower-tips/{$draft->slug}")->assertOk();
});

it('marks non-production pages noindex and adds Product schema', function () {
    $this->get('/plants/swiss-cheese-plant')
        ->assertSee('noindex, nofollow', false)
        ->assertSee('"@type":"Product"', false)
        ->assertSee('"priceCurrency":"KES"', false);
});

it('serves a sitemap and blocks robots outside production', function () {
    $this->get('/sitemap.xml')->assertOk()->assertSee(route('plants.show', 'swiss-cheese-plant'), false);
    $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
});

it('shows a branded 404 page', function () {
    $this->get('/no-such-page')->assertNotFound()->assertSee('This page has been transplanted.')->assertSee('See our plants');
});

it('renders the 500 page without touching the database', function () {
    \Illuminate\Support\Facades\DB::flushQueryLog();
    \Illuminate\Support\Facades\DB::enableQueryLog();
    expect(view('errors.500')->render())->toContain('Something went wrong on our side.')
        ->and(\Illuminate\Support\Facades\DB::getQueryLog())->toBeEmpty();
});

it('sends baseline security headers', function () {
    $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});
