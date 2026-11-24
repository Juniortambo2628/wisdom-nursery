@props(['current' => 1])
@php $steps = [1 => 'Cart', 2 => 'Details', 3 => 'Pay with M-Pesa', 4 => 'Done']; @endphp
<p class="eyebrow text-ink/60 sm:hidden">Step {{ $current }} of 4</p>
<ol class="hidden items-center gap-3 text-sm sm:flex" aria-label="Checkout steps">
    @foreach ($steps as $n => $label)
        <li class="flex items-center gap-3" @if ($n === $current) aria-current="step" @endif>
            @if ($n < $current)
                <span class="flex items-center gap-1.5 font-medium text-leaf"><x-glyph name="check" class="h-4 w-4" /> {{ $label }}</span>
            @elseif ($n === $current)
                <span class="rounded-full bg-forest px-4 py-2 font-semibold text-white">{{ $n }} · {{ $label }}</span>
            @else
                <span class="font-medium text-ink/60">{{ $n }} · {{ $label }}</span>
            @endif
            @unless ($loop->last)<span class="h-px w-8 bg-ink/15" aria-hidden="true"></span>@endunless
        </li>
    @endforeach
</ol>
