@props([
    'title' => null,
    'description' => null,
    'active' => null,    // nav key: home | about | services | plants | how | gallery | tips | contact
    'ogImage' => null,
    'floatingWhatsapp' => true, // hidden on checkout and payment pages, where it would cover the form
])
@php
    $nav = [
        'home' => ['Home', route('home')],
        'about' => ['About us', route('about')],
        'services' => ['Services', route('services')],
        'plants' => ['Our plants', route('plants.index')],
        'how' => ['How we grow', route('how-we-grow')],
        'gallery' => ['Gallery', route('gallery')],
        'tips' => ['Grower tips', route('posts.index')],
        'contact' => ['Contact', route('contact')],
    ];
    $pageTitle = $title ? $title.' · Wisdom Nursery' : 'Wisdom Nursery · '.setting('site_tagline', 'Every plant gets a strong start');
    $metaDescription = $description ?? 'Wisdom Nursery in Kitui County: quality ornamental plants, grafted fruit trees and horticultural seedlings, with reliable year-round supply for customers across Kenya. Pay with M-Pesa, collect or get it delivered.';
    $whatsapp = \App\Support\WhatsApp::link('Hello Wisdom Nursery');
    $phones = site_phones();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if (! app()->isProduction())
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Wisdom Nursery">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-default.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    @include('partials.head-icons')
    {{-- Scroll animations: hide [data-reveal] before first paint only if motion is allowed; show everything if the script hasn't started in 3s. --}}
    <script>
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('reveal-ready');
            setTimeout(function () { if (!window.__revealStarted) document.documentElement.classList.remove('reveal-ready'); }, 3000);
        }
    </script>
    <meta name="theme-color" content="#123524">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @if (config('nursery.ga4_id') && app()->isProduction())
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('nursery.ga4_id') }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ config('nursery.ga4_id') }}');</script>
    @endif
