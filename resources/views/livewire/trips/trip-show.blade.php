@php
    $photos = $package->photos;
    $quote = $this->quote;
    $departure = $this->departure;
    $stats = $this->reviewStats;
    $operator = $package->user;
    $operatorName = $operator->operatorProfile?->business_name ?? $operator->name;
    $initials = collect(explode(' ', $operatorName))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<div class="pb-24 min-[960px]:pb-0">
    <x-site.header />

    <div class="mx-auto max-w-6xl px-4">
        <nav class="py-3.5 text-[13px] text-muted" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" wire:navigate>Trips</a> /
            <a href="{{ route('home', ['q' => $package->place]) }}" wire:navigate>{{ $package->place }}</a> /
            {{ $package->title }}
        </nav>

        <div class="flex items-start justify-between gap-4 pb-4">
            <div>
                <h1 class="mb-1.5 font-display text-[clamp(26px,4vw,38px)] font-bold leading-[1.1] tracking-tight">
                    {{ $package->title }}</h1>
                <p class="text-sm text-muted">
                    @if ($stats['count'])
                        <span aria-hidden="true">&#9733;</span> <b
                            class="font-semibold text-ink">{{ number_format($stats['average'], 1) }}</b>
                        ({{ $stats['count'] }} {{ str('review')->plural($stats['count']) }}) &middot;
                    @endif
                    {{ $package->county ?: $package->place }} &middot; {{ $package->category }} &middot; by
                    {{ $operatorName }}
                </p>
            </div>

            <div class="flex shrink-0 gap-2">
                <div class="relative" x-data="{ copied: false }">
                    <button type="button" aria-label="Share this trip"
                        class="grid size-10 place-items-center rounded-full border border-line bg-surface"
                        x-on:click="navigator.share?.({ title: document.title, url: location.href }) ?? navigator.clipboard.writeText(location.href); copied = true; setTimeout(() => copied = false, 2000)">
                        <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="18" cy="5" r="3" />
                            <circle cx="6" cy="12" r="3" />
                            <circle cx="18" cy="19" r="3" />
                            <path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" />
                        </svg>
                    </button>
                    <span x-show="copied" x-cloak
                        class="absolute right-0 top-12 whitespace-nowrap rounded-md bg-ink px-2.5 py-1 text-xs text-canvas">Link
                        copied</span>
                </div>

                <button type="button" wire:click="toggleSave" aria-pressed="{{ $saved ? 'true' : 'false' }}"
                    aria-label="{{ $saved ? 'Remove from saved' : 'Save trip' }}"
                    class="grid size-10 place-items-center rounded-full border border-line bg-surface">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" @class([
                            'size-5',
                            'fill-accent stroke-accent' => $saved,
                            'fill-none' => !$saved,
                        ]) aria-hidden="true">
                        <path
                            d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-x-10 pb-10 min-[960px]:grid-cols-[minmax(0,1fr)_360px]">
            <div wire:ignore class="mb-4 min-[960px]:mb-2" x-data="{ i: 0, n: {{ $photos->count() }} }">
                <div class="relative h-[260px] overflow-hidden rounded-xl bg-soft min-[960px]:h-[380px]">
                    @foreach ($photos as $k => $photo)
                        <img src="{{ $photo->url }}" alt="{{ $photo->alt }}"
                            style="object-position: {{ $photo->focus }}"
                            @if ($k > 0) loading="lazy" @endif
                            x-bind:class="i === {{ $k }} ? 'opacity-100' : 'opacity-0'"
                            class="absolute inset-0 h-full w-full object-cover transition-opacity duration-500">
                    @endforeach

                    @if ($photos->count() > 1)
                        <button type="button" aria-label="Previous photo" x-on:click="i = (i + n - 1) % n"
                            class="absolute left-2.5 top-1/2 grid size-[38px] -translate-y-1/2 place-items-center rounded-full bg-surface text-xl">&lsaquo;</button>
                        <button type="button" aria-label="Next photo" x-on:click="i = (i + 1) % n"
                            class="absolute right-2.5 top-1/2 grid size-[38px] -translate-y-1/2 place-items-center rounded-full bg-surface text-xl">&rsaquo;</button>
                        <span
                            class="absolute bottom-2.5 right-2.5 rounded-full bg-black/55 px-2.5 py-[3px] text-xs font-semibold text-white"
                            x-text="(i + 1) + ' / ' + n"></span>
                    @endif
                </div>

                @if ($photos->count() > 1)
                    <div class="mt-2 flex gap-2">
                        @foreach ($photos as $k => $photo)
                            <button type="button" aria-label="Show photo {{ $k + 1 }}"
                                x-on:click="i = {{ $k }}"
                                x-bind:class="i === {{ $k }} ? 'border-accent opacity-100' :
                                    'border-transparent opacity-65'"
                                class="h-[52px] flex-1 overflow-hidden rounded-lg border-2">
                                <img src="{{ $photo->url }}" alt=""
                                    style="object-position: {{ $photo->focus }}" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside
                class="mb-2 min-[960px]:sticky min-[960px]:top-20 min-[960px]:col-start-2 min-[960px]:row-span-2 min-[960px]:row-start-1 min-[960px]:mb-0 min-[960px]:self-start">
                <div class="rounded-xl border border-line bg-surface p-4">
                    <p><b class="font-display text-[26px] font-bold">KES {{ number_format($package->price) }}</b> <span
                            class="text-muted">per person</span></p>

                    <span class="auth-label mt-3.5">Choose a date</span>
                    @forelse ($this->departures as $d)
                        @php($left = $d->seatsLeft())
                        <button type="button" wire:key="departure-{{ $d->id }}"
                            wire:click="selectDeparture({{ $d->id }})" @disabled($left < 1)
                            @class([
                                'mb-1.5 flex w-full items-center justify-between gap-2 rounded-md border-[1.5px] px-3 py-[9px] text-left text-sm disabled:opacity-50',
                                'border-accent bg-soft' => $d->id === $departureId,
                                'border-line' => $d->id !== $departureId,
                            ])>
                            <span>
                                <b class="font-semibold">{{ $d->rangeLabel() }}</b>
                                <small class="block text-xs text-muted">Balance due
                                    {{ $d->balanceDueOn()->format('j M') }}</small>
                            </span>
                            <span @class([
                                'text-[13px]',
                                'font-semibold text-warn' => $left > 0 && $left <= 3,
                                'text-muted' => $left > 3 || $left < 1,
                            ])>
                                {{ $left < 1 ? 'Sold out' : ($left <= 3 ? 'Only ' . $left . ' seats left' : $left . ' seats left') }}
                            </span>
                        </button>
                    @empty
                        <p class="text-sm text-muted">No upcoming dates yet. Check back soon.</p>
                    @endforelse

                    @if ($departure)
                        <span class="auth-label mt-3.5">Travelers</span>
                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="adjustTravelers(-1)" @disabled($travelers <= 1)
                                aria-label="Fewer travelers"
                                class="size-9 rounded-md border-[1.5px] border-line text-lg disabled:opacity-40">&minus;</button>
                            <b>{{ $travelers }}</b>
                            <button type="button" wire:click="adjustTravelers(1)" @disabled($travelers >= $this->maxTravelers)
                                aria-label="More travelers"
                                class="size-9 rounded-md border-[1.5px] border-line text-lg disabled:opacity-40">+</button>
                            <span class="text-[13px] text-muted">Up to {{ $this->maxTravelers }} for this date</span>
                        </div>

                        <div class="mt-3.5 rounded-lg bg-soft px-3 py-2.5 text-sm">
                            <div class="flex justify-between gap-2 py-[3px]"><span class="text-muted">KES
                                    {{ number_format($package->price) }} &times; {{ $travelers }}</span><b
                                    class="font-semibold">KES {{ number_format($quote['total']) }}</b></div>
                            <div class="my-2 border-t border-line"></div>
                            <div class="flex justify-between gap-2 py-[3px]"><span class="text-muted">Pay today
                                    ({{ $package->deposit_percent }}% deposit)</span><b class="font-semibold">KES
                                    {{ number_format($quote['deposit']) }}</b></div>
                            <div class="flex justify-between gap-2 py-[3px]"><span class="text-muted">Pay by
                                    {{ $quote['due_on']->format('j M') }}</span><b class="font-semibold">KES
                                    {{ number_format($quote['balance']) }}</b></div>
                        </div>
                    @endif

                    @error('departure')
                        <p class="auth-error mt-2.5" role="alert">{{ $message }}</p>
                    @enderror
                    @error('travelers')
                        <p class="auth-error mt-2.5" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="button" wire:click="reserve" wire:loading.attr="disabled" wire:target="reserve"
                        @disabled(!$departure) class="auth-btn mt-3">
                        {{ $departure ? 'Reserve ' . $departure->starts_on->format('j M') : 'No dates available' }}
                    </button>
                    <p class="auth-fine">You only pay the deposit today. The operator's phone number and meeting point
                        are shared once it is paid.</p>
                </div>
            </aside>

            <div class="min-[960px]:col-start-1 min-[960px]:row-start-2">
                <section class="pt-2">
                    <div class="grid grid-cols-2 gap-2.5 min-[640px]:grid-cols-4">
                        @foreach ([['Duration', $package->durationLabel()], ['Group size', 'Up to ' . $package->max_group . ' people'], ['Pickup', trim(($package->pickup_location ?? '') . ($package->pickup_time ? ', ' . $package->pickup_time : ''), ', ') ?: 'Arranged with the operator'], ['Languages', implode(', ', $package->languages ?? []) ?: 'English']] as [$label, $value])
                            <div class="rounded-xl border border-line bg-surface p-3">
                                <small class="block text-xs text-muted">{{ $label }}</small>
                                <b class="text-[15px] font-semibold">{{ $value }}</b>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mt-5 border-t border-line pt-5">
                    <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">About this trip</h2>
                    @foreach (preg_split('/\R{2,}/', trim($package->description)) as $paragraph)
                        <p class="mb-2.5 {{ $loop->first ? '' : 'text-muted' }}">{{ $paragraph }}</p>
                    @endforeach
                </section>

                @if (!empty($package->itinerary))
                    <section class="mt-5 border-t border-line pt-5">
                        <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">Day by day</h2>
                        @foreach ($package->itinerary as $day)
                            <details @if ($loop->first) open @endif
                                class="group mb-2 rounded-xl border border-line bg-surface">
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 px-3.5 py-3 font-semibold [&::-webkit-details-marker]:hidden">
                                    {{ $day['title'] }}
                                    <span class="font-medium text-muted group-open:hidden">+</span>
                                    <span class="hidden font-medium text-muted group-open:inline">&minus;</span>
                                </summary>
                                <div class="px-3.5 pb-3 text-muted">{{ $day['body'] }}</div>
                            </details>
                        @endforeach
                    </section>
                @endif

                <section class="mt-5 border-t border-line pt-5">
                    <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">What is included</h2>
                    <div class="grid gap-3 min-[640px]:grid-cols-2">
                        <div class="rounded-xl border border-line bg-surface p-3.5">
                            <h3 class="mb-1 font-display text-base font-bold">Included</h3>
                            <ul>
                                @foreach ($package->includes ?? [] as $item)
                                    <li
                                        class="relative py-[3px] pl-[22px] before:absolute before:left-0 before:font-bold before:text-ok before:content-['\2713']">
                                        {{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="rounded-xl border border-line bg-surface p-3.5">
                            <h3 class="mb-1 font-display text-base font-bold">Not included</h3>
                            <ul>
                                @foreach ($package->excludes ?? [] as $item)
                                    <li
                                        class="relative py-[3px] pl-[22px] before:absolute before:left-0 before:font-bold before:text-muted before:content-['\2715']">
                                        {{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border-t border-line pt-5">
                    <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">Where you will meet</h2>
                    <div class="flex gap-3 rounded-xl bg-soft p-3.5">
                        <span aria-hidden="true">&#128205;</span>
                        <div>
                            <b>{{ $package->pickup_location ?: 'Arranged with the operator' }}{{ $package->pickup_time ? ', ' . $package->pickup_time : '' }}</b>
                            <p class="mt-0.5 text-muted">The exact spot and the operator's phone number are shared as
                                soon as your deposit is paid.</p>
                        </div>
                    </div>
                </section>

                <section id="reviews" class="mt-5 scroll-mt-20 border-t border-line pt-5">
                    <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">Reviews</h2>
                    @if ($stats['count'])
                        <div class="grid gap-4 min-[640px]:grid-cols-[200px_1fr]">
                            <div>
                                <div class="font-display text-[40px] font-bold leading-none">
                                    {{ number_format($stats['average'], 1) }}</div>
                                <div class="text-muted">{{ $stats['count'] }}
                                    {{ str('review')->plural($stats['count']) }}</div>
                            </div>
                            <div class="grid gap-1 text-[13px] text-muted">
                                @foreach ([5, 4, 3, 2, 1] as $star)
                                    <div class="grid grid-cols-[14px_1fr] items-center gap-2">
                                        {{ $star }}
                                        <i class="relative block h-1.5 overflow-hidden rounded-full bg-line"><i
                                                class="absolute inset-y-0 left-0 block bg-accent"
                                                style="width: {{ round((($stats['spread'][$star] ?? 0) / $stats['count']) * 100) }}%"></i></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-3.5">
                            @foreach ($stats['latest'] as $review)
                                <div wire:key="review-{{ $review->id }}"
                                    class="mb-2.5 rounded-xl border border-line bg-surface p-3.5">
                                    <b><span aria-hidden="true">&#9733;</span>
                                        {{ number_format($review->rating, 1) }}</b>
                                    <small
                                        class="text-muted">{{ str($review->user->name ?: 'Traveler')->before(' ') }}{{ $review->travelled_on ? ', ' . $review->travelled_on->format('F') : '' }}</small>
                                    <p class="mt-1.5">{{ $review->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No reviews yet. Travelers can review this trip after they have been.</p>
                    @endif
                </section>

                <section class="mt-5 border-t border-line pt-5">
                    <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">How payment works</h2>
                    <ol class="grid">
                        @foreach ([['Today', 'pay the ' . $package->deposit_percent . '% deposit' . ($quote ? ' (KES ' . number_format($quote['deposit']) . ' for ' . $travelers . ')' : '') . '. Your seats are held.'], [$quote ? 'By ' . $quote['due_on']->format('j M') . ' (' . config('safarihub.booking.balance_due_days') . ' days before)' : config('safarihub.booking.balance_due_days') . ' days before the trip', 'pay the balance' . ($quote ? ' of KES ' . number_format($quote['balance']) : '') . '.'], [$departure ? $departure->rangeLabel() : 'Trip day', 'enjoy the trip.'], ['After', 'leave a review and add photos to your memories.']] as [$when, $what])
                            <li
                                class="relative pb-3.5 pl-7 before:absolute before:left-[5px] before:top-1.5 before:size-2.5 before:rounded-full before:bg-accent {{ $loop->last ? '' : 'after:absolute after:bottom-[-4px] after:left-[9px] after:top-[18px] after:w-0.5 after:bg-line' }}">
                                <b>{{ $when }}:</b> {{ $what }}
                            </li>
                        @endforeach
                    </ol>
                    <p class="text-muted">Cancellation terms are set by each operator and shown before you pay.</p>
                </section>

                <section class="mt-5 border-t border-line pt-5">
                    <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">Your operator</h2>
                    <div
                        class="grid gap-3.5 rounded-xl border border-line bg-surface p-3.5 min-[640px]:grid-cols-[1fr_auto] min-[640px]:items-center">
                        <div class="flex items-center gap-3">
                            <span
                                class="grid size-12 shrink-0 place-items-center rounded-full bg-soft font-display font-bold">{{ $initials }}</span>
                            <div>
                                <h3 class="font-display text-base font-bold">{{ $operatorName }}</h3>
                                @if ($operator->operatorProfile?->county)
                                    <p class="text-sm text-muted">Based in {{ $operator->operatorProfile->county }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="rounded-lg bg-soft px-3 py-2.5">
                            <b
                                class="block font-display text-lg font-bold">{{ $operator->packages()->published()->count() }}</b>
                            <small class="text-xs text-muted">Packages on SafariHub</small>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div
        class="fixed inset-x-0 bottom-0 z-20 flex items-center justify-between gap-3 border-t border-line bg-surface px-4 pt-2.5 pb-[calc(10px+env(safe-area-inset-bottom,0px))] min-[960px]:hidden">
        <div>
            <b class="font-display text-lg font-bold">KES {{ number_format($package->price) }}</b>
            <small class="block text-xs text-muted">per person</small>
        </div>
        <button type="button" wire:click="reserve" wire:loading.attr="disabled" wire:target="reserve"
            @disabled(!$departure)
            class="rounded-md bg-accent px-[22px] py-[11px] font-semibold text-on-accent disabled:opacity-60">Reserve</button>
    </div>
</div>
