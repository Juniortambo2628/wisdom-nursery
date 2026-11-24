{{-- Filterable gallery (About, Gallery). Expects $gallery. --}}
<div x-data="{ filter: 'all' }">
    <x-section-heading :title="$title ?? 'Gallery'" :level="$level ?? 2">
        <x-slot:action>
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filter photos">
                @foreach (['all' => 'All'] + \App\Models\GalleryImage::CATEGORIES as $key => $label)
                    <button type="button" @click="filter = '{{ $key }}'" :aria-pressed="filter === '{{ $key }}'"
                        class="rounded-full border px-4 py-2 text-sm transition"
                        :class="filter === '{{ $key }}' ? 'border-forest bg-forest text-white' : 'border-ink/15 bg-white hover:border-leaf'">{{ $label }}</button>
                @endforeach
            </div>
        </x-slot:action>
    </x-section-heading>
    @if ($gallery->isEmpty())
        <p class="mt-8 text-ink/60">Photos are on their way.</p>
    @else
        <ul class="mt-10 grid grid-flow-dense auto-rows-[140px] grid-cols-2 gap-4 sm:auto-rows-[200px] lg:auto-rows-[220px] lg:grid-cols-4">
            @foreach ($gallery as $image)
                <li x-show="filter === 'all' || filter === '{{ $image->category }}'"
                    @class(['col-span-2 row-span-2' => $loop->index % 5 === 0])>
                    <x-photo :src="$image->path ? Storage::disk('public')->url($image->path) : null" :label="$image->caption"
                        class="h-full w-full rounded-image" />
                </li>
            @endforeach
        </ul>
    @endif
</div>
