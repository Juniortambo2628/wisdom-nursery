<x-filament-panels::page>
    <div wire:poll.60s class="space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Checked {{ now()->format('j M Y, H:i') }}. Red needs action now; amber is worth a look. If something stays red, contact OKJTechnologies (+254 705 883 227).
        </p>
        @foreach ($groups as $group => $checks)
            <x-filament::section :heading="$group">
                <ul class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($checks as $check)
                        <li class="flex items-start gap-3 py-3">
                            <x-filament::badge :color="match ($check['status']) { 'ok' => 'success', 'warn' => 'warning', default => 'danger' }" class="mt-0.5 shrink-0">
                                {{ match ($check['status']) { 'ok' => 'OK', 'warn' => 'Check', default => 'Action' } }}
                            </x-filament::badge>
                            <div>
                                <p class="font-medium text-gray-950 dark:text-white">{{ $check['label'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $check['detail'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
