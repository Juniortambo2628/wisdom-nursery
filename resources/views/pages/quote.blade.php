<x-layouts.site title="Request a quote" description="Bulk orders for farms, schools, estates and landscapers. Tell us what you need and we'll reply with prices and a ready date.">
    <x-page-hero eyebrow="Farms, schools, estates & landscapers" title="Request a quote" :image="site_image('image_quote_banner') ?? site_image('image_step_4')" :srcset="site_image_srcset('image_quote_banner')"
        intro="Tell us what you need and where. We reply with prices and a ready date, usually the same day. We can also raise seedlings from your own seed." />
    <section class="container-page grid gap-10 py-16 lg:grid-cols-2 lg:py-24">
        <div>
            @if ($lines->isNotEmpty())
                <div class="rounded-card bg-sage p-6">
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
                <h2 class="text-3xl">Bulk and institutional orders</h2>
                <p class="mt-4 text-lg text-ink/80">Tip: you can add plants to a quote list from <a href="{{ route('plants.index') }}" class="font-semibold text-leaf underline">Our plants</a>, or just describe what you need here.</p>
            @endif
        </div>
        <x-quote-form :with-list="$lines->isNotEmpty()" class="self-start" />
    </section>
</x-layouts.site>
