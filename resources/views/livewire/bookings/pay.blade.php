<div x-data="{
    end: {{ !$isBalance && $booking->expires_at ? $booking->expires_at->getTimestamp() * 1000 : 0 }},
    left: 0,
    cur: 'KES',
    rates: @js(config('safarihub.payments.display_rates')),
    init() {
        if (!this.end) return;
        this.tick();
        setInterval(() => this.tick(), 1000);
    },
    tick() {
        const was = this.left;
        this.left = Math.max(0, Math.floor((this.end - Date.now()) / 1000));
        if (was > 0 && this.left === 0) $wire.$refresh();
    },
    get clock() {
        return Math.floor(this.left / 60) + ':' + String(this.left % 60).padStart(2, '0');
    },
    approx(kes) {
        return this.cur === 'KES' ? '' : '\u2248 ' + this.cur + ' ' + Math.round(kes * this.rates[this.cur]).toLocaleString('en-US');
    },
}">
    <x-site.header />

    <div
        class="mx-auto grid max-w-5xl gap-5 px-4 pb-14 pt-4 min-[900px]:grid-cols-[minmax(0,1fr)_340px] min-[900px]:items-start">
        <main>
            <h1 class="mb-3 mt-1 font-display text-[clamp(24px,3.4vw,32px)] font-bold leading-[1.1] tracking-tight">
                {{ $isBalance ? 'Pay the balance' : 'Complete your booking' }}
            </h1>

            @if ($expired)
                <div class="rounded-xl border border-line bg-surface p-5">
                    <h2 class="font-display text-xl font-bold tracking-tight">Your seats were released</h2>
                    <p class="mt-1 text-muted">The {{ config('safarihub.booking.hold_minutes') }} minute hold ended
                        before payment was made. We can hold the same seats again if they are still free.</p>
                    @if ($error)
                        <p class="auth-error mt-3" role="alert">{{ $error }}</p>
                    @endif
                    <button type="button" wire:click="refreshHold" class="auth-btn mt-3">Hold my seats again</button>
                    <a href="{{ route('trips.show', $booking->package) }}" wire:navigate
                        class="auth-link mt-2 block">Back to the trip</a>
                </div>
            @else
                @unless ($isBalance)
                    <div x-show="end"
                        class="mb-3.5 flex items-center gap-2 rounded-lg bg-warn/15 px-3 py-2 text-sm text-warn">
                        <span aria-hidden="true">&#9201;</span>
                        <span>We are holding your seats for <b x-text="clock"></b></span>
                    </div>
                @endunless

                <div
                    class="mb-3.5 flex justify-between gap-2 rounded-xl border border-line bg-surface px-3 py-2.5 text-sm min-[900px]:hidden">
                    <span>{{ $booking->package->title }} &middot; {{ $booking->departure->rangeLabel() }} &middot;
                        {{ $booking->travelers }} {{ str('traveler')->plural($booking->travelers) }}</span>
                    <b>KES {{ number_format($booking->total) }}</b>
                </div>

                @if ($error)
                    <div class="mb-3.5 rounded-lg bg-soft px-3 py-2.5 text-sm" role="alert">{{ $error }}</div>
                @endif

                <section class="mb-3.5 rounded-xl border border-line bg-surface p-4">
                    <h2 class="font-display text-lg font-bold tracking-tight">1. How much do you want to pay now?</h2>
                    @foreach ($options as $key => $option)
                        <button type="button" wire:key="mode-{{ $key }}"
                            wire:click="setMode('{{ $key }}')" @class([
                                'mt-2 flex w-full items-start gap-3 rounded-lg border-[1.5px] p-3 text-left',
                                'border-accent bg-soft' => $mode === $key,
                                'border-line' => $mode !== $key,
                            ])>
                            <span @class([
                                'mt-0.5 size-[18px] shrink-0 rounded-full',
                                'border-[5px] border-accent' => $mode === $key,
                                'border-2 border-line' => $mode !== $key,
                            ])></span>
                            <span>
                                <b class="font-semibold">{{ $option['label'] }}</b>
                                <small class="block text-[13px] text-muted">{{ $option['hint'] }}</small>
                            </span>
                            <span class="ml-auto whitespace-nowrap font-display text-base font-bold">KES
                                {{ number_format($option['amount']) }}</span>
                        </button>
                    @endforeach
                </section>

                <section class="mb-3.5 rounded-xl border border-line bg-surface p-4">
                    <h2 class="font-display text-lg font-bold tracking-tight">2. Choose how to pay</h2>
                    <p class="mb-1 text-sm text-muted">Pick the option that works where you live.</p>

                    @foreach ($methods as $key => [$name, $description, $help])
                        <button type="button" wire:key="method-{{ $key }}"
                            wire:click="setMethod('{{ $key }}')" @class([
                                'mt-2 flex w-full items-start gap-3 rounded-lg border-[1.5px] p-3 text-left',
                                'border-accent bg-soft' => $method === $key,
                                'border-line' => $method !== $key,
                            ])>
                            <span @class([
                                'mt-0.5 size-[18px] shrink-0 rounded-full',
                                'border-[5px] border-accent' => $method === $key,
                                'border-2 border-line' => $method !== $key,
                            ])></span>
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-soft">
                                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    @if ($key === 'mpesa')
                                        <rect x="7" y="2" width="10" height="20" rx="2" />
                                        <path d="M11 18h2" />
                                    @elseif ($key === 'apple_pay')
                                        <path d="M3 7h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                        <path d="M3 7l12-4v4" />
                                    @else
                                        <rect x="2" y="5" width="20" height="14" rx="2" />
                                        <path d="M2 10h20" />
                                    @endif
                                </svg>
                            </span>
                            <span>
                                <b class="font-semibold">{{ $name }}</b>
                                <small class="block text-[13px] text-muted">{{ $description }}</small>
                            </span>
                        </button>
                    @endforeach

                    <div class="mt-2.5 rounded-lg bg-soft px-3 py-2.5 text-sm text-muted">
                        {{ $methods[$method][2] ?? '' }}</div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5 text-sm text-muted">
                        <span>Show prices in:</span>
                        @foreach (['KES', 'USD', 'EUR', 'GBP'] as $currency)
                            <button type="button" x-on:click="cur = '{{ $currency }}'"
                                x-bind:class="cur === '{{ $currency }}' ? 'border-accent bg-soft text-ink' : 'border-line'"
                                class="rounded-full border-[1.5px] px-3 py-[3px] text-[13px] font-semibold">{{ $currency }}</button>
                        @endforeach
                    </div>
                    <p class="mt-2 text-[13px] text-muted">You are charged in Kenyan shillings (KES). Your bank converts
                        it to your own currency. Other currencies are only a guide.</p>
                </section>

                <label class="mb-1.5 mt-3.5 flex items-start gap-2.5 text-sm">
                    <input type="checkbox" wire:model="agree" class="mt-1 size-[18px] accent-accent">
                    <span>I agree to the cancellation terms and the terms of service.</span>
                </label>
                @error('agree')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror

                <button type="button" wire:click="pay" wire:loading.attr="disabled" wire:target="pay"
                    class="auth-btn mt-2.5 !py-3.5 !text-[17px]">
                    <span wire:loading.remove wire:target="pay">Pay KES {{ number_format($amount) }}</span>
                    <span wire:loading wire:target="pay">Opening secure checkout...</span>
                </button>
                <p x-show="cur !== 'KES'" x-cloak class="mt-1.5 text-center text-sm text-muted"
                    x-text="approx({{ $amount }})"></p>
                <p class="auth-fine">Payments are processed securely by Paystack. SafariHub never sees your card
                    details.</p>
            @endif
        </main>

        <aside class="min-[900px]:sticky min-[900px]:top-20">
            <section class="mb-3.5 rounded-xl border border-line bg-surface p-4">
                <h2 class="font-display text-lg font-bold tracking-tight">Your trip</h2>
                <p class="mb-2 text-sm text-muted">
                    {{ $booking->package->title }}<br>{{ $booking->package->user->operatorProfile?->business_name ?? $booking->package->user->name }}
                </p>
                <div class="flex justify-between gap-2 py-1 text-[15px]"><span class="text-muted">Dates</span><b
                        class="font-semibold">{{ $booking->departure->rangeLabel() }}</b></div>
                <div class="flex justify-between gap-2 py-1 text-[15px]"><span class="text-muted">Travelers</span><b
                        class="font-semibold">{{ $booking->travelers }}</b></div>
                <div class="my-2 border-t border-line"></div>
                <div class="flex justify-between gap-2 py-1 text-[15px]"><span class="text-muted">KES
                        {{ number_format($booking->package->price) }} &times; {{ $booking->travelers }}</span><b
                        class="font-semibold">KES {{ number_format($booking->total) }}</b></div>
                <p x-show="cur !== 'KES'" x-cloak class="text-right text-[13px] text-muted"
                    x-text="approx({{ $booking->total }})"></p>
                @if ($paid > 0)
                    <div class="flex justify-between gap-2 py-1 text-[15px]"><span class="text-muted">Already
                            paid</span><b class="font-semibold">KES {{ number_format($paid) }}</b></div>
                @endif
                <div class="my-2 border-t border-line"></div>
                <div class="flex justify-between gap-2 py-1 text-[15px]"><span class="text-muted">To pay today</span><b
                        class="font-semibold">KES {{ number_format($amount) }}</b></div>
                <p x-show="cur !== 'KES'" x-cloak class="text-right text-[13px] text-muted"
                    x-text="approx({{ $amount }})"></p>
                @if (!$isBalance && $mode === 'deposit')
                    <div class="flex justify-between gap-2 py-1 text-[15px]"><span class="text-muted">Balance due
                            {{ $booking->departure->balanceDueOn()->format('j M') }}</span><b
                            class="font-semibold">KES {{ number_format($booking->total - $booking->deposit) }}</b>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-line bg-surface p-4">
                <h2 class="font-display text-lg font-bold tracking-tight">What happens next</h2>
                <ul class="mt-1 grid gap-1 text-sm text-muted">
                    <li>You pay on a secure page.</li>
                    <li>Your booking is confirmed straight away.</li>
                    <li>The operator's contact details and the meeting point unlock.</li>
                    <li>You get a receipt by email.</li>
                </ul>
            </section>
        </aside>
    </div>
</div>
