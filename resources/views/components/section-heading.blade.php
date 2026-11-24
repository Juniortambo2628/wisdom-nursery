@props(['eyebrow' => null, 'title', 'dark' => false, 'level' => 2])
<div {{ $attributes->class('flex flex-wrap items-end justify-between gap-4') }}>
    <div>
        @if ($eyebrow)
            <p @class(['eyebrow mb-3', 'text-moss' => $dark])>{{ $eyebrow }}</p>
        @endif
        <h{{ $level }} @class(['text-3xl sm:text-4xl lg:text-5xl', 'text-white' => $dark])>{{ $title }}</h{{ $level }}>
    </div>
    @isset($action)
        <div class="max-w-full">{{ $action }}</div>
    @endisset
</div>
