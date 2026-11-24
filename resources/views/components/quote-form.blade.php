{{-- Bulk quote form: home band, quote page. Uses the session quote list if it has plants. --}}
@props(['withList' => false])
<form method="POST" action="{{ route('quote.store') }}" {{ $attributes->class('card p-6 sm:p-8') }}>
    @csrf
    <x-honeypot />
    @if (session('quote_sent'))
        <p class="mb-5 rounded-xl bg-mist px-4 py-3 text-sm font-medium text-leaf" role="status">{{ session('status') }}</p>
    @endif
    <div class="grid gap-4 sm:grid-cols-2">
        <x-field prefix="q" name="name" label="Name" placeholder="Your name" required autocomplete="name" />
        <x-field prefix="q" name="phone" label="Phone" type="tel" placeholder="07XX XXX XXX" required autocomplete="tel" />
        @unless ($withList)
            <x-field prefix="q" name="plants" label="Plants" placeholder="e.g. Grevillea seedlings" />
            <x-field prefix="q" name="quantity" label="Quantity" type="number" min="1" placeholder="e.g. 2,000" />
        @endunless
        <x-field prefix="q" name="location" label="Deliver to" placeholder="Town / farm" class="sm:col-span-2" />
        @if ($withList)
            <x-field prefix="q" name="organisation" label="Organisation (optional)" placeholder="School, farm, estate…" />
            <x-field prefix="q" name="email" label="Email (optional)" type="email" placeholder="you@example.com" autocomplete="email" />
            <x-field prefix="q" name="notes" label="Anything else?" textarea placeholder="Planting date, sizes, delivery needs" class="sm:col-span-2" />
        @endif
    </div>
    <button class="btn-dark mt-6 w-full">Send quote request</button>
</form>
