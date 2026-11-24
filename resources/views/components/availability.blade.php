@props(['plant'])
@php $tone = $plant->availabilityTone(); @endphp
<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-semibold',
    'text-leaf' => $tone === 'ready',
    'text-clay' => $tone === 'soon',
    'text-red-700' => $tone === 'out',
]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    {{ $plant->availabilityLabel() }}
</span>
