@props(['dark' => false])
{{-- Three-tree mark from the client's logo, with the name set in the site's type. --}}
<a href="{{ route('home') }}" {{ $attributes->class([
    'inline-flex min-h-10 shrink-0 items-center gap-2.5 sm:gap-3',
    'text-forest' => ! $dark,
    'text-white' => $dark,
]) }} aria-label="Wisdom Propagation Nursery, home">
    <img src="{{ asset($dark ? 'images/logo-mark-white.png' : 'images/logo-mark.png') }}" alt="" width="34" height="40" class="h-9 w-auto sm:h-10">
    <span class="font-display text-[15px] leading-[1.05] sm:text-lg">
        Wisdom<br class="sm:hidden"> Propagation<br> Nursery
    </span>
</a>
