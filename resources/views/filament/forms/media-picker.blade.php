{{-- "Choose from library": every uploaded photo as a tappable grid, with search and folder filter. --}}
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }}, q: '', folder: '' }" class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <x-filament::input.wrapper class="flex-1">
                <x-filament::input type="search" x-model="q" placeholder="Search by file name…" />
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
        @if ($images->isEmpty())
            <p class="text-sm text-gray-500">No photos uploaded yet.</p>
        @else
            <ul class="grid max-h-[60vh] grid-cols-2 gap-3 overflow-y-auto p-1 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($images as $img)
                    <li x-show="(! folder || folder === @js($img['folder'])) && (! q || @js(strtolower($img['name'])).includes(q.toLowerCase()))">
                        <button type="button" @click="state = @js($img['path'])"
                            class="group block w-full overflow-hidden rounded-xl text-left ring-2 transition"
                            :class="state === @js($img['path']) ? 'ring-primary-600' : 'ring-transparent hover:ring-gray-300 dark:hover:ring-gray-600'">
                            <span class="block aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                                <img src="{{ $img['thumb'] }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            </span>
                            <span class="flex items-center justify-between gap-2 px-2 py-1.5 text-xs">
                                <span class="truncate text-gray-700 dark:text-gray-200">{{ $img['name'] }}</span>
                                <span x-show="state === @js($img['path'])" class="shrink-0 font-semibold text-primary-600">Chosen</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-dynamic-component>
