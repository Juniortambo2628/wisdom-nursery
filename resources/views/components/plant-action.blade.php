{{-- The action for a plant in the shop list: In cart / Added to quote / Add to cart / Notify me. --}}
@props(['plant', 'inCart' => false, 'inQuote' => false])
<div class="flex flex-col items-stretch gap-1.5 sm:items-end" x-data="{ notify: false }">
    @if ($inQuote)
        <a href="{{ route('quote.show') }}" class="btn-sm btn bg-sage text-forest hover:bg-mist">Added to quote</a>
    @elseif ($inCart)
        <a href="{{ route('cart.show') }}" class="btn-sm btn bg-mist text-leaf hover:bg-sage"><x-glyph name="check" class="h-4 w-4" /> In cart</a>
    @elseif ($plant->isBuyable())
        <form method="POST" action="{{ route('cart.add') }}">
            @csrf
            <input type="hidden" name="plant_id" value="{{ $plant->id }}">
            <button class="btn-primary btn-sm w-full">Add to cart</button>
        </form>
    @else
        <button type="button" class="btn-outline btn-sm" @click="notify = ! notify" :aria-expanded="notify">Notify me</button>
        <form x-cloak x-show="notify" method="POST" action="{{ route('plants.notify', $plant) }}" class="flex gap-2">
            @csrf
            <label class="sr-only" for="notify-{{ $plant->id }}">Your phone number</label>
            <input id="notify-{{ $plant->id }}" name="phone" type="tel" required placeholder="07XX XXX XXX" class="input min-w-0 flex-1 py-2 text-xs sm:w-36 sm:flex-none">
            <button class="btn-dark btn-sm">Send</button>
        </form>
    @endif
    @if (! $inQuote && $plant->bulk_min_qty)
        <form method="POST" action="{{ route('quote.items') }}">
            @csrf
            <input type="hidden" name="plant_id" value="{{ $plant->id }}">
            <input type="hidden" name="qty" value="{{ $plant->bulk_min_qty }}">
            <button class="-my-2 min-h-10 py-2 text-xs font-semibold text-leaf underline-offset-2 hover:underline">or add {{ number_format($plant->bulk_min_qty) }}+ to quote</button>
        </form>
    @endif
</div>
