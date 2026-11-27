<x-layouts.site title="About us" active="about"
    description="The people and the place behind Wisdom Propagation Nursery, part of the Wisdom Capital family.">
    <section class="container-page grid items-end gap-8 pt-12 lg:grid-cols-2 lg:pt-20">
        <div>
            <p class="eyebrow">About us</p>
            <h1 class="mt-4 text-5xl sm:text-6xl lg:text-7xl">The people behind the plants.</h1>
        </div>
        <p class="text-lg text-ink/70 lg:pb-3">{{ setting('about_text', setting('story_text')) }}</p>
    </section>
    <div class="container-page mt-10 lg:mt-14">
        <x-photo :src="site_image('image_about_banner')" :srcset="site_image_srcset('image_about_banner')" label="Group photo: whole team in new uniforms at the nursery entrance" class="aspect-[16/10] w-full rounded-image-lg sm:aspect-[2.2/1]" />
    </div>

    @php
        $focus = collect(range(1, 4))
            ->map(fn ($i) => ['title' => setting("about_focus_{$i}_title"), 'text' => setting("about_focus_{$i}_text")])
            ->filter(fn ($f) => filled($f['title']) && filled($f['text']));
    @endphp
    @if ($focus->isNotEmpty())
        <section class="container-page pt-20 lg:pt-28">
            <x-section-heading title="What we grow" />
            <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($focus as $f)
                    <li class="card p-6">
                        <x-glyph name="leaf" class="h-6 w-6 text-leaf" />
                        <h3 class="mt-4 text-xl">{{ $f['title'] }}</h3>
                        <p class="mt-2 text-ink/70">{{ $f['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="container-page py-20 lg:py-28">
        <x-section-heading title="Meet the team">
            <x-slot:action><p class="text-ink/60">{{ $team->count() }} people · one nursery</p></x-slot:action>
        </x-section-heading>
        <ul class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
            @foreach ($team as $member)
                <li>
                    <x-photo :src="$member->photo_path ? Storage::disk('public')->url($member->photo_path) : null" label="Portrait in uniform" class="aspect-[9/10] w-full rounded-image" />
                    <h3 class="mt-4 text-xl sm:text-2xl">{{ $member->name }}</h3>
                    <p class="text-sm text-ink/60 sm:text-base">{{ $member->role }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    @include('partials.facilities')

    <section class="container-page py-20 lg:py-28">
        @include('partials.gallery')
    </section>
</x-layouts.site>
