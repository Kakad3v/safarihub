<div>
    <div class="h-40 bg-cover bg-center md:h-56"
        style="{{ $profile->cover_path ? 'background-image:url(' . Storage::url($profile->cover_path) . ')' : 'background:linear-gradient(120deg,var(--soft),var(--accent))' }}">
    </div>

    <section class="mx-auto max-w-5xl px-4">
        @if (session('status'))
            <div class="mt-4 rounded-lg border border-line bg-soft p-4 text-sm font-semibold">{{ session('status') }}
            </div>
        @endif

        @if ($owner && $profile->status !== 'verified')
            <div class="mt-4 rounded-lg border border-line bg-soft p-4 text-sm">
                <span class="font-semibold">Pending verification.</span> Only you can see this page until your account is
                approved.
            </div>
        @endif

        <div class="-mt-12 flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-end gap-4">
                @if ($profile->logo_path)
                    <img src="{{ Storage::url($profile->logo_path) }}" alt=""
                        class="h-24 w-24 shrink-0 rounded-lg border-4 border-canvas bg-surface object-cover">
                @else
                    <div
                        class="grid h-24 w-24 shrink-0 place-items-center rounded-lg border-4 border-canvas bg-accent font-display text-4xl font-bold text-on-accent">
                        {{ Str::substr($profile->business_name, 0, 1) }}
                    </div>
                @endif
                <div class="pb-1">
                    <h1 class="font-display text-2xl font-bold leading-tight md:text-3xl">{{ $profile->business_name }}
                    </h1>
                    @if ($profile->location)
                        <p class="text-sm text-muted">{{ $profile->location }}</p>
                    @endif
                </div>
            </div>

            <div class="flex gap-2 pb-1" x-data="{ copied: false }">
                @if ($owner)
                    <a href="{{ route('operator.packages.create') }}" wire:navigate class="btn-accent">Add package</a>
                @else
                    <a href="{{ auth()->check() ? '#' : route('login') }}" class="btn-accent">Message</a>
                @endif
                <button type="button" class="btn-line"
                    @click="navigator.share ? navigator.share({ title: @js($profile->business_name), url: location.href }) : (navigator.clipboard.writeText(location.href), copied = true, setTimeout(() => copied = false, 2000))">
                    <span x-text="copied ? 'Link copied' : 'Share'">Share</span>
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
            @if ($profile->status === 'verified')
                <span class="rounded-full bg-soft px-3 py-1 text-accent">✓ Verified operator</span>
            @endif
            @if ($profile->founded_year)
                <span class="rounded-full border border-line bg-surface px-3 py-1">Since
                    {{ $profile->founded_year }}</span>
            @endif
        </div>

        <div class="mt-5 grid grid-cols-3 gap-3">
            <div class="rounded-lg border border-line bg-surface p-4">
                <p class="text-xl font-bold">
                    @if ($rating)
                        <span class="text-accent">★</span> {{ number_format($rating, 1) }}
                    @else
                        New
                    @endif
                </p>
                <p class="mt-1 text-xs text-muted">{{ $reviews }} {{ Str::plural('review', $reviews) }}</p>
            </div>
            <div class="rounded-lg border border-line bg-surface p-4">
                <p class="text-xl font-bold">{{ $packages->where('type', \App\Enums\PackageType::Trip)->count() }}</p>
                <p class="mt-1 text-xs text-muted">Trips</p>
            </div>
            <div class="rounded-lg border border-line bg-surface p-4">
                <p class="text-xl font-bold">
                    {{ $packages->where('type', \App\Enums\PackageType::Experience)->count() }}</p>
                <p class="mt-1 text-xs text-muted">Experiences</p>
            </div>
        </div>

        @if ($profile->about)
            <p class="mt-5 max-w-2xl leading-relaxed text-muted">{{ $profile->about }}</p>
        @endif

        <div class="mt-8 flex gap-8 border-b border-line">
            @foreach (['trips' => 'Trips & experiences', 'reviews' => 'Reviews'] as $key => $label)
                <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    class="border-b-2 pb-3 font-semibold {{ $tab === $key ? 'border-accent' : 'border-transparent text-muted' }}">{{ $label }}</button>
            @endforeach
        </div>

        @if ($tab === 'trips')
            <div class="grid gap-5 py-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($packages as $package)
                    <article wire:key="p-{{ $package->id }}"
                        class="relative overflow-hidden rounded-lg border border-line bg-surface transition hover:shadow-lg">
                        <div class="relative aspect-[4/3] bg-soft">
                            @if ($package->cover_url)
                                <img src="{{ $package->cover_url }}" alt="" loading="lazy"
                                    class="h-full w-full object-cover">
                            @endif
                            <span
                                class="absolute left-3 top-3 rounded-full bg-surface px-3 py-1 text-xs font-semibold">{{ $package->type->label() }}</span>
                            @if ($package->status === \App\Enums\PackageStatus::Draft)
                                <span
                                    class="absolute right-3 top-3 rounded-full bg-ink px-3 py-1 text-xs font-semibold text-canvas">Draft</span>
                            @endif
                            <p
                                class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-4 pt-10 font-display text-lg font-bold text-white">
                                {{ $package->destination->name }}</p>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold leading-snug">
                                <a href="{{ route('packages.show', $package) }}" wire:navigate
                                    class="after:absolute after:inset-0">{{ $package->title }}</a>
                            </h3>
                            <p class="mt-1 text-sm text-muted">{{ $package->duration_label }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-sm text-muted">From <span class="text-lg font-bold text-ink">KES
                                        {{ number_format($package->price) }}</span></p>
                                @if ($package->reviews_count)
                                    <p class="text-sm font-medium"><span class="text-accent">★</span>
                                        {{ number_format($package->rating_avg, 1) }}</p>
                                @endif
                            </div>
                        </div>
                        @if ($owner)
                            <a href="{{ route('operator.packages.edit', $package) }}" wire:navigate
                                class="absolute bottom-4 right-4 z-10 rounded-md border border-line bg-surface px-3 py-1.5 text-sm font-semibold">Edit</a>
                        @endif
                    </article>
                @empty
                    <p class="col-span-full rounded-lg border border-line bg-surface py-12 text-center text-muted">
                        {{ $owner ? 'No packages yet. Add your first trip or experience.' : 'No trips or experiences listed yet.' }}
                    </p>
                @endforelse
            </div>
        @else
            <div class="py-6">
                <p class="rounded-lg border border-line bg-surface py-12 text-center text-muted">
                    Reviews appear here after travelers complete a trip.
                </p>
            </div>
        @endif
    </section>

    <div class="pb-16"></div>
</div>
