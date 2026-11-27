<section class="bg-forest py-20 text-white lg:py-28">
    <div class="container-page grid items-center gap-12 lg:grid-cols-2">
        <div>
            <p class="eyebrow text-moss">Our facilities</p>
            <h2 class="mt-4 text-3xl text-white sm:text-4xl lg:text-5xl">Built for healthy plants.</h2>
            <p class="mt-5 max-w-lg text-lg text-white/80">{{ setting('facilities_text') }}</p>
            @php
                // One unit per line in Site settings → About; older data used two fixed fields.
                $units = filled(setting('facility_units'))
                    ? preg_split('/\R/', trim(setting('facility_units')))
                    : [setting('facility_1', '[Facility 1]'), setting('facility_2', '[Facility 2]')];
                $items = array_values(array_filter(array_map('trim', [...$units, (string) setting('facility_certification')])));
            @endphp
            <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                @foreach ($items as $text)
                    <li class="flex items-center gap-3"><x-glyph name="check" class="h-5 w-5 shrink-0 text-moss" /> {{ $text }}</li>
                @endforeach
            </ul>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <x-photo :src="site_image('image_facility_1')" label="Greenhouse" dark class="aspect-[4/3] w-full rounded-image" />
            <x-photo :src="site_image('image_facility_2')" label="Shade net" dark class="aspect-[4/3] w-full rounded-image" />
            <x-photo :src="site_image('image_facility_wide')" label="Aerial / wide shot of the nursery" dark class="col-span-2 aspect-[3/1] w-full rounded-image" />
        </div>
    </div>
</section>
