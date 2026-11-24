<x-layouts.site title="Checkout">
    <div class="container-page py-10 lg:py-14"
         x-data="{ fulfilment: @js(old('fulfilment', 'collect')), area: @js((string) old('delivery_area_id', '')), fees: @js($areas->pluck('fee', 'id')), subtotal: {{ $subtotal }},
                   phone: @js(old('phone', '')), mpesa: @js(old('mpesa_phone', '')), mpesaTouched: {{ old('mpesa_phone') ? 'true' : 'false' }},
                   get fee() { return this.fulfilment === 'delivery' ? (this.fees[this.area] ?? 0) : 0 },
                   get total() { return this.subtotal + this.fee },
                   ksh(n) { return 'KSh ' + n.toLocaleString('en-KE') } }">
        <x-checkout-steps :current="2" />
        <h1 class="mt-6 text-5xl lg:text-6xl">Checkout</h1>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[1fr_440px]">
            @csrf
            <x-honeypot />
            <div class="space-y-6">
                <section class="card p-6 sm:p-8">
                    <h2 class="text-2xl sm:text-3xl">Your details</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <x-field name="name" label="Full name" required autocomplete="name" placeholder="Your name" />
                        <x-field name="phone" label="Phone" type="tel" required autocomplete="tel" placeholder="07XX XXX XXX" x-model="phone" @input="if (! mpesaTouched) mpesa = phone" />
                    </div>

                    <fieldset class="mt-6">
                        <legend class="label">How would you like to get your plants?</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @php $card = 'block cursor-pointer rounded-2xl border-2 p-4 transition has-[:checked]:border-leaf has-[:checked]:bg-mist has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-leaf border-ink/10 bg-white hover:border-leaf/50'; @endphp
                            <label class="{{ $card }}">
                                <input type="radio" name="fulfilment" value="collect" class="sr-only" x-model="fulfilment">
                                <span class="block font-semibold">Collect at the nursery</span>
                                <span class="mt-1 block text-sm text-ink/60">Free · {{ setting('collection_note') }}</span>
                            </label>
                            @if ($areas->isNotEmpty())
                                <label class="{{ $card }}">
                                    <input type="radio" name="fulfilment" value="delivery" class="sr-only" x-model="fulfilment">
                                    <span class="block font-semibold">Deliver to me</span>
                                    <span class="mt-1 block text-sm text-ink/60">From {{ ksh($areas->min('fee')) }} · {{ $areas->pluck('name')->take(3)->implode(', ') }}{{ $areas->count() > 3 ? '…' : '' }}</span>
                                </label>
                            @else
                                <div class="rounded-2xl border-2 border-dashed border-ink/10 p-4 text-sm text-ink/60">
                                    <span class="block font-semibold text-ink/70">Deliver to me</span>
                                    Ask us on WhatsApp about delivery to your area.
                                </div>
                            @endif
                        </div>
                        @error('fulfilment')<p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>@enderror
                    </fieldset>

                    @if ($areas->isNotEmpty())
                        <div class="mt-5 grid gap-4 sm:grid-cols-2" x-cloak x-show="fulfilment === 'delivery'">
                            <div>
                                <label for="f-delivery_area_id" class="label">Delivery area</label>
                                <select id="f-delivery_area_id" name="delivery_area_id" x-model="area" @class(['input', 'border-red-600' => $errors->has('delivery_area_id')])>
                                    <option value="">Choose…</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->id }}">{{ $a->name }} · {{ ksh($a->fee) }}{{ $a->eta ? ' · '.$a->eta : '' }}</option>
                                    @endforeach
                                </select>
                                @error('delivery_area_id')<p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <x-field name="delivery_address" label="Where should we deliver?" placeholder="Estate, road, landmark" autocomplete="street-address" />
                        </div>
                    @endif
                </section>

                <section class="rounded-card border-2 border-mpesa bg-white p-6 sm:p-8">
                    <h2 class="flex items-center gap-3 text-2xl sm:text-3xl"><x-mpesa-badge class="px-3 py-2 text-sm" /> Pay with M-Pesa</h2>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start">
                        <x-field name="mpesa_phone" label="M-Pesa phone number" type="tel" required placeholder="07XX XXX XXX" class="flex-1" x-model="mpesa" @input="mpesaTouched = true" />
                        <button class="btn-mpesa text-base sm:mt-7 sm:min-w-44" x-text="'Pay ' + ksh(total)">Pay {{ ksh($subtotal) }}</button>
                    </div>
                    <p class="mt-4 text-sm text-ink/60">You'll get an M-Pesa prompt on this phone. Enter your PIN to pay. No card needed.</p>
                </section>
            </div>

            <x-order-summary :lines="$lines" :total="$subtotal" class="self-start lg:sticky lg:top-32">
                <p class="mt-2 text-right text-sm text-ink/60" x-cloak x-show="fee > 0">Delivery: <span x-text="ksh(fee)"></span> · Total <span class="font-semibold text-forest" x-text="ksh(total)"></span></p>
                <p class="mt-6 rounded-xl bg-mist p-4 text-sm text-ink/70">Buying 100 or more? <a href="{{ route('quote.show') }}" class="font-semibold text-forest">Request a bulk quote</a> for better prices.</p>
            </x-order-summary>
        </form>
    </div>
</x-layouts.site>
