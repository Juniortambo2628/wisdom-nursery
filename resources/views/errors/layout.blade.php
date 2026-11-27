{{-- Error pages: no database or settings calls, so they still render when the database is the problem. --}}
@php $whatsapp = \App\Support\WhatsApp::link('Hello Wisdom Propagation Nursery'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · Wisdom Propagation Nursery</title>
    @include('partials.head-icons')
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-mist">
    <header class="container-page flex h-[72px] items-center lg:h-24">
        <a href="{{ url('/') }}" class="inline-flex min-h-10 items-center" aria-label="Wisdom Propagation Nursery, home">
            <img src="{{ asset('images/logo.png') }}" alt="Wisdom Nursery" width="290" height="320" class="h-20 w-auto">
        </a>
    </header>
    <main class="container-page flex flex-1 items-center py-16">
        <div class="max-w-xl">
            <p class="eyebrow">@yield('code')</p>
            <h1 class="mt-4 text-5xl sm:text-6xl">@yield('heading')</h1>
            <p class="mt-5 text-lg text-ink/70">@yield('message')</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @section('actions')
                    <a href="{{ url('/') }}" class="btn-primary">Back to the home page</a>
                    <a href="{{ url('/plants') }}" class="btn-outline">See our plants</a>
                @show
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-outline">WhatsApp us</a>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
