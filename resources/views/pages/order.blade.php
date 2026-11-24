@php
    $paid = $order->isPaid();
    $pendingCode = $order->latestPayment?->status === \App\Enums\PaymentStatus::PendingVerification;
@endphp
<x-layouts.site :title="'Order '.$order->number">
    <div class="container-page py-10 lg:py-14">
        <x-checkout-steps :current="$paid ? 4 : 3" />
        <h1 class="mt-6 text-5xl lg:text-6xl">{{ $paid ? 'Payment received' : 'Pay with M-Pesa' }}</h1>
        <p class="mt-3 text-ink/70">Order <strong class="font-semibold text-ink">{{ $order->number }}</strong></p>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_440px]">
            <section class="rounded-card border-2 border-mpesa bg-white p-6 sm:p-8"
                     x-data="{ paid: {{ $paid ? 'true' : 'false' }}, receipt: @js($order->latestPayment?->mpesa_receipt), tries: 0,
                               init() { if (! this.paid) this.poll() },
                               poll() {
                                   if (this.paid || this.tries++ > 60) return;
                                   fetch(@js(route('orders.status', $order)), { headers: { Accept: 'application/json' } })
                                       .then(r => r.json()).then(d => { if (d.paid) { this.paid = true; this.receipt = d.receipt; window.location.reload() } })
                                       .finally(() => setTimeout(() => this.poll(), 5000))
                               } }">
                @if ($paid)
                    <div class="flex items-start gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-mpesa text-white"><x-glyph name="check" class="h-6 w-6" /></span>
                        <div>
                            <h2 class="text-2xl">Thank you, {{ Str::before($order->customer_name, ' ') }}!</h2>
                            <p class="mt-2 text-ink/70">We've received {{ ksh($order->total) }} for order {{ $order->number }}@if ($order->latestPayment?->mpesa_receipt) (M-Pesa {{ $order->latestPayment->mpesa_receipt }})@endif. You'll get an SMS when it's {{ $order->fulfilment->value === 'delivery' ? 'on its way' : 'ready to collect' }}.</p>
                        </div>
                    </div>
                @elseif ($pendingCode)
                    <h2 class="text-2xl">We're checking your payment</h2>
                    <p class="mt-2 text-ink/70">Thank you. We'll confirm M-Pesa code <strong>{{ $order->latestPayment->mpesa_receipt }}</strong> shortly and send you an SMS.</p>
                @else
                    <div class="flex flex-col gap-6 rounded-2xl bg-mist p-5 sm:flex-row sm:items-center sm:p-6">
                        <div class="mx-auto w-36 shrink-0 rounded-[22px] border-[6px] border-forest bg-white p-3 text-[10px] leading-tight sm:mx-0" aria-hidden="true">
                            <p class="font-bold">M-PESA</p>
                            <p class="mt-1">Pay {{ ksh($order->total) }} to WISDOM NURSERY?</p>
                            <p class="mt-2">Enter M-PESA PIN</p>
                            <div class="mt-1 h-4 rounded border border-ink/20"></div>
                            <div class="mt-2 flex gap-1"><span class="flex-1 rounded bg-ink/10 py-0.5 text-center">Cancel</span><span class="flex-1 rounded bg-mpesa py-0.5 text-center text-white">OK</span></div>
                        </div>
                        <div role="status" aria-live="polite">
                            <h2 class="text-2xl">Check your phone</h2>
                            <p class="mt-2 text-ink/70">We've sent an M-Pesa prompt to {{ $mpesaPhone }}. Enter your PIN to pay. This page updates by itself once the payment goes through, and you'll get an SMS confirmation.</p>
                            <p class="mt-3 text-sm text-ink/60">No prompt?@if ($till) Pay to Till {{ $till }} and enter the M-Pesa code below.@else Enter the M-Pesa code below once you've paid.@endif</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('orders.mpesa-code', $order) }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start">
                        @csrf
                        <x-field name="mpesa_code" label="M-Pesa code" placeholder="e.g. SKL4H7XXXX" class="flex-1" autocomplete="off" maxlength="10" />
                        <button class="btn-dark sm:mt-7">Send code</button>
                    </form>
                @endif
            </section>

            <x-order-summary :lines="$order->items->map(fn ($i) => (object) ['label' => $i->plant_name, 'qty' => $i->quantity, 'lineTotal' => $i->line_total, 'plant' => $i->plant])"
                :fulfilment-label="$order->fulfilment->value === 'delivery' ? 'Delivery · '.$order->delivery_area_name : 'Collection'"
                :fee="$order->delivery_fee" :total="$order->total" class="self-start" />
        </div>
    </div>
</x-layouts.site>
