<div>
    <header class="sticky top-0 z-10 border-b border-line bg-canvas">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" wire:navigate
                    class="font-display text-xl font-bold tracking-tight">SafariHub</a>
                <nav class="hidden items-center gap-4 text-sm font-semibold text-muted min-[900px]:flex"
                    aria-label="Sections">
                    <a href="#destinations">Destinations</a>
                    <a href="#trips">Trips</a>
                    <a href="#reviews">Reviews</a>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('login', ['as' => 'operator']) }}" wire:navigate
                    class="hidden px-2 text-sm font-semibold text-muted sm:inline">Become an operator</a>

                <button type="button" wire:click="$toggle('onlySaved')"
                    x-on:click="document.getElementById('trips').scrollIntoView({ behavior: 'smooth' })"
                    aria-pressed="{{ $onlySaved ? 'true' : 'false' }}" @class([
                        'rounded-full border px-3 py-[7px] text-[13px] font-semibold',
                        'border-accent bg-soft text-ink' => $onlySaved,
                        'border-line bg-surface text-ink' => !$onlySaved,
                    ])>Saved
                    ({{ count($saved) }})</button>

                <x-auth.theme-toggle />

                @if ($user)
                    <button type="button" wire:click="logout"
                        class="rounded-full bg-accent px-3.5 py-[7px] text-[13px] font-semibold text-on-accent">Sign
                        out</button>
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        class="rounded-full bg-accent px-3.5 py-[7px] text-[13px] font-semibold text-on-accent">Sign
                        in</a>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pb-14">
        <section
            class="grid items-center gap-8 pb-6 pt-8 min-[900px]:grid-cols-[1.1fr_1fr] min-[900px]:gap-12 min-[900px]:pt-14">
            <div>
                <h1
                    class="max-w-[13em] font-display text-[clamp(32px,5.2vw,56px)] font-bold leading-[1.05] tracking-tight">
                    {{ $user ? 'Welcome back, ' . str($user->name)->before(' ') . '. Where to next?' : 'Honest trips and reviews for Kenya and Africa' }}
                </h1>
                <p class="mt-4 max-w-[30em] text-base text-muted">
                    Real reviews, clear prices and direct chat with local operators. Starting in Kenya, built for all of
                    Africa.
                </p>

                <div class="mt-6 max-w-lg rounded-xl border border-line bg-surface p-3.5">
                    <label for="search" class="auth-label">Where do you want to go?</label>
                    <div class="auth-field">
                        <input id="search" type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Masai Mara, Diani, a trip or an operator" class="auth-input">
                    </div>
                    <button type="button"
                        x-on:click="document.getElementById('trips').scrollIntoView({ behavior: 'smooth' })"
                        class="auth-btn">Search trips</button>
                </div>

                <p class="mt-4 text-sm text-muted">Clear prices &middot; Direct chat &middot; Honest reviews</p>
            </div>

            <aside class="rounded-xl bg-panel p-4 text-panel-ink" aria-label="Featured trip">
                <div class="relative h-52 overflow-hidden rounded-lg" style="background: {{ $featured['color'] }}">
                    @if ($featured['image'])
                        <img src="{{ config('safarihub.placeholder_images.' . $featured['image']) }}"
                            alt="{{ $featured['place'] }}" class="h-full w-full object-cover" onerror="this.remove()">
                    @endif
                    <span
                        class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white">{{ $featured['place'] }}</span>
                </div>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-bold leading-tight tracking-tight">
                            {{ $featured['title'] }}</h2>
                        <p class="text-sm text-panel-mute">{{ $featured['operator'] }}</p>
                    </div>
                    <p class="text-right text-sm text-panel-mute">From <b
                            class="font-display text-lg font-bold text-panel-ink">KES
                            {{ number_format($featured['price']) }}</b></p>
                </div>
                <div class="mt-3 border-t border-panel-line pt-3">
                    <p class="text-sm leading-snug">The guide knew every animal by name and the price was exactly what
                        we paid. No surprises.</p>
                    <span class="text-[13px] text-panel-mute">Wanjiru, 5.0 for Masai Mara safari</span>
                </div>
            </aside>
        </section>

        <section id="destinations" class="scroll-mt-20 pt-10" aria-label="Destinations">
            <div class="mb-3 flex items-end justify-between gap-3">
                <h2 class="font-display text-2xl font-bold tracking-tight">Popular destinations in Kenya</h2>
                <p class="hidden text-sm text-muted sm:block">More African countries are coming</p>
            </div>

            <div
                class="-mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($destinations as $d)
                    <button type="button" wire:key="dest-{{ $d['place'] }}"
                        wire:click="pickPlace('{{ $d['place'] }}')"
                        x-on:click="document.getElementById('trips').scrollIntoView({ behavior: 'smooth' })"
                        class="w-[220px] shrink-0 snap-start overflow-hidden rounded-xl border border-line bg-surface text-left">
                        <span class="relative block h-28" style="background: {{ $d['color'] }}">
                            @if ($d['image'])
                                <img src="{{ config('safarihub.placeholder_images.' . $d['image']) }}" alt=""
                                    loading="lazy" class="h-full w-full object-cover" onerror="this.remove()">
                            @endif
                            <span
                                class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white">{{ $d['place'] }}</span>
                        </span>
                        <span class="block px-3 pb-3 pt-2.5 text-sm leading-snug">
                            <span class="block">{{ $d['tagline'] }}</span>
                            <span class="text-[13px] text-muted">{{ $d['count'] }}
                                {{ str('trip')->plural($d['count']) }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        <section id="trips" class="scroll-mt-20 pt-10" aria-label="Trips">
            <h2 class="font-display text-2xl font-bold tracking-tight">
                {{ $onlySaved ? 'Your saved trips' : 'Trips you can book now' }}</h2>

            <div class="mt-3.5 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach (['' => 'All', ...array_combine($categories, $categories)] as $key => $label)
                    <button type="button" wire:click="setCategory('{{ $key }}')"
                        aria-pressed="{{ $category === $key ? 'true' : 'false' }}"
                        @class([
                            'shrink-0 rounded-full border-[1.5px] px-3.5 py-1.5 text-sm',
                            'border-accent bg-soft font-semibold' => $category === $key,
                            'border-line bg-surface font-medium' => $category !== $key,
                        ])>{{ $label }}</button>
                @endforeach
            </div>

            <p class="mb-3 mt-3 text-sm text-muted">
                {{ $trips->count() }}
                {{ str('trip')->plural($trips->count()) }}{{ $search !== '' ? ' for "' . $search . '"' : '' }}
            </p>

            @if ($trips->isEmpty())
                <div class="rounded-xl border border-line bg-surface p-6 text-center">
                    <p class="font-semibold">
                        {{ $onlySaved && !count($saved) ? 'You have not saved any trips yet.' : 'No trips match that search.' }}
                    </p>
                    <p class="mt-1 text-sm text-muted">Tap the heart on a trip to keep it here.</p>
                    <button type="button" wire:click="clearFilters" class="auth-link mt-2">Show all trips</button>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($trips as $trip)
                        @php($isSaved = in_array($trip['id'], $saved, true))
                        <article wire:key="trip-{{ $trip['id'] }}"
                            class="overflow-hidden rounded-xl border border-line bg-surface">
                            <div class="relative h-44" style="background: {{ $trip['color'] }}">
                                @if ($trip['image'])
                                    <img src="{{ config('safarihub.placeholder_images.' . $trip['image']) }}"
                                        alt="{{ $trip['place'] }}" loading="lazy" class="h-full w-full object-cover"
                                        onerror="this.remove()">
                                @endif
                                <span
                                    class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white">{{ $trip['place'] }}</span>

                                <button type="button" wire:click="toggleSave({{ $trip['id'] }})"
                                    aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
                                    aria-label="{{ $isSaved ? 'Remove from saved' : 'Save trip' }}"
                                    class="absolute right-2.5 top-2.5 grid size-9 place-items-center rounded-full bg-surface text-ink">
                                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" @class([
                                            'size-5',
                                            'fill-accent stroke-accent' => $isSaved,
                                            'fill-none' => !$isSaved,
                                        ])
                                        aria-hidden="true">
                                        <path
                                            d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-3.5">
                                <h3 class="font-display text-lg font-bold leading-tight tracking-tight">
                                    {{ $trip['title'] }}</h3>
                                <p class="mt-0.5 text-sm text-muted">{{ $trip['operator'] }} &middot;
                                    {{ $trip['duration'] }}</p>
                                <div class="mt-3 flex items-end justify-between">
                                    <p class="text-sm"><span aria-hidden="true">&#9733;</span> <b
                                            class="font-semibold">{{ number_format($trip['rating'], 1) }}</b> <span
                                            class="text-muted">({{ $trip['reviews'] }})</span></p>
                                    <p class="text-right text-sm text-muted">From <b
                                            class="font-display text-lg font-bold text-ink">KES
                                            {{ number_format($trip['price']) }}</b></p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section id="reviews" class="scroll-mt-20 pt-12" aria-label="Traveler reviews">
            <h2 class="font-display text-2xl font-bold tracking-tight">What travelers say</h2>
            <div class="mt-3.5 grid gap-3 sm:grid-cols-3">
                @foreach ($reviews as [$quote, $name, $trip, $rating])
                    <figure class="rounded-xl border border-line bg-surface p-4">
                        <p class="text-sm"><span aria-hidden="true">&#9733;</span> <b
                                class="font-semibold">{{ number_format($rating, 1) }}</b></p>
                        <blockquote class="mt-2 text-[15px] leading-snug">{{ $quote }}</blockquote>
                        <figcaption class="mt-3 text-[13px] text-muted">{{ $name }}, {{ $trip }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        <section class="pt-12" aria-label="How it works">
            <h2 class="font-display text-2xl font-bold tracking-tight">How it works</h2>
            <div class="mt-3.5 grid gap-3 sm:grid-cols-3">
                @foreach ([['Find a trip', 'Browse packages with photos, what is included and the full price.'], ['Chat with the operator', 'Ask questions in the app. Your phone number and email stay private.'], ['Book, travel and review', 'Pay securely, enjoy the trip, then tell other travelers how it went.']] as $i => [$title, $text])
                    <div class="rounded-xl border border-line bg-surface p-4">
                        <span
                            class="grid size-8 place-items-center rounded-full bg-accent font-display font-bold text-on-accent">{{ $i + 1 }}</span>
                        <h3 class="mt-3 font-display text-base font-bold tracking-tight">{{ $title }}</h3>
                        <p class="mt-1 text-sm leading-snug text-muted">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section
            class="mt-12 flex flex-col gap-4 rounded-xl bg-panel p-6 text-panel-ink sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-xl font-bold tracking-tight">Run tours? Put your packages in front of
                    travelers</h2>
                <p class="mt-1 max-w-[34em] text-sm text-panel-mute">List your trips, get inquiries in one inbox, and
                    track what you have earned.</p>
            </div>
            <a href="{{ route('login', ['as' => 'operator']) }}" wire:navigate
                class="shrink-0 rounded-md bg-accent px-5 py-3 text-center text-sm font-semibold text-on-accent">Become
                an operator</a>
        </section>
    </main>

    <footer class="border-t border-line">
        <div
            class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
            <p><span class="font-display font-bold text-ink">SafariHub</span> &middot; Honest trips and reviews for
                Kenya and Africa.</p>
            <nav class="flex gap-4" aria-label="Footer">
                <a href="{{ route('login') }}" wire:navigate>Sign in</a>
                <a href="{{ route('login', ['as' => 'operator']) }}" wire:navigate>For operators</a>
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
            </nav>
        </div>
    </footer>
</div>
