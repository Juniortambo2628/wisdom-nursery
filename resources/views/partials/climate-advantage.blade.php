{{-- "Why our plants last": why plants raised in a semi-arid climate do better after planting (Home, About). Text in Site settings → Home page. --}}
@php
    $benefits = collect(range(1, 4))
        ->map(fn ($i) => ['title' => setting("advantage_{$i}_title"), 'text' => setting("advantage_{$i}_text")])
        ->filter(fn ($b) => filled($b['title']));
    $icons = ['leaf', 'check', 'clock', 'leaf'];
@endphp
@if ($benefits->isNotEmpty())
    <section class="bg-forest py-20 text-white lg:py-28">
        <div class="container-page grid gap-12 lg:grid-cols-[5fr_7fr] lg:gap-16">
            <div data-reveal>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-moss">{{ setting('advantage_eyebrow', 'Why our plants last') }}</p>
                <h2 class="mt-4 text-4xl text-white sm:text-5xl">{{ setting('advantage_title') }}</h2>
                <p class="mt-6 text-lg leading-relaxed text-white/90">{{ setting('advantage_lead') }}</p>
                @if ($closing = setting('advantage_closing'))
                    <p class="mt-8 border-l-4 border-moss pl-5 font-display text-2xl italic text-moss">{{ $closing }}</p>
                @endif
            </div>
            <ul class="grid gap-5 sm:grid-cols-2" data-reveal data-reveal-stagger>
                @foreach ($benefits as $b)
                    <li class="rounded-image bg-white/[0.07] p-6 ring-1 ring-white/10 sm:p-7">
                        <span class="grid h-11 w-11 place-items-center rounded-full bg-moss/20 text-moss"><x-glyph :name="$icons[$loop->index] ?? 'leaf'" class="h-5 w-5" /></span>
                        <h3 class="mt-4 text-xl text-white sm:text-2xl">{{ $b['title'] }}</h3>
                        <p class="mt-2 leading-relaxed text-white/85">{{ $b['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
