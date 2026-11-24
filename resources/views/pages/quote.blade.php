<x-layouts.site title="Request a quote" description="Bulk orders for farms, schools, estates and landscapers. Tell us what you need and we'll reply with prices and a ready date.">
    <section class="container-page grid gap-10 py-12 lg:grid-cols-2 lg:py-20">
        <div>
            <p class="eyebrow">Farms, schools, estates &amp; landscapers</p>
            <h1 class="mt-4 text-5xl sm:text-6xl">Request a quote</h1>
            <p class="mt-5 max-w-lg text-lg text-ink/70">Tell us what you need and where. We reply with prices and a ready date, usually the same day. We can also raise seedlings from your own seed.</p>
            @if ($lines->isNotEmpty())
                <div class="mt-8 rounded-card bg-mist p-6">
                    <h2 class="text-2xl">Your quote list</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($lines as $line)
                            <li class="flex items-center justify-between gap-3">
                                <span>{{ $line->plant->name }} <span class="text-sm text-ink/50">· {{ $line->plant->sold_as->label() }}</span></span>
                                <form method="POST" action="{{ route('quote.items') }}" class="flex items-center gap-2" x-data>
                                    @csrf
                                    <input type="hidden" name="plant_id" value="{{ $line->plant->id }}">
                                    <label for="ql-{{ $line->plant->id }}" class="sr-only">Quantity of {{ $line->plant->name }}</label>
                                    <input id="ql-{{ $line->plant->id }}" name="qty" type="number" min="0" value="{{ $line->qty }}" class="input w-28 py-2 text-right" @change="$el.form.submit()">
                                    <button name="qty" value="0" class="grid h-10 w-10 place-items-center rounded-full text-ink/50 hover:bg-white hover:text-red-700" aria-label="Remove {{ $line->plant->name }}"><x-glyph name="trash" class="h-4 w-4" /></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="mt-8 text-ink/70">Tip: you can add plants to a quote list from <a href="{{ route('plants.index') }}" class="font-semibold text-leaf underline">Our plants</a>, or just describe what you need here.</p>
            @endif
        </div>
        <x-quote-form :with-list="$lines->isNotEmpty()" class="self-start" />
    </section>
</x-layouts.site>