</head>
<body class="min-h-screen bg-mist" x-data="{ menu: false }" :class="menu && 'overflow-hidden xl:overflow-auto'">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

    {{-- Top strip --}}
    <div class="hidden bg-forest text-xs text-white/85 sm:block">
        <div class="container-page flex items-center justify-between gap-4 py-2.5">
            <p>Pay with M-Pesa · Collect at the nursery or get it delivered</p>
            <p>Call / WhatsApp <x-phone-numbers /> · {{ setting('hours') }}</p>
        </div>
    </div>

    {{-- Header --}}
    <header class="sticky top-0 z-30 border-b border-ink/10 bg-mist">
        <div class="container-page flex h-[72px] items-center justify-between gap-4 lg:h-24">
            <x-logo />
            <nav aria-label="Main" class="hidden items-center gap-6 xl:flex 2xl:gap-8">
                @foreach ($nav as $key => [$label, $href])
                    <a href="{{ $href }}" @if ($active === $key) aria-current="page" @endif
                       @class([
                           'whitespace-nowrap border-b-2 py-1 text-[15px] transition',
                           'border-leaf text-leaf' => $active === $key,
                           'border-transparent text-ink hover:text-leaf' => $active !== $key,
                       ])>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('quote.show') }}" class="btn hidden border border-forest px-4 py-2 text-forest hover:bg-forest hover:text-white sm:inline-flex">Request a quote</a>
                <a href="{{ route('cart.show') }}" class="relative grid h-12 w-12 place-items-center rounded-full border border-ink/10 bg-white text-forest hover:border-leaf" aria-label="Cart, {{ $cartCount }} {{ \Illuminate\Support\Str::plural('item', $cartCount) }}">
                    <x-glyph name="cart" />
                    @if ($cartCount > 0)
                        <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-clay px-1 text-[11px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
                <button type="button" class="grid h-12 w-10 place-items-center text-forest xl:hidden" @click="menu = true" aria-label="Open menu" :aria-expanded="menu">
                    <x-glyph name="menu" class="h-6 w-6" />
                </button>
            </div>
        </div>
    </header>

    {{-- Mobile menu --}}
    <div x-cloak x-show="menu" x-transition.opacity class="fixed inset-0 z-40 bg-ink/40 xl:hidden" @click="menu = false"></div>
    <nav x-cloak x-show="menu" x-transition:enter="transition" x-transition:enter-start="translate-x-full" x-transition:leave="transition" x-transition:leave-end="translate-x-full"
         @keydown.escape.window="menu = false" aria-label="Mobile"
         class="fixed inset-y-0 right-0 z-50 flex w-[85%] max-w-sm flex-col bg-mist p-6 shadow-xl xl:hidden">
        <div class="flex items-center justify-between">
            <x-logo />
            <button type="button" class="grid h-11 w-11 place-items-center text-forest" @click="menu = false" aria-label="Close menu"><x-glyph name="close" class="h-6 w-6" /></button>
        </div>
        <ul class="mt-8 space-y-1">
            @foreach ($nav as $key => [$label, $href])
                <li><a href="{{ $href }}" @class(['block rounded-xl px-4 py-3 text-lg font-display', 'bg-sage text-forest' => $active === $key, 'text-forest hover:bg-sage' => $active !== $key])>{{ $label }}</a></li>
            @endforeach
        </ul>
        <div class="mt-auto space-y-3">
            <a href="{{ route('quote.show') }}" class="btn-dark w-full">Request a quote{{ $quoteCount ? " ($quoteCount)" : '' }}</a>
            @if ($phones)
                <p class="text-center text-sm text-ink/70">Call / WhatsApp<br><x-phone-numbers separator="<br>" link-class="font-semibold text-forest" /></p>
            @endif
        </div>
    </nav>

    @if (session('status') && ! session('quote_sent') && ! session('contact_sent'))
        <div class="container-page pt-4" role="status">
            <p class="rounded-xl bg-sage px-4 py-3 text-sm font-medium text-forest">{{ session('status') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="container-page pt-4" role="alert">
            <p class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <main id="main">{{ $slot }}</main>

    {{-- Footer --}}
    <footer class="bg-deep text-white/80">
        <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr] lg:py-16">
            <div>
                <x-logo dark />
                <p class="mt-5 max-w-xs text-[15px] leading-relaxed">{{ setting('hero_pill', 'Quality Ornamental Plants, Grafted Fruit Trees and Horticultural Seedlings') }}</p>
                @if ($closing = setting('commitment_closing'))
                    <p class="mt-3 max-w-xs text-sm italic text-moss">{{ \Illuminate\Support\Str::after($closing, '– ') }}</p>
                @endif
                @if ($till = setting('till_number'))
                    <p class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm"><x-mpesa-badge /> <span><x-mpesa-payto :till="$till" /></span></p>
                @endif
            </div>
            <div>
                <h2 class="font-sans text-xs font-bold uppercase tracking-[0.14em] text-white">Shop</h2>
                <ul class="mt-4 space-y-2.5 text-[15px]">
                    @foreach (\App\Models\Category::orderBy('sort_order')->get() as $category)
                        <li><a href="{{ route('plants.index', ['category' => $category->slug]) }}" class="hover:text-white">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="font-sans text-xs font-bold uppercase tracking-[0.14em] text-white">Help</h2>
                <ul class="mt-4 space-y-2.5 text-[15px]">
                    <li><a href="{{ route('contact') }}#delivery" class="hover:text-white">Delivery &amp; collection</a></li>
                    <li><a href="{{ route('quote.show') }}" class="hover:text-white">Bulk orders &amp; quotes</a></li>
                    <li><a href="{{ route('posts.index') }}" class="hover:text-white">Plant care guides</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Track my order</a></li>
                </ul>
            </div>
            <div>
                <h2 class="font-sans text-xs font-bold uppercase tracking-[0.14em] text-white">Visit</h2>
                <ul class="mt-4 space-y-2.5 text-[15px]">
                    <li>{{ setting('address') }}</li>
                    <li>{{ setting('hours') }}</li>
                    @foreach ($phones as $p)
                        <li>@if ($p['tel'])<a href="tel:{{ $p['tel'] }}" class="hover:text-white">{{ $p['number'] }}</a>@else{{ $p['number'] }}@endif{{ $p['label'] ? ' ('.$p['label'].')' : '' }}</li>
                    @endforeach
                    <li>{{ setting('email') }}</li>
                    @php $socials = array_filter(['Facebook' => setting('social_facebook'), 'Instagram' => setting('social_instagram'), 'TikTok' => setting('social_tiktok')]); @endphp
                    @if ($socials)
                        <li>@foreach ($socials as $label => $url)<a href="{{ $url }}" class="hover:text-white" rel="noopener" target="_blank">{{ $label }}</a>@if (! $loop->last) · @endif @endforeach</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="container-page flex flex-col gap-2 border-t border-white/10 py-6 text-sm text-white/60 sm:flex-row sm:justify-between">
            <p>© {{ date('Y') }} Wisdom Nursery, a Wisdom Capital company</p>
            <p>Wisdom Chicken · Wisdom Feeds · Uki Honey</p>
        </div>
    </footer>

    {{-- Floating WhatsApp --}}
    @if ($whatsapp && $floatingWhatsapp)
        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"
           class="fixed bottom-5 right-5 z-30 grid h-14 w-14 place-items-center rounded-full bg-mpesa text-white shadow-lg transition hover:scale-105 sm:bottom-8 sm:right-8 sm:h-16 sm:w-16">
            <x-glyph name="chat" class="h-7 w-7" />
        </a>
    @endif
    @stack('scripts')
</body>
</html>
