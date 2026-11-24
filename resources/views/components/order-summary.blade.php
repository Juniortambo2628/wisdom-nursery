{{-- Order summary card. $lines: objects with label, qty, lineTotal, plant (optional). --}}
@props(['lines', 'fulfilmentLabel' => 'Collection', 'fee' => 0, 'total'])
<section {{ $attributes->class('card p-6 sm:p-8') }}>
    <h2 class="text-2xl sm:text-3xl">Order summary</h2>
    <ul class="mt-6 divide-y divide-ink/10">
        @foreach ($lines as $line)
            <li class="flex items-center gap-4 py-4">
                <x-photo :src="$line->plant?->primaryImage?->url()" label="Img" class="h-14 w-14 shrink-0 rounded-xl text-[9px]" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $line->label }}</p>
                    <p class="text-sm text-ink/60">Qty {{ $line->qty }}
                        @if ($line->plant?->isComingSoon()) · <span class="text-clay">{{ $line->plant->availabilityLabel() }}</span>@endif
                    </p>
                </div>
                <span class="shrink-0 whitespace-nowrap font-semibold">{{ ksh($line->lineTotal) }}</span>
            </li>
        @endforeach
    </ul>
    <div class="space-y-3 border-t border-ink/10 pt-5">
        <div class="flex justify-between text-ink/70"><span>{{ $fulfilmentLabel }}</span><span>{{ $fee ? ksh($fee) : 'Free' }}</span></div>
        <div class="flex justify-between text-xl font-semibold"><span>Total</span><span class="text-forest">{{ ksh($total) }}</span></div>
    </div>
    {{ $slot }}
</section>
