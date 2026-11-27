<x-layouts.site active="home">
    @php
        $steps = [
            ['Seed', setting('how_we_grow_1'), 'Sowing in clean trays'],
            ['Germination', setting('how_we_grow_2'), 'Germination room'],
            ['Hardening', setting('how_we_grow_3'), 'Under the shade net'],
            ['Dispatch', setting('how_we_grow_4'), 'Loading crates'],
        ];
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-forest text-white">
        @if ($hero = site_image('image_hero'))
            <img src="{{ $hero }}" @if ($srcset = site_image_srcset('image_hero')) srcset="{{ $srcset }}" sizes="100vw" @endif
                 alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            {{-- Darken the photo evenly so the centred headline reads. --}}
            <div class="absolute inset-0 bg-gradient-to-b from-forest/80 via-forest/65 to-forest/85" aria-hidden="true"></div>
        @else
            <x-photo label="Full-screen photo: team in uniforms" dark class="absolute inset-0 h-full w-full items-start pt-6 text-[11px] sm:items-center sm:pt-0 sm:text-transparent" />
        @endif
        <div class="container-page relative flex flex-col items-center pb-28 pt-40 text-center sm:pb-36 sm:pt-32 lg:pb-44 lg:pt-44">
            <p class="text-xl font-semibold uppercase leading-snug tracking-[0.2em] text-white sm:whitespace-nowrap sm:text-[22px] sm:tracking-[0.24em] md:text-[28px] lg:text-[36px]">Wisdom Propagation Nursery</p>
            <p class="eyebrow mt-3 text-moss">Kitui, Kenya · Since {{ setting('since_year', '[Year]') }}</p>
            <h1 class="mt-6 max-w-4xl text-[44px] leading-[1.02] text-white sm:text-6xl lg:text-[88px]">
                Every plant gets a <em class="font-normal italic text-moss">strong</em> start.
            </h1>
            <p class="mt-6 max-w-2xl text-lg text-white/85">Fruit trees, seedlings, ornamentals and indoor plants from the Wisdom Capital family, for homes, offices, farms and planting projects.</p>
            <div class="mt-8 flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-center">
                <a href="{{ route('plants.index') }}" class="btn bg-white text-forest hover:bg-mist">Shop plants</a>
                <a href="#quote" class="btn border border-white/60 text-white hover:bg-white/10">Request a bulk quote</a>
                <p class="hidden items-center gap-2 text-sm text-white/85 sm:ml-3 sm:flex"><x-mpesa-badge /> Pay on your phone</p>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="container-page relative z-10 -mt-14 sm:-mt-16">
        <dl class="grid grid-cols-2 overflow-hidden rounded-card bg-white shadow-lift ring-1 ring-ink/5 lg:grid-cols-4">
            @foreach (range(1, 4) as $i)
                <div class="flex flex-col-reverse border-ink/10 p-5 sm:p-7 [&:nth-child(odd)]:border-r lg:[&:not(:last-child)]:border-r [&:nth-child(-n+2)]:border-b lg:[&:nth-child(-n+2)]:border-b-0">
                    <dt class="mt-1 text-sm text-ink/70">{{ setting("stat_{$i}_label") }}</dt>
                    <dd class="font-display text-3xl text-forest sm:text-4xl">{{ setting("stat_{$i}_value") }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Our story --}}
    <section class="container-page grid items-center gap-12 py-20 lg:grid-cols-2 lg:gap-20 lg:py-28">
        <div class="relative pb-16 pr-16 sm:pb-24 sm:pr-24">
            <x-photo :src="site_image('image_story_main')" label="Founder / manager portrait" class="aspect-[9/10] w-full rounded-image-lg" />
            <div class="absolute bottom-0 right-0 w-1/2 rounded-image-lg bg-mist p-2.5">
                <x-photo :src="site_image('image_story_small')" label="Hands potting a seedling" class="aspect-square w-full rounded-image" />
            </div>
        </div>
        <div>
            <p class="eyebrow">Our story</p>
            <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl">{{ setting('story_title') }}</h2>
            <p class="mt-5 max-w-xl text-lg text-ink/80">{{ setting('story_text') }}</p>
            <a href="{{ route('about') }}" class="btn-outline mt-8 border-forest text-forest">Meet the team</a>
        </div>
    </section>

    {{-- What we grow --}}
    <section class="bg-white py-20 lg:py-28">
        <div class="container-page">
            <x-section-heading eyebrow="What we grow" title="Grown here, ready to plant.">
                <x-slot:action><a href="{{ route('plants.index') }}" class="btn-outline border-forest text-forest">See all plants</a></x-slot:action>
            </x-section-heading>
            <div class="mt-10 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($categories as $category)
                    <a href="{{ route('plants.index', ['category' => $category->slug]) }}" class="group">
                        <x-photo :src="$category->image_path ? Storage::disk('public')->url($category->image_path) : null" :label="$category->name" class="aspect-[4/3] w-full rounded-image transition group-hover:opacity-90" />
                        <div class="mt-4 flex items-baseline justify-between gap-3">
                            <h3 class="text-xl group-hover:text-leaf">{{ $category->name }}</h3>
                            <span class="shrink-0 text-sm text-ink/70">
                                @if ($category->plants_min_price) From {{ ksh($category->plants_min_price) }} @else Ask us @endif
                            </span>
                        </div>
                        <p class="mt-1.5 text-sm text-ink/70">{{ $category->summary }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Popular this season --}}
    @if ($popular->isNotEmpty())
        <section class="container-page py-20 lg:py-28">
            <x-section-heading eyebrow="Shop online" title="Popular this season">
                <x-slot:action><p class="flex items-center gap-2 text-sm text-ink/80"><x-mpesa-badge /> Checkout in under a minute</p></x-slot:action>
            </x-section-heading>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($popular as $plant)
                    <x-plant-card :plant="$plant" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- How we grow --}}
    <section class="bg-forest py-20 text-white lg:py-28">
        <div class="container-page">
            <x-section-heading eyebrow="How we grow" title="From seed to your farm in four steps." dark class="max-w-2xl" />
            <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $i => [$name, $text, $photo])
                    <li>
                        <x-photo :src="site_image('image_step_'.($i + 1))" :label="$photo" dark class="aspect-[4/3] w-full rounded-image" />
                        <p class="mt-5 text-sm font-semibold text-moss">{{ sprintf('%02d', $i + 1) }}</p>
                        <h3 class="mt-1 text-2xl text-white">{{ $name }}</h3>
                        <p class="mt-2 text-white/75">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Bulk quote band --}}
    <section id="quote" class="container-page scroll-mt-28 py-20 lg:py-28">
        <div class="grid items-center gap-10 rounded-image-lg bg-sage p-6 sm:p-10 lg:grid-cols-2 lg:p-16">
            <div>
                <p class="eyebrow">Farms, schools, estates &amp; landscapers</p>
                <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl">Planting 1,000 trees or more? Get a quote.</h2>
                <p class="mt-5 max-w-lg text-lg text-ink/80">Tell us what you need and where. We reply with prices and a ready date, usually the same day. We can also raise seedlings from your own seed.</p>
                <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold text-forest">
                    <li class="flex items-center gap-1.5"><x-glyph name="check" class="h-4 w-4" /> Contract propagation</li>
                    <li class="flex items-center gap-1.5"><x-glyph name="check" class="h-4 w-4" /> Delivery in crates</li>
                    <li class="flex items-center gap-1.5"><x-glyph name="check" class="h-4 w-4" /> Agronomy advice</li>
                </ul>
            </div>
            <x-quote-form />
        </div>
    </section>

    {{-- At the nursery --}}
    <section class="bg-white py-20 lg:py-28">
        <div class="container-page">
            <x-section-heading title="At the nursery">
                <x-slot:action><a href="{{ route('gallery') }}" class="btn-outline border-forest text-forest">Open gallery</a></x-slot:action>
            </x-section-heading>
            <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4 lg:grid-rows-2">
                @foreach ($gallery as $image)
                    <x-photo :src="$image->path ? Storage::disk('public')->url($image->path) : null" :label="$image->caption"
                        @class(['w-full rounded-image', 'col-span-2 row-span-2 aspect-square lg:aspect-auto lg:h-full' => $loop->first, 'aspect-[4/3]' => ! $loop->first]) />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonial + partners --}}
    @if ($testimonial || $partners->isNotEmpty())
        <section class="container-page grid gap-12 py-20 lg:grid-cols-2 lg:py-28">
            @if ($testimonial)
                <figure>
                    <span class="font-display text-6xl leading-none text-leaf" aria-hidden="true">“</span>
                    <blockquote class="mt-2 font-display text-2xl text-forest sm:text-3xl">{{ $testimonial->quote }}</blockquote>
                    <figcaption class="mt-5 text-sm text-ink/80"><strong class="font-semibold text-ink">{{ $testimonial->author }}</strong>@if ($testimonial->context), {{ $testimonial->context }}@endif</figcaption>
                </figure>
            @endif
            @if ($partners->isNotEmpty())
                <div>
                    <p class="eyebrow">Trusted by</p>
                    <ul class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($partners as $partner)
                            <li><x-photo :src="$partner->logo_path ? Storage::disk('public')->url($partner->logo_path) : null" :label="$partner->name" class="h-16 w-full rounded-xl object-contain" /></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif

    {{-- Visit us --}}
    @include('partials.visit')
</x-layouts.site>
