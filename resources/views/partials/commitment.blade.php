{{-- "Our Commitment" (Home, About): the client's own words. Text in Site settings → About. --}}
@if (filled($commitment = setting('commitment_text')))
    <section class="container-page py-20 lg:py-28">
        <div class="mx-auto max-w-3xl text-center" data-reveal>
            <p class="eyebrow">{{ setting('commitment_title', 'Our Commitment') }}</p>
            @foreach (preg_split('/\R{2,}/', trim($commitment)) as $para)
                <p @class(['text-ink/80', 'mt-5 font-display text-2xl leading-snug text-forest sm:text-3xl' => $loop->first, 'mt-5 text-lg' => ! $loop->first])>{{ $para }}</p>
            @endforeach
            @if ($closing = setting('commitment_closing'))
                <p class="mt-8 text-sm font-bold uppercase tracking-[0.18em] text-leaf">{{ $closing }}</p>
            @endif
        </div>
    </section>
@endif
