@props(['separator' => ' · ', 'linkClass' => 'hover:underline'])
{{-- The nursery's phone numbers from Site settings, each a call link (placeholders show as plain text). --}}
@foreach (site_phones() as $p)
    @if ($p['tel'])<a href="tel:{{ $p['tel'] }}" class="{{ $linkClass }}">{{ $p['number'] }}</a>@else{{ $p['number'] }}@endif{{ $p['label'] ? ' ('.$p['label'].')' : '' }}@if (! $loop->last){!! $separator !!}@endif
@endforeach
