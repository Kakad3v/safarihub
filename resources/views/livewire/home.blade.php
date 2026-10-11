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
                    @if (count($reviews))
                        <a href="#reviews">Reviews</a>
                    @endif
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

    <section class="relative overflow-hidden" x-data="{
        packages: @js($heroPackages),
        slides: [],
        s: 0,
        timer: null,
        paused: false,
        reduce: matchMedia('(prefers-reduced-motion: reduce)').matches,
        init() {
            this.slides = this.packages.flatMap((pkg, pi) => pkg.images.map((img, ii) => ({ ...img, pi, ii })));
            this.play();
        },
        get p() {
            return this.slides[this.s]?.pi ?? 0;
        },
        get pkg() {
            return this.packages[this.p];
        },
        get dots() {
            return this.slides.map((slide, k) => ({ k, pi: slide.pi })).filter(d => d.pi === this.p);
        },
        play() {
            clearInterval(this.timer);
            if (this.reduce || this.slides.length < 2) return;
            this.timer = setInterval(() => {
                if (!this.paused && !document.hidden) this.s = (this.s + 1) % this.slides.length;
            }, 3500);
        },
        show(pi) {
            this.s = this.slides.findIndex(slide => slide.pi === pi);
            this.play();
        },
        go(k) {
            this.s = k;
            this.play();
        },
    }">
        @if (count($heroPackages))
            <div wire:ignore class="pointer-events-none absolute inset-0" aria-hidden="true">
                <template x-for="(slide, k) in slides" x-bind:key="'bg' + k">
                    <img x-bind:src="slide.src" alt="" x-bind:class="k === s ? 'opacity-25' : 'opacity-0'"
                        x-bind:style="'object-position:' + (slide.pos ?? '50% 50%') + ';transform:scale(' + (k === s && !reduce ?
                            1.12 : 1.04) + ')'"
                        class="absolute inset-0 h-full w-full object-cover blur-lg"
                        style="transition: opacity 1.4s ease, transform 9s ease-out">
                </template>
                <div class="absolute inset-0 bg-linear-to-r from-canvas to-transparent to-75%"></div>
                <div class="absolute inset-x-0 bottom-0 h-2/5 bg-linear-to-t from-canvas to-transparent"></div>
            </div>
        @endif

        <div
            class="relative mx-auto grid max-w-6xl items-center gap-8 px-4 pb-6 pt-8 min-[900px]:grid-cols-[1.1fr_1fr] min-[900px]:gap-12 min-[900px]:pt-14">
            <div>
                <h1
                    class="max-w-[13em] font-display text-[clamp(32px,5.2vw,56px)] font-bold leading-[1.05] tracking-tight">
                    {{ $user && $user->name ? 'Welcome back, ' . str($user->name)->before(' ') . '. Where to next?' : 'Honest trips and reviews for Kenya and Africa' }}
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

            @if (count($heroPackages))
                <aside wire:ignore class="rounded-xl bg-panel p-4 text-panel-ink" aria-label="Featured trips"
                    aria-roledescription="carousel" x-on:mouseenter="paused = true" x-on:mouseleave="paused = false"
                    x-on:focusin="paused = true" x-on:focusout="paused = false">
                    <div class="relative h-64 overflow-hidden rounded-lg min-[900px]:h-72"
                        x-bind:style="'background:' + pkg.color">
                        <template x-for="(slide, k) in slides" x-bind:key="k">
                            <img x-bind:src="slide.src" x-bind:alt="slide.alt" x-bind:aria-hidden="k !== s"
                                x-bind:class="k === s ? 'opacity-100' : 'opacity-0'"
                                x-bind:style="'object-position:' + (slide.pos ?? '50% 50%')"
                                class="absolute inset-0 h-full w-full object-cover transition-opacity duration-700"
                                onerror="this.remove()">
                        </template>

                        <span
                            class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white"
                            x-text="pkg.place"></span>

                        <div class="absolute right-3 top-3 flex items-center gap-1.5">
                            <template x-for="d in dots" x-bind:key="d.k">
                                <button type="button" x-on:click="go(d.k)"
                                    x-bind:aria-label="'Show photo ' + (d.k + 1)"
                                    x-bind:class="d.k === s ? 'w-5 opacity-100' : 'w-2 opacity-60'"
                                    class="h-2 rounded-full bg-white transition-all"></button>
                            </template>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5" role="tablist" aria-label="Featured destinations">
                        <template x-for="(pk, pi) in packages" x-bind:key="pk.id">
                            <button type="button" role="tab" x-on:click="show(pi)" x-bind:aria-selected="pi === p"
                                x-bind:class="pi === p ? 'border-accent bg-accent text-on-accent' :
                                    'border-panel-line text-panel-mute'"
                                class="rounded-full border px-3 py-1 text-[13px] font-semibold"
                                x-text="pk.tab ?? pk.place"></button>
                        </template>
                    </div>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div>
                            <a x-bind:href="pkg.url" wire:navigate class="hover:underline">
                                <h2 class="font-display text-lg font-bold leading-tight tracking-tight"
                                    x-text="pkg.title"></h2>
                            </a>
                            <p class="text-sm text-panel-mute" x-text="pkg.operator + ' · ' + pkg.duration"></p>
                        </div>
                        <p class="shrink-0 text-right text-sm text-panel-mute">
                            From
                            <b class="block font-display text-lg font-bold text-panel-ink"
                                x-text="'KES ' + pkg.price.toLocaleString()"></b>
                        </p>
                    </div>

                    <div class="mt-3 min-h-[5.5rem] border-t border-panel-line pt-3">
                        <p class="text-sm leading-snug" x-text="pkg.review"></p>
                        <span class="text-[13px] text-panel-mute" x-text="pkg.reviewer"></span>
                    </div>

                    <a x-bind:href="pkg.url" wire:navigate
                        class="mt-3 block rounded-md bg-accent px-3 py-2.5 text-center text-sm font-semibold text-on-accent">View
                        trip</a>
                </aside>
            @endif
        </div>
    </section>

    <main class="mx-auto max-w-6xl px-4 pb-14">
        <section id="destinations" class="scroll-mt-20 pt-10" aria-label="Destinations">
            <div class="mb-3 flex items-end justify-between gap-3">
                <h2 class="font-display text-2xl font-bold tracking-tight">Popular destinations in Kenya</h2>
                <p class="hidden text-sm text-muted sm:block">More African countries are coming</p>
            </div>

            <div
                class="-mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($destinations as $d)
                    <button type="button" wire:key="dest-{{ $d['place'] }}"
                        wire:click="pickPlace(@js($d['place']))"
                        x-on:click="document.getElementById('trips').scrollIntoView({ behavior: 'smooth' })"
                        class="flex w-[220px] shrink-0 snap-start flex-col justify-start overflow-hidden rounded-xl border border-line bg-surface text-left">
                        <span class="relative block h-28 w-full" style="background: {{ $d['color'] }}">
                            @if ($d['photo'])
                                <img src="{{ $d['photo']->url }}" alt="" loading="lazy"
                                    style="object-position: {{ $d['photo']->focus }}"
                                    class="h-full w-full object-cover">
                            @endif
                            <span
                                class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white">{{ $d['place'] }}</span>
                        </span>
                        <span class="block w-full px-3 pb-3 pt-2.5 text-sm leading-snug">
                            <span class="block">{{ $d['county'] ?: 'Kenya' }}</span>
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
                    @foreach ($trips as $package)
                        @php($isSaved = in_array($package->id, $saved, true))
                        @php($photo = $package->photos->first())
                        <article wire:key="trip-{{ $package->id }}"
                            class="relative overflow-hidden rounded-xl border border-line bg-surface transition-shadow hover:shadow-md">
                            <div class="relative h-44"
                                style="background: {{ $colors[$package->category] ?? '#BFD3D0' }}">
                                @if ($photo)
                                    <img src="{{ $photo->url }}" alt="{{ $photo->alt }}" loading="lazy"
                                        style="object-position: {{ $photo->focus }}"
                                        class="h-full w-full object-cover">
                                @endif
                                <span
                                    class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white">{{ $package->place }}</span>

                                <button type="button" wire:click="toggleSave({{ $package->id }})"
                                    aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
                                    aria-label="{{ $isSaved ? 'Remove from saved' : 'Save trip' }}"
                                    class="absolute right-2.5 top-2.5 z-10 grid size-9 place-items-center rounded-full bg-surface text-ink">
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
                                    <a href="{{ route('trips.show', $package) }}" wire:navigate
                                        class="after:absolute after:inset-0">{{ $package->title }}</a>
                                </h3>
                                <p class="mt-0.5 text-sm text-muted">
                                    {{ $package->user->operatorProfile?->business_name ?? $package->user->name }}
                                    &middot; {{ $package->durationLabel() }}</p>
                                <div class="mt-3 flex items-end justify-between">
                                    <p class="text-sm">
                                        @if ($package->reviews_count)
                                            <span aria-hidden="true">&#9733;</span> <b
                                                class="font-semibold">{{ number_format($package->reviews_avg_rating, 1) }}</b>
                                            <span class="text-muted">({{ $package->reviews_count }})</span>
                                        @else
                                            <span class="text-muted">New</span>
                                        @endif
                                    </p>
                                    <p class="text-right text-sm text-muted">From <b
                                            class="font-display text-lg font-bold text-ink">KES
                                            {{ number_format($package->price) }}</b></p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if (count($reviews))
            <section id="reviews" class="scroll-mt-20 pt-12" aria-label="Traveler reviews">
                <h2 class="font-display text-2xl font-bold tracking-tight">What travelers say</h2>
                <div class="mt-3.5 grid gap-3 sm:grid-cols-3">
                    @foreach ($reviews as [$quote, $name, $tripTitle, $rating])
                        <figure class="rounded-xl border border-line bg-surface p-4">
                            <p class="text-sm"><span aria-hidden="true">&#9733;</span> <b
                                    class="font-semibold">{{ number_format($rating, 1) }}</b></p>
                            <blockquote class="mt-2 text-[15px] leading-snug">{{ $quote }}</blockquote>
                            <figcaption class="mt-3 text-[13px] text-muted">{{ $name }}, {{ $tripTitle }}
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

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
