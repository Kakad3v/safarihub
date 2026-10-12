@php
    $package = $booking->package;
    $operator = $package->user;
    $left = $booking->total - $paid;
@endphp

<div @if ($processing) wire:poll.3s="refreshStatus" @endif>
    <x-site.header />

    <main class="mx-auto max-w-xl px-4 pb-16 pt-8">
        @if ($confirmed)
            <div
                class="mb-3 grid size-[52px] place-items-center rounded-full bg-ok font-display text-[26px] font-bold text-white">
                &#10003;</div>
            <h1 class="font-display text-[clamp(26px,4vw,34px)] font-bold leading-tight tracking-tight">You are booked
            </h1>
            <p class="mt-1 text-muted">Booking
                {{ $booking->reference }}{{ auth()->user()->email ? '. A receipt is on its way to your email.' : '.' }}
            </p>

            <section class="mt-5 rounded-xl border border-line bg-surface p-4">
                @foreach ([['Trip', $package->title], ['Dates', $booking->departure->rangeLabel()], ['Travelers', $booking->travelers], ['Paid so far', 'KES ' . number_format($paid)], ['Paid with', $lastPayment?->channel === 'mobile_money' ? 'M-Pesa' : ($lastPayment?->channel ? ucfirst(str_replace('_', ' ', $lastPayment->channel)) : 'Paystack')], [$left > 0 ? 'Balance due ' . $booking->departure->balanceDueOn()->format('j M') : 'Balance', $left > 0 ? 'KES ' . number_format($left) : 'Paid in full']] as [$label, $value])
                    <div class="flex justify-between gap-3 py-1 text-[15px]"><span
                            class="text-muted">{{ $label }}</span><b
                            class="text-right font-semibold">{{ $value }}</b></div>
                @endforeach
            </section>

            <section class="mt-4 rounded-xl border-[1.5px] border-ok p-4">
                <h2 class="font-display text-base font-bold">Your operator</h2>
                <p class="mt-1">{{ $operator->operatorProfile?->business_name ?? $operator->name }}</p>
                <p class="text-muted">{{ $operator->phone ?: $operator->email }}</p>
                @if ($package->meeting_details || $package->pickup_location)
                    <p class="mt-2">Meet at
                        {{ $package->meeting_details ?: trim($package->pickup_location . ($package->pickup_time ? ', ' . $package->pickup_time : '')) }}
                    </p>
                @endif
                <p class="mt-2 text-[13px] text-muted">Shown now that your payment has gone through.</p>
            </section>

            @if ($left > 0)
                <a href="{{ route('bookings.pay', $booking) }}" wire:navigate
                    class="auth-btn mt-4 block text-center">Pay the balance of KES {{ number_format($left) }}</a>
            @endif
            <a href="{{ route('me') }}" wire:navigate
                class="mt-2.5 block rounded-md border-[1.5px] border-line px-3 py-3 text-center font-semibold">View my
                trips</a>
            <a href="{{ route('trips.show', $package) }}" wire:navigate class="auth-link mt-2 block text-center">Back
                to the trip</a>
        @elseif ($processing)
            <div class="rounded-xl border border-line bg-surface p-6 text-center">
                <div
                    class="mx-auto mb-3 size-8 animate-spin rounded-full border-[3px] border-line border-t-accent motion-reduce:animate-none">
                </div>
                <h1 class="font-display text-xl font-bold tracking-tight">Confirming your payment</h1>
                <p class="mt-1 text-muted">This usually takes a few seconds. You can leave this page open.</p>
            </div>
        @else
            <div class="rounded-xl border border-line bg-surface p-6">
                <h1 class="font-display text-xl font-bold tracking-tight">Your booking is not paid yet</h1>
                <p class="mt-1 text-muted">{{ $package->title }}, {{ $booking->departure->rangeLabel() }}.</p>
                <a href="{{ route('bookings.pay', $booking) }}" wire:navigate
                    class="auth-btn mt-3 block text-center">Continue to payment</a>
            </div>
        @endif
    </main>
</div>
