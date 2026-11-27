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
        {{-- Even darkening (like the home hero) so centred white text reads anywhere on the photo. --}}
        <div class="absolute inset-0 bg-gradient-to-b from-forest/85 via-forest/75 to-forest/85" aria-hidden="true"></div>
    @endif
    <div class="container-page relative flex flex-col items-center py-16 text-center sm:py-24 lg:py-32" data-reveal data-reveal-stagger>
        @if ($eyebrow)
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-moss sm:text-base">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-4 max-w-4xl text-[40px] leading-[1.05] text-white sm:text-6xl lg:text-[84px] lg:leading-[1.02]">{{ $title }}</h1>
        @if ($intro)
            <p class="mt-6 max-w-3xl text-lg font-medium leading-relaxed text-white sm:text-xl lg:text-2xl lg:leading-relaxed">{{ $intro }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="mt-8 flex w-full justify-center">{{ $slot }}</div>
        @endif
    </div>
</section>
