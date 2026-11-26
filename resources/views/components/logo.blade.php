@props(['dark' => false])
<a href="{{ route('home') }}" {{ $attributes->class(['inline-flex min-h-10 shrink-0 items-center gap-2.5 sm:gap-3','text-forest' => ! $dark,'text-white' => $dark]) }} aria-label="Wisdom Propagation Nursery, home">
    <img src="{{ asset($dark ? 'images/logo-mark-white.png' : 'images/logo-mark.png') }}" alt="" width="40" height="40" class="h-10 w-10 sm:h-11 sm:w-11">
    <span class="flex flex-col">
        <span class="font-display text-[15px] leading-tight sm:text-lg">Wisdom Propagation</span>
        <span class="mt-0.5 text-[10px] font-medium uppercase leading-none tracking-[0.42em] opacity-80 sm:text-[11px]">Nursery</span>
    </span>
</a>
