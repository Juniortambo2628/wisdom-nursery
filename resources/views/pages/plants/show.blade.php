@php
    $images = $plant->images;
    $soldAsNoun = match ($plant->sold_as->value) {
        'potted' => 'per potted plant', 'per_plant' => 'per plant', 'seedling' => 'per seedling', 'tray' => 'per tray', default => '',
    };
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $plant->name,
        'description' => strip_tags((string) $plant->description),
        'category' => $plant->category->name,
        'image' => $images->map->url()->values()->all(),
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'KES',
            'price' => $plant->price,
            'availability' => $plant->isBuyable() ? ($plant->isComingSoon() ? 'https://schema.org/PreOrder' : 'https://schema.org/InStock') : 'https://schema.org/OutOfStock',
            'url' => route('plants.show', $plant),
        ],
    ];
    if ($plant->botanical_name) { $schema['alternateName'] = $plant->botanical_name; }
@endphp
<x-layouts.site :title="$plant->name" active="plants"
    :description="$plant->name.' from Wisdom Propagation Nursery, '.ksh($plant->price).' '.$soldAsNoun.'. Pay with M-Pesa, collect or get it delivered.'"
    :og-image="$images->first()?->url()">
    @push('head')
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    <div class="container-page py-8 lg:py-12">
        <nav aria-label="Breadcrumb" class="text-sm text-ink/70">
            <ol class="flex flex-wrap gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-leaf">Home</a> /</li>
                <li><a href="{{ route('plants.index') }}" class="hover:text-leaf">Our plants</a> /</li>
                <li><a href="{{ route('plants.index', ['category' => $plant->category->slug]) }}" class="hover:text-leaf">{{ $plant->category->name }}</a> /</li>
                <li aria-current="page" class="font-semibold text-ink">{{ $plant->name }}</li>
            </ol>
        </nav>

        <div class="mt-6 grid gap-10 lg:mt-10 lg:grid-cols-2 lg:gap-16">
            {{-- Gallery --}}
            <div x-data="{ active: 0 }">
                @if ($images->isEmpty())
                    <x-photo :label="'Main photo: '.$plant->name" class="aspect-square w-full rounded-image-lg" />
                    <div class="mt-4 grid grid-cols-4 gap-3">
                        @foreach (['In pot', 'Leaf close-up', 'Grown plant', 'In place'] as $thumb)
                            <x-photo :label="$thumb" @class(['aspect-[4/3] w-full rounded-image text-[10px]', 'ring-2 ring-leaf ring-offset-2 ring-offset-mist' => $loop->first]) />
                        @endforeach
                    </div>
                @else
                    @foreach ($images as $i => $image)
                        <img x-show="active === {{ $i }}" @if ($i) x-cloak @endif src="{{ $image->url() }}" alt="{{ $image->alt ?: $plant->name }}" class="aspect-square w-full rounded-image-lg object-cover">
                    @endforeach
                    @if ($images->count() > 1)
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            @foreach ($images as $i => $image)
                                <button type="button" @click="active = {{ $i }}" class="overflow-hidden rounded-image ring-offset-2 ring-offset-mist" :class="active === {{ $i }} && 'ring-2 ring-leaf'" aria-label="Show photo {{ $i + 1 }}">
                                    <img src="{{ $image->url() }}" alt="" class="aspect-[4/3] w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Details --}}
            <div x-data="{ buyAs: 'single', qty: 1 }">
                <p @class(['text-xs font-bold uppercase tracking-[0.12em]',
                    'text-leaf' => $plant->availabilityTone() === 'ready',
                    'text-clay' => $plant->availabilityTone() === 'soon',
                    'text-red-700' => $plant->availabilityTone() === 'out'])>
                    ● {{ $plant->availabilityTone() === 'ready' ? 'In stock · '.number_format($plant->stock_qty).' available' : $plant->availabilityLabel() }}
                </p>
                <h1 class="mt-3 text-4xl sm:text-5xl lg:text-6xl">{{ $plant->name }}</h1>
                @if ($plant->botanical_name)
                    <p class="mt-3 font-display text-xl italic text-ink/80">{{ $plant->botanical_name }}</p>
                @endif
                @if ($plant->description)
                    <div class="mt-5 max-w-xl text-lg text-ink/80">{!! nl2br(e($plant->description)) !!}</div>
                @endif

                <p class="mt-7 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <span class="whitespace-nowrap font-display text-4xl text-forest sm:text-5xl">{{ ksh($plant->price) }}</span>
                    <span class="text-ink/70">{{ $soldAsNoun }}</span>
                </p>

                @if ($plant->isBuyable())
                    @if (($plant->set_size && $plant->set_price) || $plant->bulk_min_qty)
                        <fieldset class="mt-7">
                            <legend class="label">Buy as</legend>
                            <div class="flex flex-wrap gap-2">
                                @php $opt = 'cursor-pointer rounded-full border px-4 py-2 text-sm transition has-[:checked]:border-forest has-[:checked]:bg-forest has-[:checked]:text-white has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-leaf border-ink/15 bg-white hover:border-leaf'; @endphp
                                <label class="{{ $opt }}"><input type="radio" class="sr-only" value="single" x-model="buyAs"> Single {{ $plant->sold_as->value === 'tray' ? 'tray' : 'plant' }}</label>
                                @if ($plant->set_size && $plant->set_price)
                                    <label class="{{ $opt }}"><input type="radio" class="sr-only" value="set" x-model="buyAs"> Set of {{ $plant->set_size }} · {{ ksh($plant->set_price) }}</label>
                                @endif
                                @if ($plant->bulk_min_qty)
                                    <label class="{{ $opt }}"><input type="radio" class="sr-only" value="bulk" x-model="buyAs"> Bulk ({{ number_format($plant->bulk_min_qty) }}+)</label>
                                @endif
                            </div>
                        </fieldset>
                    @endif

                    {{-- Cart --}}
                    <form method="POST" action="{{ route('cart.add') }}" class="mt-6 flex gap-3" x-show="buyAs !== 'bulk'">
                        @csrf
                        <input type="hidden" name="plant_id" value="{{ $plant->id }}">
                        <input type="hidden" name="buy_as" :value="buyAs">
                        <div class="flex items-center rounded-full border border-ink/15 bg-white">
                            <button type="button" class="grid h-12 w-12 place-items-center text-forest disabled:opacity-40" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1" aria-label="One less"><x-glyph name="minus" class="h-4 w-4" /></button>
                            <label for="qty" class="sr-only">Quantity</label>
                            <input id="qty" name="qty" type="number" min="1" max="{{ \App\Support\Cart::MAX_QTY }}" x-model.number="qty" class="h-12 w-12 border-0 bg-transparent p-0 text-center font-semibold [appearance:textfield] focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none">
                            <button type="button" class="grid h-12 w-12 place-items-center text-forest" @click="qty = Math.min({{ \App\Support\Cart::MAX_QTY }}, qty + 1)" aria-label="One more"><x-glyph name="plus" class="h-4 w-4" /></button>
                        </div>
                        <button class="btn-primary flex-1 text-base"><x-glyph name="cart" class="h-5 w-5" /> {{ $inCart ? 'Add more to cart' : 'Add to cart' }}</button>
                    </form>

                    {{-- Bulk → quote list --}}
                    @if ($plant->bulk_min_qty)
                        <form method="POST" action="{{ route('quote.items') }}" class="mt-6 flex gap-3" x-cloak x-show="buyAs === 'bulk'">
                            @csrf
                            <input type="hidden" name="plant_id" value="{{ $plant->id }}">
                            <label for="bulk-qty" class="sr-only">Quantity</label>
                            <input id="bulk-qty" name="qty" type="number" min="{{ $plant->bulk_min_qty }}" value="{{ $plant->bulk_min_qty }}" class="input w-32 rounded-full text-center font-semibold">
                            <button class="btn-dark flex-1 text-base">Add to quote list</button>
                        </form>
                    @endif

                    @if ($plant->isComingSoon())
                        <p class="mt-3 text-sm text-clay">Pre-order: {{ Str::lcfirst($plant->availabilityLabel()) }} ({{ $plant->ready_on->format('j M') }}). We'll SMS you when it's ready.</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('plants.notify', $plant) }}" class="mt-7 max-w-md">
                        @csrf
                        <x-field name="phone" label="Get an SMS when it's back" type="tel" placeholder="07XX XXX XXX" required autocomplete="tel" />
                        <button class="btn-dark mt-3 w-full">Notify me</button>
                    </form>
                @endif

                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn mt-3 w-full border border-forest text-base text-forest hover:bg-mist"><x-glyph name="chat" class="h-5 w-5" /> Order this on WhatsApp</a>
                @endif

                <p class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-ink/80">
                    <x-mpesa-badge /> Pay on your phone <span aria-hidden="true">·</span> Collect, or delivery to {{ setting('delivery_areas_summary', '[areas]') }}
                </p>

                @php
                    $facts = array_filter([
                        'Light' => $plant->light, 'Water' => $plant->water,
                        'Pot size' => $plant->pot_size, 'Grows to' => $plant->grows_to,
                    ]);
                @endphp
                @if ($facts)
                    <dl class="mt-7 grid grid-cols-2 gap-x-6 gap-y-5 rounded-card bg-sage/70 p-6">
                        @foreach ($facts as $label => $value)
                            <div><dt class="text-sm text-ink/70">{{ $label }}</dt><dd class="mt-0.5 font-semibold">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </div>
    </div>

    <section class="mt-12 bg-white py-14 lg:mt-16 lg:py-20">
        <div class="container-page grid gap-10 md:grid-cols-3">
            <div>
                <h2 class="text-2xl">Planting &amp; care</h2>
                <p class="mt-3 text-ink/80">{{ setting('care_text', '[Potting, watering, feeding and common problems.]') }} <a href="{{ route('posts.index') }}" class="font-semibold text-leaf hover:underline">Grower tips</a></p>
            </div>
            <div>
                <h2 class="text-2xl">Delivery &amp; collection</h2>
                <p class="mt-3 text-ink/80">{{ setting('delivery_text', '[Delivery areas, fees and how long plants keep after collection.]') }}</p>
            </div>
            <div>
                <h2 class="text-2xl">Questions?</h2>
                <p class="mt-3 text-ink/80">Our agronomist answers on WhatsApp, {{ setting('agronomist_hours', '[days & hours]') }}.</p>
            </div>
        </div>
    </section>

    @if ($pairings->isNotEmpty())
        <section class="container-page py-20 lg:py-28">
            <x-section-heading title="Goes well with" />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pairings as $pair)
                    <x-plant-card :plant="$pair" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
