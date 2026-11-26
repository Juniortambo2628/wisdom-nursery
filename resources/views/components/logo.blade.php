@props(['dark' => false])
<a href="{{ route('home') }}" {{ $attributes->class('inline-flex min-h-10 shrink-0 items-center') }} aria-label="Wisdom Propagation Nursery, home">
    <img src="{{ asset($dark ? 'images/logo-horizontal-white.png' : 'images/logo-horizontal.png') }}" alt="Wisdom Propagation Nursery" width="541" height="168" class="h-14 w-auto sm:h-16 lg:h-[76px]">
</a>
