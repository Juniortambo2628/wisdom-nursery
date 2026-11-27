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
    'home' => ['/', 'Growing Green.'],
    'our plants' => ['/plants', 'What’s growing now'],
    'category filter' => ['/plants?category=palms', 'Our plants'],
    'plant page' => ['/plants/swiss-cheese-plant', 'Monstera deliciosa'],
    'about' => ['/about', 'The people behind the plants.'],
    'how we grow' => ['/how-we-grow', 'From seed to your farm in four steps.'],
    'services' => ['/services', 'Five ways we help you grow'],
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

it('hides WhatsApp links while the number is a placeholder', function () {
    config()->set('nursery.whatsapp', '2547XXXXXXXX');
    expect(\App\Support\WhatsApp::link('Hi'))->toBeNull();
    config()->set('nursery.whatsapp', '0712 345 678');
    expect(\App\Support\WhatsApp::link('Hi'))->toBe('https://wa.me/254712345678?text=Hi');
});

it('shows every phone number staff add, each as a call link', function () {
    \App\Models\Setting::put('phones', json_encode([
        ['number' => '0712 345 678', 'label' => 'Orders'],
        ['number' => '0733 111 222', 'label' => null],
    ]));

    $this->get('/contact')->assertOk()
        ->assertSee('href="tel:0712345678"', false)->assertSee('0712 345 678</a> (Orders)', false)
        ->assertSee('href="tel:0733111222"', false)
        ->assertSee('Call Orders: 0712 345 678', false)
        ->assertSee('"telephone":"0712 345 678"', false);
});

it('still shows the single phone number saved before lists were allowed', function () {
    \App\Models\Setting::put('phone', '0700 000 000');

    expect(site_phones())->toBe([['number' => '0700 000 000', 'label' => null, 'tel' => '0700000000']]);
    $this->get('/')->assertSee('href="tel:0700000000"', false);
});

it('shows a placeholder number as text, not a call link', function () {
    expect(site_phones()[0])->toMatchArray(['number' => '[+254 7XX XXX XXX]', 'tel' => null]);
    $this->get('/contact')->assertSee('[+254 7XX XXX XXX]', false)->assertDontSee('href="tel:', false);
});

it('shows a plant from the chosen category in the plants page photo', function () {
    $plant = Plant::whereHas('category', fn ($q) => $q->where('slug', 'tree-seedlings'))->has('images')->ordered()->first();
    expect($plant)->not->toBeNull();

    $this->get('/plants?category=tree-seedlings')->assertSee($plant->primaryImage->url(), false);
    $this->get('/plants')->assertSee(site_image('image_shop_banner'), false);
});

it('has a Home link and shows the paybill with its account number', function () {
    $this->get('/about')->assertSee('>Home</a>', false)
        ->assertSee('Paybill <strong>303030</strong>, account <strong>2043089423</strong>', false)
        ->assertSee('What we grow')->assertSee('Kilimanjaro Greenhouse')->assertSee('Jacob Malonza');
});

it('shows plants as a grid by default, with a list view to switch to', function () {
    $this->get('/plants')->assertOk()
        ->assertSee('aria-label="Grid view"', false)->assertDontSee('<table', false)->assertSee('Swiss Cheese Plant');
    $this->get('/plants?view=list')->assertOk()->assertSee('<table', false)->assertSee('Swiss Cheese Plant')
        ->assertSee('category=indoor-plants&amp;view=list', false); // the choice stays on category links
});

it('shows the five services, each linking somewhere useful', function () {
    $this->get('/services')->assertOk()
        ->assertSeeInOrder(['Seed propagation', 'Cuttings propagation', 'Agronomy support', 'Crop information', 'Ornamentals &amp; garden'], false)
        ->assertSee('category=fruit-trees', false)->assertSee(route('posts.index'), false);
    $this->get('/')->assertSee('>Services</a>', false);
});

it('shows the client testimonial on the home page', function () {
    $this->get('/')->assertSee('saving us costly replacements')->assertSee('Faith W.')->assertSee('What our customers say')->assertDontSee('[Customer testimonial]');
});
