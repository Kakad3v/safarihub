@push('head')
    <meta name="robots" content="noindex">
@endpush

<div>
    <x-site.header />

    <main class="mx-auto max-w-2xl px-4 pb-16 pt-8">
        <img src="{{ $memory->url }}" alt="{{ $memory->caption }}" class="w-full rounded-xl object-contain">

        @if ($memory->caption)
            <p class="mt-4 font-display text-xl font-bold tracking-tight">{{ $memory->caption }}</p>
        @endif

        <p class="mt-1 text-sm text-muted">
            Shared by {{ str($memory->user->name ?: 'a traveler')->before(' ') }}
            @if ($memory->package)
                &middot; <a href="{{ route('trips.show', $memory->package) }}" wire:navigate
                    class="text-accent">{{ $memory->package->title }}</a>
            @endif
        </p>

        <div class="mt-8 rounded-xl bg-panel p-5 text-panel-ink">
            <h2 class="font-display text-lg font-bold tracking-tight">Plan a trip of your own</h2>
            <p class="mt-1 text-sm text-panel-mute">Browse trips from local operators across Kenya, with clear prices
                and honest reviews.</p>
            <a href="{{ route('home') }}" wire:navigate
                class="mt-3 inline-block rounded-md bg-accent px-4 py-2.5 text-sm font-semibold text-on-accent">Explore
                trips</a>
        </div>
    </main>
</div>
