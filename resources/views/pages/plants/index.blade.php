<x-layouts.site title="Our plants" active="plants"
    description="See what's growing at Wisdom Propagation Nursery now, with prices and availability. Buy online with M-Pesa, or request a quote for bulk orders.">
    @php
        // A category shows one of its own plants (popular ones first); "All" shows the nursery photo.
        $heroPlant = $activeCategory ? $plants->first(fn ($p) => $p->primaryImage) : null;
    @endphp
    <x-page-hero
        :eyebrow="'Availability · Updated '.($updatedAt ? \Illuminate\Support\Carbon::parse($updatedAt)->format('j M Y') : '[date]')"
        :title="$activeCategory ? $activeCategory->name : 'What’s growing now'"
        :image="$heroPlant ? $heroPlant->primaryImage->url() : site_image('image_shop_banner')"
        :srcset="$heroPlant ? null : site_image_srcset('image_shop_banner')"
        intro="Buy online and pay with M-Pesa, or add large quantities to a quote list and we'll reply with prices and a ready date." />

    <section class="container-page grid gap-8 pb-20 pt-8 lg:grid-cols-[1fr_380px] lg:pb-28 lg:pt-12">
        <div class="min-w-0">
            {{-- Categories, search and the grid/list toggle stay under the header while scrolling. --}}
            <form method="GET" action="{{ route('plants.index') }}" class="sticky top-[72px] z-20 -mx-4 flex flex-col gap-3 border-b border-ink/10 bg-mist px-4 py-3 sm:-mx-8 sm:px-8 lg:top-24 lg:mx-0 lg:flex-row lg:items-center lg:justify-between lg:gap-4 lg:px-0 lg:py-4">
                <nav aria-label="Plant categories" class="scrollbar-none -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
                    @php $pill = 'inline-flex min-h-10 shrink-0 items-center rounded-full border px-4 py-2 text-sm transition'; @endphp
                    <a href="{{ route('plants.index', array_filter(['q' => $search, 'view' => $view === 'list' ? 'list' : null])) }}" @class([$pill, 'border-forest bg-forest text-white' => ! $activeCategory, 'border-ink/15 bg-white hover:border-leaf' => $activeCategory]) @if (! $activeCategory) aria-current="page" @endif>All</a>
                    @foreach ($categories as $category)
                        <a href="{{ route('plants.index', array_filter(['category' => $category->slug, 'q' => $search, 'view' => $view === 'list' ? 'list' : null])) }}"
                           @class([$pill, 'border-forest bg-forest text-white' => $activeCategory?->is($category), 'border-ink/15 bg-white hover:border-leaf' => ! $activeCategory?->is($category)])
                           @if ($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                    @endforeach
                </nav>
                @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
                @if ($view === 'list')<input type="hidden" name="view" value="list">@endif
                <div class="flex items-center gap-2">
                <div class="relative flex-1 lg:w-64 lg:flex-none">
                    <label for="plant-search" class="sr-only">Search plants</label>
                    <input id="plant-search" type="search" name="q" value="{{ $search }}" placeholder="Search plants…" class="input rounded-full pl-10">
                    <x-glyph name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/40" />
                </div>
                {{-- Grid / list toggle; the choice stays on category and search links. --}}
                @php
                    $viewUrl = fn ($v) => route('plants.index', array_filter(['category' => $activeCategory?->slug, 'q' => $search, 'view' => $v === 'list' ? 'list' : null]));
                    $toggle = 'grid h-11 w-11 place-items-center rounded-full transition';
                @endphp
                <div class="flex shrink-0 rounded-full border border-ink/15 bg-white p-0.5" role="group" aria-label="Show plants as">
                    <a href="{{ $viewUrl('grid') }}" @class([$toggle, 'bg-forest text-white' => $view === 'grid', 'text-ink/70 hover:text-leaf' => $view !== 'grid']) @if ($view === 'grid') aria-current="true" @endif aria-label="Grid view" title="Grid view"><x-glyph name="grid" class="h-5 w-5" /></a>
                    <a href="{{ $viewUrl('list') }}" @class([$toggle, 'bg-forest text-white' => $view === 'list', 'text-ink/70 hover:text-leaf' => $view !== 'list']) @if ($view === 'list') aria-current="true" @endif aria-label="List view" title="List view"><x-glyph name="list" class="h-5 w-5" /></a>
                </div>
                </div>
            </form>

            @if ($plants->isEmpty())
                <div class="card mt-6 p-10 text-center">
                    <p class="font-display text-2xl">No plants found{{ $search ? " for “{$search}”" : '' }}.</p>
                    <p class="mt-2 text-ink/70">We grow to order. <a href="{{ route('quote.show') }}" class="font-semibold text-leaf underline">Tell us what you need</a>.</p>
                </div>
            @elseif ($view === 'grid')
                <ul class="mt-6 grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-3" data-reveal data-reveal-stagger>
                    @foreach ($plants as $plant)
                        <li class="card flex min-w-0 flex-col overflow-hidden">
                            <a href="{{ route('plants.show', $plant) }}" class="block">
                                <x-photo :src="$plant->primaryImage?->url()" :srcset="media_srcset($plant->primaryImage?->path)" sizes="(min-width: 1280px) 20vw, (min-width: 1024px) 30vw, 50vw" :label="$plant->primaryImage ? '' : 'Photo soon'" class="aspect-[4/3] w-full text-[10px]" />
                            </a>
                            <div class="flex flex-1 flex-col p-3 sm:p-5">
                                <x-availability :plant="$plant" />
                                <h2 class="mt-1.5 font-sans text-base font-semibold leading-snug sm:text-lg">
                                    <a href="{{ route('plants.show', $plant) }}" class="hover:text-leaf">{{ $plant->name }}</a>
                                </h2>
                                @if ($plant->botanical_name)<p class="truncate text-sm italic text-ink/50">{{ $plant->botanical_name }}</p>@endif
                                <p class="mt-1 text-sm text-ink/70">{{ $plant->category->name }} · {{ $plant->sold_as->label() }}</p>
                                <p class="mt-3 font-semibold">{{ ksh($plant->price) }}</p>
                                <div class="mt-auto pt-4 [&>div]:items-stretch">
                                    <x-plant-action :plant="$plant" :in-cart="$cart->has($plant)" :in-quote="$quoteList->has($plant)" />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="card mt-6 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="hidden border-b border-ink/10 text-xs font-semibold uppercase tracking-[0.12em] text-ink/70 md:table-header-group">
                            <tr>
                                <th scope="col" class="py-4 pl-5 pr-3" colspan="2">Plant</th>
                                <th scope="col" class="px-3 py-4">Type</th>
                                <th scope="col" class="px-3 py-4">Sold as</th>
                                <th scope="col" class="px-3 py-4">Price</th>
                                <th scope="col" class="px-3 py-4">Availability</th>
                                <th scope="col" class="py-4 pl-3 pr-5"><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/10">
                            @foreach ($plants as $plant)
                                <tr class="grid grid-cols-[80px_1fr] gap-x-4 gap-y-2 p-4 md:table-row md:p-0">
                                    <td class="row-span-3 md:w-32 md:py-4 md:pl-5 md:pr-4">
                                        <a href="{{ route('plants.show', $plant) }}" class="block">
                                            <x-photo :src="$plant->primaryImage?->url()" :srcset="media_srcset($plant->primaryImage?->path)" sizes="96px" :label="$plant->primaryImage ? '' : 'Photo soon'" class="h-20 w-20 rounded-xl text-[10px] md:h-24 md:w-24" />
                                        </a>
                                    </td>
                                    <td class="md:py-4 md:pr-3">
                                        <a href="{{ route('plants.show', $plant) }}" class="-my-2 inline-block py-2.5 font-semibold hover:text-leaf">{{ $plant->name }}</a>
                                        @if ($plant->botanical_name)<p class="text-sm italic text-ink/50">{{ $plant->botanical_name }}</p>@endif
                                        <p class="text-sm text-ink/70 md:hidden">{{ $plant->category->name }} · {{ $plant->sold_as->label() }}</p>
                                    </td>
                                    <td class="hidden px-3 py-4 text-ink/80 md:table-cell">{{ $plant->category->name }}</td>
                                    <td class="hidden px-3 py-4 text-ink/80 md:table-cell">{{ $plant->sold_as->label() }}</td>
                                    <td class="flex items-center justify-between gap-3 md:table-cell md:px-3 md:py-4">
                                        <span class="whitespace-nowrap font-semibold">{{ ksh($plant->price) }}</span>
                                        <x-availability :plant="$plant" class="md:hidden" />
                                    </td>
                                    <td class="hidden px-3 py-4 md:table-cell"><x-availability :plant="$plant" /></td>
                                    <td class="md:py-4 md:pl-3 md:pr-5">
                                        <x-plant-action :plant="$plant" :in-cart="$cart->has($plant)" :in-quote="$quoteList->has($plant)" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <p class="mt-5 text-ink/80">Don't see it? We grow to order. <a href="{{ route('quote.show') }}" class="font-semibold text-forest underline underline-offset-2">Tell us what you need →</a></p>
        </div>

        @include('partials.shop-sidebar')
    </section>
</x-layouts.site>
