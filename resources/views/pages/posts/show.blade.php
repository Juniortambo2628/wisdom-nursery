<x-layouts.site :title="$post->title" active="tips" :description="$post->excerpt"
    :og-image="$post->image_path ? Storage::disk('public')->url($post->image_path) : null">
    <article class="container-page max-w-3xl py-12 lg:py-20">
        <nav aria-label="Breadcrumb" class="text-sm text-ink/70"><a href="{{ route('posts.index') }}" class="hover:text-leaf">Grower tips</a> / <span class="text-ink">{{ $post->category }}</span></nav>
        <h1 class="mt-4 text-4xl sm:text-5xl lg:text-6xl">{{ $post->title }}</h1>
        <p class="mt-3 text-sm text-ink/70">{{ $post->published_at->format('j F Y') }}</p>
        @if ($post->image_path)
            <img src="{{ Storage::disk('public')->url($post->image_path) }}" alt="" class="mt-8 aspect-[16/9] w-full rounded-image-lg object-cover">
        @endif
        {{-- Body is written by staff in the dashboard's rich editor. --}}
        <div class="prose-post mt-8">{!! $post->body !!}</div>
    </article>
    @if ($more->isNotEmpty())
        <section class="container-page pb-20">
            <h2 class="text-3xl">More grower tips</h2>
            <ul class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ($more as $m)
                    <li><a href="{{ route('posts.show', $m) }}" class="card block p-6 hover:ring-leaf"><p class="eyebrow">{{ $m->category }}</p><p class="mt-2 font-display text-xl">{{ $m->title }}</p></a></li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.site>
