@props(['package'])
@php($photo = $package->photos->first())
<article class="relative overflow-hidden rounded-xl border border-line bg-surface transition-shadow hover:shadow-md">
    <div class="relative h-40 bg-soft">
        @if ($photo)
            <img src="{{ $photo->url }}" alt="{{ $photo->alt }}" loading="lazy"
                style="object-position: {{ $photo->focus }}" class="h-full w-full object-cover">
        @endif
        <span
            class="absolute inset-x-0 bottom-0 bg-black/45 px-3 py-1.5 font-display text-[15px] font-bold text-white">{{ $package->place }}</span>
    </div>
    <div class="p-3.5">
        <h3 class="font-display text-lg font-bold leading-tight tracking-tight">
            <a href="{{ route('trips.show', $package) }}" wire:navigate
                class="after:absolute after:inset-0">{{ $package->title }}</a>
        </h3>
        <p class="mt-0.5 text-sm text-muted">{{ $package->durationLabel() }}</p>
        <div class="mt-3 flex items-end justify-between">
            <p class="text-sm">
                @if ($package->reviews_count)
                    <span aria-hidden="true">&#9733;</span> <b
                        class="font-semibold">{{ number_format($package->reviews_avg_rating, 1) }}</b> <span
                        class="text-muted">({{ $package->reviews_count }})</span>
                @else
                    <span class="text-muted">New</span>
                @endif
            </p>
            <p class="text-right text-sm text-muted">From <b class="font-display text-lg font-bold text-ink">KES
                    {{ number_format($package->price) }}</b></p>
        </div>
    </div>
</article>
