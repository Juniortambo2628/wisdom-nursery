{{-- Our plants: sticky cart + quote list. Expects $cartLines, $cartWhatsapp, $quoteLines. --}}
<aside class="space-y-5 self-start lg:sticky lg:top-32 lg:max-h-[calc(100vh-9rem)] lg:overflow-y-auto" aria-label="Your cart and quote list">
    <section class="card overflow-hidden border-t-4 border-leaf p-6">
        <div class="flex items-baseline justify-between">
            <h2 class="text-2xl">Your cart</h2>
            <span class="text-sm text-ink/70">{{ $count = $cartLines->sum('qty') }} {{ Str::plural('item', $count) }}</span>
        </div>
        @if ($cartLines->isEmpty())
            <p class="mt-4 text-sm text-ink/70">Your cart is empty. Add plants from the list to pay with M-Pesa.</p>
        @else
            <ul class="mt-4 divide-y divide-ink/10 text-[15px]">
                @foreach ($cartLines as $line)
                    <li class="flex justify-between gap-3 py-3">
                        <span>{{ $line->label }} <span class="text-ink/50">× {{ $line->qty }}</span></span>
                        <span class="shrink-0 font-semibold">{{ ksh($line->lineTotal) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="flex justify-between border-t border-ink/10 pt-4 text-lg font-semibold">
                <span>Total</span><span>{{ ksh($cartLines->sum('lineTotal')) }}</span>
            </div>
            <a href="{{ route('checkout.show') }}" class="btn-primary mt-5 w-full">Checkout · <x-mpesa-badge /></a>
            @if ($cartWhatsapp)
                <a href="{{ $cartWhatsapp }}" target="_blank" rel="noopener" class="mt-1 block py-2.5 text-center text-sm text-ink/70 hover:text-leaf">or send this cart on WhatsApp</a>
            @endif
        @endif
    </section>

    <section class="rounded-card bg-sage p-6 ring-1 ring-ink/5">
        <h2 class="text-2xl">Quote list <span class="font-sans text-sm text-ink/70">for bulk orders</span></h2>
        @if ($quoteLines->isEmpty())
            <p class="mt-3 text-sm text-ink/70">Buying in bulk? Add plants to a quote list and we'll reply with prices and a ready date.</p>
        @else
            <ul class="mt-4 space-y-3">
                @foreach ($quoteLines as $line)
                    <li class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[15px]">{{ $line->plant->name }}</p>
                            <p class="text-sm text-ink/50">{{ $line->plant->sold_as->label() }}</p>
                        </div>
                        <form method="POST" action="{{ route('quote.items') }}" x-data>
                            @csrf
                            <input type="hidden" name="plant_id" value="{{ $line->plant->id }}">
                            <label class="sr-only" for="q-{{ $line->plant->id }}">Quantity of {{ $line->plant->name }}</label>
                            <input id="q-{{ $line->plant->id }}" name="qty" type="number" min="0" value="{{ $line->qty }}" class="input w-28 py-2.5 text-right" @change="$el.form.submit()">
                        </form>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('quote.show') }}" class="btn-dark mt-5 w-full">Request a quote</a>
        @endif
    </section>
</aside>
