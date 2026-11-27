{{-- "Come and see us": home and contact. --}}
<section class="bg-white py-20 lg:py-28">
    <div class="container-page grid gap-10 lg:grid-cols-2 lg:gap-16">
        <div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl">Come and see us</h2>
            <ul class="mt-6 space-y-3 text-ink/80">
                <li class="flex gap-3"><x-glyph name="pin" class="mt-0.5 h-5 w-5 shrink-0 text-leaf" /> <span>{{ setting('address') }}@if (setting('directions'))<br><span class="text-sm text-ink/70">{{ setting('directions') }}</span>@endif</span></li>
                <li class="flex gap-3"><x-glyph name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-leaf" /> {{ setting('hours') }}</li>
                <li class="flex gap-3"><x-glyph name="phone" class="mt-0.5 h-5 w-5 shrink-0 text-leaf" /> <span><x-phone-numbers separator="<br>" link-class="hover:text-forest" /><br>{{ setting('email') }}</span></li>
            </ul>
            @if ($embed = setting('map_embed_url'))
                <iframe src="{{ $embed }}" title="Map to Wisdom Nursery" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="mt-8 aspect-[16/10] w-full rounded-image border-0"></iframe>
            @else
                <x-photo label="Map" class="mt-8 aspect-[16/10] w-full rounded-image" />
            @endif
            @if ($mapLink = setting('map_link'))
                <a href="{{ $mapLink }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-1.5 font-semibold text-leaf hover:text-forest">Get directions <x-glyph name="arrow-right" class="h-4 w-4" /></a>
            @endif
        </div>
        <x-contact-form class="self-start" />
    </div>
</section>
