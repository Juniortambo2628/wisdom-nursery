{{-- Inline styles: Filament's stylesheet does not include the site's Tailwind classes. --}}
<span style="display:flex;align-items:center;gap:.625rem">
    <img src="{{ asset($dark ? 'images/logo-mark-white.png' : 'images/logo-mark.png') }}" alt="" style="height:2.25rem;width:2.25rem">
    <span style="display:flex;flex-direction:column;color:{{ $dark ? '#fff' : '#030712' }}">
        <span style="font-size:1rem;font-weight:600;line-height:1.25">Wisdom Propagation</span>
        <span style="margin-top:.125rem;font-size:10px;font-weight:500;line-height:1;letter-spacing:.42em;text-transform:uppercase;opacity:.7">Nursery</span>
    </span>
</span>
