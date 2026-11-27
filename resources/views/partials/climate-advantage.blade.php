{{-- "Why Choose Wisdom Nursery?" (Home, About): the client's five points. Text in Site settings → Home page. --}}
@php
    $points = collect(range(1, 5))
        ->map(fn ($i) => ['title' => setting("advantage_{$i}_title"), 'text' => setting("advantage_{$i}_text")])
        ->filter(fn ($p) => filled($p['title']) && filled($p['text']));
@endphp
@if ($points->isNotEmpty())
    <section class="bg-forest py-20 text-white lg:py-28">
        <div class="container-page">
            <div class="max-w-3xl" data-reveal>
                @if ($eyebrow = setting('advantage_eyebrow'))
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-moss">{{ $eyebrow }}</p>
                @endif
                <h2 class="mt-4 text-4xl text-white sm:text-5xl">{{ setting('advantage_title', 'Why Choose Wisdom Nursery?') }}</h2>
                @if ($lead = setting('advantage_lead'))
                    <p class="mt-6 text-lg leading-relaxed text-white/90">{{ $lead }}</p>
                @endif
            </div>
            <ul class="mt-12 flex flex-wrap justify-center gap-5" data-reveal data-reveal-stagger>
                @foreach ($points as $p)
                    <li class="w-full rounded-image bg-white/[0.07] p-6 ring-1 ring-white/10 sm:w-[calc(50%-0.625rem)] sm:p-7 lg:w-[calc(33.333%-0.84rem)]">
                        <span class="grid h-11 w-11 place-items-center rounded-full bg-moss/20 font-display text-xl text-moss">{{ $loop->iteration }}</span>
                        <h3 class="mt-4 text-xl text-white sm:text-2xl">{{ $p['title'] }}</h3>
                        <p class="mt-2 leading-relaxed text-white/85">{{ $p['text'] }}</p>
                    </li>
                @endforeach
            </ul>
            @if ($closing = setting('advantage_closing'))
                <p class="mt-12 text-center font-display text-2xl italic text-moss" data-reveal>{{ $closing }}</p>
            @endif
        </div>
    </section>
@endif
