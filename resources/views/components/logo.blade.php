@props(['dark' => false])
{{-- Placeholder until the original logo file arrives (proposal 06). --}}
<a href="{{ route('home') }}" {{ $attributes->class([
    'inline-flex items-center gap-2 rounded-lg border border-dashed px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.16em] sm:px-4',
    'border-forest/30 text-forest' => ! $dark,
    'border-white/30 text-white' => $dark,
]) }}>
    <x-glyph name="leaf" class="h-4 w-4" />
    <span>Wisdom Propagation Nursery</span>
</a>
