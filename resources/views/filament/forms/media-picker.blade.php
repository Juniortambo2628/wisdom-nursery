{{-- "Choose from library": every uploaded photo, drawn in the browser; tapping one uses it and closes the dialog. --}}
<div x-data="{ images: @js($images), q: '', folder: '', busy: false,
              get shown() { const q = this.q.toLowerCase(); return this.images.filter(i => (! this.folder || i.folder === this.folder) && (! q || i.name.toLowerCase().includes(q))) } }"
     class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row">
        <x-filament::input.wrapper class="flex-1">
            <x-filament::input type="search" x-model.debounce.150ms="q" placeholder="Search by file name…" />
        </x-filament::input.wrapper>
        <x-filament::input.wrapper class="sm:w-56">
            <x-filament::input.select x-model="folder">
                <option value="">All folders</option>
                @foreach ($folders as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
    <p x-show="! shown.length" class="text-sm text-gray-500">No photos found.</p>
    <ul class="grid max-h-[60vh] grid-cols-2 gap-3 overflow-y-auto p-1 sm:grid-cols-3 lg:grid-cols-5" :class="busy && 'pointer-events-none opacity-60'">
        <template x-for="img in shown" :key="img.path">
            <li>
                <button type="button" @click="busy = true; $wire.callMountedFormComponentAction({ path: img.path })"
                    class="block w-full overflow-hidden rounded-xl text-left ring-2 ring-transparent transition hover:ring-primary-600 focus-visible:ring-primary-600">
                    <span class="block aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                        <img :src="img.thumb" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover">
                    </span>
                    <span class="block truncate px-2 py-1.5 text-xs text-gray-700 dark:text-gray-200" x-text="img.name"></span>
                </button>
            </li>
        </template>
    </ul>
</div>
