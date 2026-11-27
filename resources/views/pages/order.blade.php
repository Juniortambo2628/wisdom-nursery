@php
    use App\Enums\PaymentStatus;
    $paid = $order->isPaid();
    $status = $payment?->status;
    $checking = $status === PaymentStatus::PendingVerification;
    $waiting = ! $paid && $payment?->method === 'stk' && $status === PaymentStatus::Pending;
    $failed = ! $paid && in_array($status, [PaymentStatus::Failed, PaymentStatus::Cancelled], true);
@endphp
<x-layouts.site :title="'Order '.$order->number" :floating-whatsapp="false">
    <div class="container-page py-10 lg:py-14">
        <x-checkout-steps :current="$paid ? 4 : 3" />
        <h1 class="mt-6 text-5xl lg:text-6xl">@if ($paid) Payment received @else Pay with <span class="whitespace-nowrap">M-Pesa</span> @endif</h1>
        <p class="mt-3 text-ink/70">Order <strong class="font-semibold text-ink">{{ $order->number }}</strong></p>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_440px]">
            <section class="rounded-card border-2 border-mpesa bg-white p-6 sm:p-8"
                     x-data="{ tries: 0,
                               init() { if ({{ $waiting ? 'true' : 'false' }}) setTimeout(() => this.poll(), 4000) },
                               poll() {
                                   if (this.tries++ > 60) return;
                                   fetch(@js(route('orders.status', $order)), { headers: { Accept: 'application/json' } })
                                       .then(r => r.json())
                                       .then(d => { if (d.paid || d.payment_status !== 'pending') { window.location.reload() } else { setTimeout(() => this.poll(), 4000) } })
                                       .catch(() => setTimeout(() => this.poll(), 8000))
                               } }">
                @if ($paid)
                    <div class="flex items-start gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-mpesa text-white"><x-glyph name="check" class="h-6 w-6" /></span>
                        <div>
                            <h2 class="text-2xl">Thank you, {{ Str::before($order->customer_name, ' ') }}!</h2>
                            <p class="mt-2 text-ink/70">We've received {{ ksh($order->total) }} for order {{ $order->number }}@if ($order->latestPayment?->mpesa_receipt) (M-Pesa {{ $order->latestPayment->mpesa_receipt }})@endif. You'll get an SMS when it's {{ $order->fulfilment->value === 'delivery' ? 'on its way' : 'ready to collect' }}.</p>
                        </div>
                    </div>
                @elseif ($checking)
                    <h2 class="text-2xl">We're checking your payment</h2>
                    <p class="mt-2 text-ink/70">Thank you. We'll confirm @if ($payment->mpesa_receipt)M-Pesa code <strong>{{ $payment->mpesa_receipt }}</strong>@else your payment @endif shortly and send you an SMS.</p>
                @else
                    @if ($waiting)
                        <div class="flex flex-col gap-6 rounded-2xl bg-mist p-5 sm:flex-row sm:items-center sm:p-6">
                            <div class="mx-auto w-36 shrink-0 rounded-[22px] border-[6px] border-forest bg-white p-3 text-[10px] leading-tight sm:mx-0" aria-hidden="true">
                                <p class="font-bold">M-PESA</p>
                                <p class="mt-1">Pay {{ ksh($order->total) }} to WISDOM PROPAGATION NURSERY?</p>
                                <p class="mt-2">Enter M-PESA PIN</p>
                                <div class="mt-1 h-4 rounded border border-ink/20"></div>
                                <div class="mt-2 flex gap-1"><span class="flex-1 rounded bg-ink/10 py-0.5 text-center">Cancel</span><span class="flex-1 rounded bg-mpesa py-0.5 text-center text-white">OK</span></div>
                            </div>
                            <div role="status" aria-live="polite">
                                <h2 class="text-2xl">Check your phone</h2>
                                <p class="mt-2 text-ink/70">We've sent an M-Pesa prompt to {{ $mpesaPhone }}. Enter your PIN to pay. This page updates by itself once the payment goes through, and you'll get an SMS confirmation.</p>
                                <p class="mt-3 text-sm text-ink/60">No prompt?@if ($till) Pay to <x-mpesa-payto :till="$till" /> and enter the M-Pesa code below.@else Enter the M-Pesa code below once you've paid.@endif</p>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl bg-mist p-5 sm:p-6" role="status">
                            @if ($failed)
                                <h2 class="text-2xl">The payment didn't go through</h2>
                                <p class="mt-2 text-ink/70">{{ $status === PaymentStatus::Cancelled ? 'The M-Pesa prompt was cancelled.' : 'M-Pesa did not complete the payment.' }} You can send the prompt again, or pay another way below.</p>
                            @else
                                <h2 class="text-2xl">Pay {{ ksh($order->total) }} with <span class="whitespace-nowrap">M-Pesa</span></h2>
                                <p class="mt-2 text-ink/70">@if ($till)Pay to <x-mpesa-payto :till="$till" />, then enter the M-Pesa code below.@else Pay with M-Pesa, then enter the M-Pesa code below, or order on WhatsApp.@endif</p>
                            @endif
                        </div>
                        @if ($canRetry)
                            <form method="POST" action="{{ route('orders.retry', $order) }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start">
                                @csrf
                                <x-field name="phone" label="M-Pesa phone number" type="tel" :value="$mpesaPhone" required class="flex-1" />
                                <button class="btn-mpesa sm:mt-7">Send prompt again</button>
                            </form>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('orders.mpesa-code', $order) }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start">
                        @csrf
                        <x-field name="mpesa_code" label="Already paid? Enter the M-Pesa code" placeholder="e.g. SKL4H7XXXX" class="flex-1" autocomplete="off" maxlength="10" />
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
