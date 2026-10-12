<div>
    <x-site.header />

    <main class="mx-auto max-w-6xl px-4 pb-16 pt-6">
        <section
            class="flex flex-col gap-4 rounded-xl border border-line bg-surface p-4 min-[760px]:flex-row min-[760px]:items-center">
            <x-avatar :user="$operator" class="size-20 text-2xl" />
            <div class="min-w-0 flex-1">
                <h1 class="font-display text-[26px] font-bold leading-tight tracking-tight">{{ $profile->business_name }}
                </h1>
                <p class="text-sm text-muted">Based in {{ $profile->county }} &middot; On SafariHub since
                    {{ $operator->created_at->format('F Y') }}</p>
            </div>
            <div class="grid grid-cols-3 gap-2.5 text-center min-[760px]:w-72">
                @foreach ([['Packages', $packages->count()], ['Reviews', $reviewCount], ['Rating', $average ? number_format($average, 1) : '-']] as [$label, $value])
                    <div class="rounded-lg bg-soft px-2 py-2">
                        <b class="block font-display text-lg font-bold">{{ $value }}</b>
                        <small class="text-xs text-muted">{{ $label }}</small>
                    </div>
                @endforeach
            </div>
            <div x-data="{ copied: false }" class="relative">
                <button type="button" class="rounded-md border-[1.5px] border-line px-4 py-2 text-sm font-semibold"
                    x-on:click="navigator.share?.({ title: document.title, url: location.href }) ?? navigator.clipboard.writeText(location.href); copied = true; setTimeout(() => copied = false, 2000)"
                    x-text="copied ? 'Link copied' : 'Share'"></button>
            </div>
        </section>

        @if ($profile->bio || !empty($profile->offerings))
            <section class="mt-8" aria-label="About">
                <h2 class="mb-2 font-display text-[22px] font-bold tracking-tight">About {{ $profile->business_name }}
                </h2>
                @if ($profile->bio)
                    <p class="max-w-[44em] text-muted">{{ $profile->bio }}</p>
                @endif
                @if (!empty($profile->offerings))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($profile->offerings as $offer)
                            <span
                                class="rounded-full border border-line bg-surface px-3 py-1 text-sm">{{ $offer }}</span>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        <section class="mt-10" aria-label="Packages">
            <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">Trips by {{ $profile->business_name }}
            </h2>
            @if ($packages->isEmpty())
                <p class="text-muted">No trips are listed right now.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($packages as $package)
                        <x-trip-card :package="$package" wire:key="package-{{ $package->id }}" />
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-10" aria-label="Reviews">
            <h2 class="mb-3 font-display text-[22px] font-bold tracking-tight">Reviews</h2>
            @forelse ($reviews as $review)
                <div wire:key="review-{{ $review->id }}"
                    class="mb-2.5 rounded-xl border border-line bg-surface p-3.5">
                    <b><span aria-hidden="true">&#9733;</span> {{ number_format($review->rating, 1) }}</b>
                    <small class="text-muted">{{ str($review->user->name ?: 'Traveler')->before(' ') }} on <a
                            href="{{ route('trips.show', $review->package) }}" wire:navigate
                            class="underline">{{ $review->package->title }}</a></small>
                    <p class="mt-1.5">{{ $review->body }}</p>
                </div>
            @empty
                <p class="text-muted">No reviews yet.</p>
            @endforelse
        </section>
    </main>
</div>
