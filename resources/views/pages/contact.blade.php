@php
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'GardenStore',
        'name' => 'Wisdom Propagation Nursery',
        'parentOrganization' => ['@type' => 'Organization', 'name' => 'Wisdom Capital Limited'],
        'url' => route('home'),
        'telephone' => str_starts_with((string) setting('phone'), '[') ? null : setting('phone'),
        'address' => str_starts_with((string) setting('address'), '[') ? null : setting('address'),
        'openingHours' => str_starts_with((string) setting('hours'), '[') ? null : setting('hours'),
        'geo' => setting('latitude') ? ['@type' => 'GeoCoordinates', 'latitude' => setting('latitude'), 'longitude' => setting('longitude')] : null,
        'paymentAccepted' => 'M-Pesa',
    ]);
    $whatsapp = \App\Support\WhatsApp::link('Hello Wisdom Propagation Nursery');
@endphp
<x-layouts.site title="Contact and visit" active="contact" description="Visit Wisdom Propagation Nursery, call or WhatsApp us, or send a message.">
    @push('head')
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush
    <section class="container-page pb-4 pt-12 lg:pt-20">
        <p class="eyebrow">Contact &amp; visit</p>
        <h1 class="mt-4 text-5xl sm:text-6xl lg:text-7xl">Talk to the growers.</h1>
        <div class="mt-8 flex flex-wrap gap-3">
            @if ($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-mpesa"><x-glyph name="chat" class="h-5 w-5" /> WhatsApp us</a>@endif
            @if (($phone = setting('phone')) && ! str_starts_with($phone, '['))<a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="btn-outline"><x-glyph name="phone" class="h-5 w-5" /> Call {{ $phone }}</a>@endif
        </div>
    </section>
    @include('partials.visit')
    <section id="delivery" class="container-page scroll-mt-28 py-20 lg:py-28">
        <div class="grid gap-10 md:grid-cols-2">
            <div>
                <h2 class="text-3xl">Delivery &amp; collection</h2>
                <p class="mt-4 text-ink/70">{{ setting('delivery_text') }}</p>
                @if (($areas = \App\Models\DeliveryArea::where('is_active', true)->orderBy('sort_order')->get())->isNotEmpty())
                    <ul class="card mt-6 divide-y divide-ink/10">
                        @foreach ($areas as $a)
                            <li class="flex justify-between px-5 py-3"><span>{{ $a->name }}</span><span class="font-semibold">{{ ksh($a->fee) }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div>
                <h2 class="text-3xl">Track my order</h2>
                <p class="mt-4 text-ink/70">Your order link is in the SMS we sent when you paid. Or WhatsApp us your order number (it starts with WN-) and we'll update you.</p>
            </div>
        </div>
    </section>
</x-layouts.site>
