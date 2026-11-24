<form method="POST" action="{{ route('contact.store') }}" {{ $attributes->class('card p-6 sm:p-8') }}>
    @csrf
    <x-honeypot />
    <h3 class="text-2xl">Send us a message</h3>
    @if (session('contact_sent'))
        <p class="mt-4 rounded-xl bg-mist px-4 py-3 text-sm font-medium text-leaf" role="status">{{ session('status') }}</p>
    @endif
    <div class="mt-5 grid gap-4">
        <x-field name="name" label="Name" placeholder="Your name" required autocomplete="name" />
        <x-field name="phone" label="Phone" type="tel" placeholder="07XX XXX XXX" required autocomplete="tel" />
        <x-field name="message" label="What are you looking for?" textarea required />
    </div>
    <button class="btn-primary mt-6 w-full">Send</button>
</form>
