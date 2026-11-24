<x-layouts.site title="Your cart">
    <div class="container-page py-10 lg:py-14">
        <x-checkout-steps :current="1" />
        <h1 class="mt-6 text-5xl lg:text-6xl">Your cart</h1>

        @if ($lines->isEmpty())
            <div class="card mt-8 max-w-xl p-8">
                <p class="font-display text-2xl">Your cart is empty.</p>
                <p class="mt-2 text-ink/70">Pick plants from the list and pay with M-Pesa, or send us a list for a bulk quote.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('plants.index') }}" class="btn-primary">Shop plants</a>
                    <a href="{{ route('quote.show') }}" class="btn-outline">Request a quote</a>
                </div>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_400px]">
                <section class="card divide-y divide-ink/10" aria-label="Items">
                    @foreach ($lines as $line)
                        <div class="flex flex-wrap items-center gap-4 p-5 sm:flex-nowrap sm:p-6">
                            <x-photo :src="$line->plant->primaryImage?->url()" label="Img" class="h-16 w-16 shrink-0 rounded-xl text-[9px]" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('plants.show', $line->plant) }}" class="font-semibold hover:text-leaf">{{ $line->label }}</a>
                                <p class="text-sm text-ink/60">{{ ksh($line->unitPrice) }} each
                                    @if ($line->plant->isComingSoon()) · <span class="text-clay">{{ $line->plant->availabilityLabel() }}</span>@endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2" x-data>
                                @csrf @method('PATCH')
                                <input type="hidden" name="key" value="{{ $line->key }}">
                                <label for="qty-{{ $loop->index }}" class="sr-only">Quantity of {{ $line->label }}</label>
                                <input id="qty-{{ $loop->index }}" name="qty" type="number" min="0" max="{{ \App\Support\Cart::MAX_QTY }}" value="{{ $line->qty }}" class="input w-20 py-2 text-center" @change="$el.form.submit()">
                                <button name="qty" value="0" class="grid h-10 w-10 place-items-center rounded-full text-ink/50 hover:bg-mist hover:text-red-700" aria-label="Remove {{ $line->label }}"><x-glyph name="trash" class="h-4 w-4" /></button>
                            </form>
                            <span class="w-24 text-right font-semibold">{{ ksh($line->lineTotal) }}</span>
                        </div>
                    @endforeach
                </section>
                <section class="card self-start p-6 sm:p-8">
                    <div class="flex justify-between text-xl font-semibold"><span>Subtotal</span><span class="text-forest">{{ ksh($subtotal) }}</span></div>
                    <p class="mt-2 text-sm text-ink/60">Collection is free. Delivery fees are shown at the next step.</p>
                    <a href="{{ route('checkout.show') }}" class="btn-primary mt-6 w-full">Checkout · <x-mpesa-badge /></a>
                    @if ($whatsapp)
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-3 block text-center text-sm text-ink/60 hover:text-leaf">or send this cart on WhatsApp</a>
                    @endif
                    <p class="mt-6 rounded-xl bg-mist p-4 text-sm text-ink/70">Buying 100 or more? <a href="{{ route('quote.show') }}" class="font-semibold text-forest">Request a bulk quote</a> for better prices.</p>
                </section>
            </div>
        @endif
    </div>
</x-layouts.site>
