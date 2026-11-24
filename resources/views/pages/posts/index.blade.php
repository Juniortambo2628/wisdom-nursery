<x-layouts.site title="Grower tips" active="tips" description="Plant care and tree planting guides from the Wisdom Nursery team.">
    <section class="container-page py-12 lg:py-20">
        <p class="eyebrow">Grower tips</p>
        <h1 class="mt-4 text-5xl sm:text-6xl lg:text-7xl">Grow it well.</h1>
        <p class="mt-5 max-w-xl text-lg text-ink/70">Plant care and tree planting guides from our growers.</p>
        @if ($posts->isEmpty())
            <div class="card mt-10 max-w-xl p-8">
                <p class="font-display text-2xl">Our first guides are on their way.</p>
                <p class="mt-2 text-ink/70">Questions in the meantime? Ask our agronomist on WhatsApp.</p>
            </div>
        @else
            <ul class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li class="card overflow-hidden">
                        <a href="{{ route('posts.show', $post) }}" class="group block">
                            <x-photo :src="$post->image_path ? Storage::disk('public')->url($post->image_path) : null" :label="$post->title" class="aspect-[16/10] w-full" />
                            <div class="p-6">
                                @if ($post->category)<p class="eyebrow">{{ $post->category }}</p>@endif
                                <h2 class="mt-2 text-2xl group-hover:text-leaf">{{ $post->title }}</h2>
                                @if ($post->excerpt)<p class="mt-2 text-ink/70">{{ $post->excerpt }}</p>@endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.site>
