@props(['plant'])
<article class="card flex flex-col overflow-hidden">
    <a href="{{ route('plants.show', $plant) }}" class="block">
        <x-photo :src="$plant->primaryImage?->url()" :srcset="media_srcset($plant->primaryImage?->path)" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" :label="$plant->name.' photo'" class="aspect-[4/3] w-full" />
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p @class([
            'text-[11px] font-bold uppercase tracking-[0.12em]',
            'text-leaf' => $plant->availabilityTone() === 'ready',
            'text-clay' => $plant->availabilityTone() === 'soon',
            'text-ink/50' => $plant->availabilityTone() === 'out',
        ])>{{ $plant->availabilityTone() === 'ready' ? 'In stock' : $plant->availabilityLabel() }}</p>
        <h3 class="mt-2 text-xl">
            <a href="{{ route('plants.show', $plant) }}" class="-my-2 block py-2 hover:text-leaf">{{ $plant->name }}</a>
        </h3>
        <p class="mt-1 text-sm text-ink/70">{{ $plant->category->name }} · {{ $plant->sold_as->label() }}</p>
        <div class="mt-auto flex items-center justify-between pt-5">
            <span class="font-semibold">{{ ksh($plant->price) }}</span>
            @if ($plant->isBuyable())
                <form method="POST" action="{{ route('cart.add') }}">
                    @csrf
                    <input type="hidden" name="plant_id" value="{{ $plant->id }}">
                    <button class="grid h-11 w-11 place-items-center rounded-full bg-leaf text-white transition hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-leaf focus-visible:ring-offset-2"
                            aria-label="Add {{ $plant->name }} to cart">
                        <x-glyph name="plus" class="h-5 w-5" />
                    </button>
                </form>
            @else
                <a href="{{ route('plants.show', $plant) }}" class="btn-outline btn-sm">Notify me</a>
            @endif
        </div>
    </div>
</article>
