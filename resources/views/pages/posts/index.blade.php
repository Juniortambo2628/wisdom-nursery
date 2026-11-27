<x-layouts.site title="Grower tips" active="tips" description="Plant care and tree planting guides from the Wisdom Nursery team.">
    <x-page-hero eyebrow="Grower tips" title="Grow it well." :image="site_image('image_tips_banner') ?? site_image('image_step_1')" :srcset="site_image_srcset('image_tips_banner')"
        intro="Plant care and tree planting guides from our growers." />
    <section class="container-page py-16 lg:py-24">
        @if ($posts->isEmpty())
            <div class="card max-w-xl p-8">
                <p class="font-display text-2xl">Our first guides are on their way.</p>
                <p class="mt-2 text-ink/80">Questions in the meantime? Ask our agronomist on WhatsApp.</p>
            </div>
        @else
            <ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3" data-reveal data-reveal-stagger>
                @foreach ($posts as $post)
                    <li class="card overflow-hidden">
                        <a href="{{ route('posts.show', $post) }}" class="group block">
                            <x-photo :src="$post->image_path ? Storage::disk('public')->url($post->image_path) : null" :label="$post->title" class="aspect-[16/10] w-full" />
                            <div class="p-6">
                                @if ($post->category)<p class="eyebrow">{{ $post->category }}</p>@endif
                                <h2 class="mt-2 text-2xl group-hover:text-leaf">{{ $post->title }}</h2>
                                @if ($post->excerpt)<p class="mt-2 text-ink/80">{{ $post->excerpt }}</p>@endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.site>
