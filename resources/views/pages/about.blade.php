<x-layouts.site title="About us" active="about"
    description="The people and the place behind Wisdom Propagation Nursery, part of the Wisdom Capital family.">
    <x-page-hero eyebrow="About us" title="The people behind the plants."
        :image="site_image('image_about_banner')" :srcset="site_image_srcset('image_about_banner')"
        :intro="setting('about_text', setting('story_text'))" />

    @include('partials.climate-advantage')

    @php
        $focus = collect(range(1, 4))
            ->map(fn ($i) => ['title' => setting("about_focus_{$i}_title"), 'text' => setting("about_focus_{$i}_text")])
            ->filter(fn ($f) => filled($f['title']) && filled($f['text']));
    @endphp
    @if ($focus->isNotEmpty())
        <section class="container-page pt-20 lg:pt-28">
            <x-section-heading title="What we grow" />
            <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal data-reveal-stagger>
                @foreach ($focus as $f)
                    <li class="card p-6">
                        <x-glyph name="leaf" class="h-6 w-6 text-leaf" />
                        <h3 class="mt-4 text-xl">{{ $f['title'] }}</h3>
                        <p class="mt-2 text-ink/80">{{ $f['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="container-page py-20 lg:py-28">
        <x-section-heading title="Meet the team">
            <x-slot:action><p class="text-ink/70">{{ $team->count() }} people · one nursery</p></x-slot:action>
        </x-section-heading>
        <ul class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4" data-reveal data-reveal-stagger>
            @foreach ($team as $member)
                <li>
                    <x-photo :src="$member->photo_path ? Storage::disk('public')->url($member->photo_path) : null" label="Portrait in uniform" class="aspect-[9/10] w-full rounded-image" />
                    <h3 class="mt-4 text-xl sm:text-2xl">{{ $member->name }}</h3>
                    <p class="text-sm text-ink/70 sm:text-base">{{ $member->role }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    @include('partials.facilities')

    <section class="container-page py-20 lg:py-28">
        @include('partials.gallery')
    </section>
</x-layouts.site>
