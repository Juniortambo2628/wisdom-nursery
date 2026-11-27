<x-layouts.site title="Our services" active="services"
    description="Seed propagation, cuttings propagation, agronomy support, crop information, and ornamentals and garden plants from Wisdom Propagation Nursery in Kitui.">
    <x-page-hero eyebrow="Our services" title="From seed to a thriving planting."
        :image="site_image('image_services_banner') ?? site_image('image_step_3')" :srcset="site_image_srcset('image_services_banner')"
        intro="Quality planting materials, clear stock readiness and practical growing support, grown in Kitui for farmers, institutions and landscaping projects." />

    <section class="container-page py-16 lg:py-24">
        <x-section-heading eyebrow="What we do" title="Five ways we help you grow">
            <x-slot:action><p class="hidden text-ink/70 lg:block">Point at or tap a service to open it.</p></x-slot:action>
        </x-section-heading>

        {{--
            Expanding cards. Computers: one row, the open card is wide and the others are narrow strips
            with a sideways title; hovering, focusing or tapping a strip opens it. Phones and tablets: stacked,
            every card open. Without JavaScript all cards show their text.
        --}}
        <ul class="mt-10 flex flex-col gap-4 lg:h-[600px] lg:flex-row" x-data="{ active: 0 }">
            @foreach ($services as $i => $service)
                <li class="relative h-[400px] overflow-hidden rounded-image-lg bg-forest sm:h-[440px] lg:h-full lg:min-w-0 lg:basis-0 lg:transition-[flex-grow] lg:duration-500 lg:ease-out motion-reduce:transition-none"
                    :class="active === {{ $i }} ? 'lg:grow-[4]' : 'lg:grow'"
                    @mouseenter="active = {{ $i }}" @focusin="active = {{ $i }}">
                    @if ($service['image'])
                        <img src="{{ $service['image'] }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-forest via-forest/50 to-forest/10" aria-hidden="true"></div>

                    {{-- Narrow strip (computers only): sideways title; a button so keyboards can open it too. --}}
                    <button type="button" @click="active = {{ $i }}" :aria-expanded="(active === {{ $i }}).toString()" aria-controls="service-{{ $i }}"
                        class="absolute inset-0 hidden items-center justify-center transition-opacity duration-300 lg:flex"
                        :class="active === {{ $i }} ? 'pointer-events-none opacity-0' : 'opacity-100'">
                        <span class="rotate-180 whitespace-nowrap text-lg font-bold uppercase tracking-[0.2em] text-white [writing-mode:vertical-rl]">{{ $service['title'] }}</span>
                    </button>

                    {{-- Open card --}}
                    <div id="service-{{ $i }}" class="absolute inset-x-0 bottom-0 p-6 sm:p-8 lg:w-[560px] lg:max-w-full lg:p-10 lg:transition-opacity lg:duration-300"
                        :class="active === {{ $i }} ? 'lg:opacity-100 lg:delay-200' : 'lg:pointer-events-none lg:opacity-0'">
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-moss">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</p>
                        <h2 class="mt-2 text-3xl text-white sm:text-4xl lg:text-5xl">{{ $service['title'] }}</h2>
                        <p class="mt-4 max-w-md text-base font-medium leading-relaxed text-white sm:text-lg">{{ $service['text'] }}</p>
                        <a href="{{ $service['url'] }}" class="mt-6 inline-flex min-h-11 items-center gap-3 font-semibold text-white">
                            <span class="grid h-12 w-12 place-items-center rounded-full bg-white text-forest transition hover:bg-mist"><x-glyph name="arrow-right" class="h-5 w-5" /></span>
                            {{ $service['action'] }}
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="container-page pb-20 lg:pb-28">
        <div class="flex flex-col items-center gap-6 rounded-image-lg bg-sage p-8 text-center sm:p-12 lg:p-16">
            <h2 class="max-w-2xl text-3xl sm:text-4xl lg:text-5xl">Planting for a farm, school or project?</h2>
            <p class="max-w-xl text-lg text-ink/80">Tell us what you need and where. We reply with prices and a ready date, usually the same day.</p>
            <div class="flex w-full flex-col justify-center gap-3 sm:w-auto sm:flex-row">
                <a href="{{ route('quote.show') }}" class="btn-dark">Request a quote</a>
                <a href="{{ route('contact') }}" class="btn-outline">Contact us</a>
            </div>
        </div>
    </section>
</x-layouts.site>
