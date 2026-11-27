<x-filament-panels::page>
    <div x-data="{ q: '', folder: '', unused: false }" class="space-y-6">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ $images->count() }} photos. Any of them can be put in a photo box with <strong>Choose from library</strong>, so there is no need to upload the same photo twice.
            Photos in use can't be deleted here; change the photo where it is used first.
        </p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
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
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                <x-filament::input.checkbox x-model="unused" /> Not in use only
            </label>
        </div>

        <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($images as $img)
                @php $uses = $usage[$img['path']] ?? []; @endphp
                <li wire:key="photo-{{ md5($img['path']) }}"
                    x-show="(! folder || folder === @js($img['folder'])) && (! q || @js(strtolower($img['name'])).includes(q.toLowerCase())) && (! unused || {{ $uses ? 'false' : 'true' }})"
                    class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <a href="{{ $img['url'] }}" target="_blank" rel="noopener" class="block aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                        <img src="{{ $img['thumb'] }}" alt="" loading="lazy" class="h-full w-full object-cover">
                    </a>
                    <div class="space-y-1.5 p-3 text-xs">
                        <p class="truncate font-medium text-gray-950 dark:text-white" title="{{ $img['path'] }}">{{ $img['name'] }}</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ $folders[$img['folder']] ?? $img['folder'] }} · {{ number_format($img['size'] / 1024) }} KB</p>
                        @if ($uses)
                            <p class="truncate text-primary-600 dark:text-primary-400" title="{{ implode(' · ', $uses) }}">In use: {{ $uses[0] }}{{ count($uses) > 1 ? ' +'.(count($uses) - 1) : '' }}</p>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Not in use</span>
                                <button type="button" wire:click="deletePhoto(@js($img['path']))" wire:confirm="Delete {{ $img['name'] }}? This can't be undone."
                                    class="font-semibold text-danger-600 hover:underline dark:text-danger-400">Delete</button>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</x-filament-panels::page>
