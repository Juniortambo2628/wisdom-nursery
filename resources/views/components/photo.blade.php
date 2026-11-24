@props([
    'src' => null,       // real image URL; null shows the labelled placeholder
    'label' => '',       // what photo goes here (also the alt text)
    'dark' => false,     // placeholder on a forest background
    'srcset' => null,    // optional responsive sources
    'sizes' => '100vw',
])
@if ($src)
    <img src="{{ $src }}" @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif alt="{{ $label }}" loading="lazy" {{ $attributes->class('object-cover') }}>
@else
    <div role="img" aria-label="Photo to come: {{ $label }}" {{ $attributes->class([
        'photo-placeholder px-4 uppercase tracking-[0.14em]',
        'photo-placeholder-dark' => $dark,
    ]) }}>
        <span>{{ $label }}</span>
    </div>
@endif
