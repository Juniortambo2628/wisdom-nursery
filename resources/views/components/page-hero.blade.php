@props([
    'eyebrow' => null,  // small label above the title, e.g. "About us"
    'title',
    'image' => null,    // background photo URL; without one the band is plain forest green
    'srcset' => null,
    'intro' => null,    // a sentence or two under the title
])
{{-- Large page header for inner pages: photo, dark overlay, big white title, so each page is clearly announced. --}}
<section {{ $attributes->class('relative overflow-hidden bg-forest text-white') }}>
    @if ($image)
        <img src="{{ $image }}" @if ($srcset) srcset="{{ $srcset }}" sizes="100vw" @endif alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-r from-forest/95 via-forest/80 to-forest/50" aria-hidden="true"></div>
    @endif
    <div class="container-page relative py-20 sm:py-24 lg:py-32">
        @if ($eyebrow)
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-moss sm:text-base">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-4 max-w-4xl text-5xl leading-[1.02] text-white sm:text-6xl lg:text-[84px]">{{ $title }}</h1>
        @if ($intro)
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-white/90 sm:text-xl">{{ $intro }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="mt-8">{{ $slot }}</div>
        @endif
    </div>
</section>
