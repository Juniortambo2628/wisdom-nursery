<x-layouts.site title="How we grow" active="how"
    description="How Wisdom Propagation Nursery raises its plants, from seed to dispatch.">
    @php
        $steps = [
            ['Seed', setting('how_we_grow_1'), 'Sowing in clean trays'],
            ['Germination', setting('how_we_grow_2'), 'Germination room'],
            ['Hardening', setting('how_we_grow_3'), 'Under the shade net'],
            ['Dispatch', setting('how_we_grow_4'), 'Loading crates'],
        ];
    @endphp
    <x-page-hero eyebrow="How we grow" title="From seed to your farm in four steps." :image="site_image('image_how_banner') ?? site_image('image_facility_2')" :srcset="site_image_srcset('image_how_banner')" />
    <section class="container-page pb-16 pt-16 lg:pb-20 lg:pt-24">
        <ol class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4" data-reveal data-reveal-stagger>
            @foreach ($steps as $i => [$name, $text, $photo])
                <li>
                    <x-photo :src="site_image('image_step_'.($i + 1))" :label="$photo" class="aspect-[4/3] w-full rounded-image" />
                    <p class="mt-5 text-sm font-semibold text-fern">{{ sprintf('%02d', $i + 1) }}</p>
                    <h2 class="mt-1 text-2xl">{{ $name }}</h2>
                    <p class="mt-2 text-ink/80">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>
    @include('partials.facilities')
    <section class="container-page py-20 text-center lg:py-28">
        <h2 class="text-3xl sm:text-4xl">Need seedlings raised for a project?</h2>
        <p class="mx-auto mt-4 max-w-xl text-lg text-ink/80">We can also raise seedlings from your own seed. Tell us what you need and when.</p>
        <a href="{{ route('quote.show') }}" class="btn-dark mt-8">Request a quote</a>
    </section>
</x-layouts.site>
