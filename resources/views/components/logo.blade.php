@props(['dark' => false])
{{-- The official logo has dark green lettering, so on dark backgrounds it sits on a white panel. --}}
<a href="{{ route('home') }}" {{ $attributes->class(['inline-flex shrink-0 items-center','rounded-2xl bg-white p-3' => $dark]) }} aria-label="Wisdom Nursery, home">
    <img src="{{ asset('images/logo.png') }}" alt="Wisdom Nursery" width="290" height="320" @class(['w-auto','h-24' => $dark,'h-16 lg:h-20' => ! $dark])>
</a>
