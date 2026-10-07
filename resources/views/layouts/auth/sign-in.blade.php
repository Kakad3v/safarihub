<div class="grid min-h-screen grid-cols-1 min-[900px]:grid-cols-[1.2fr_1fr]" x-data
    x-effect="document.documentElement.dataset.role = $wire.role">
    <main
        class="order-1 flex flex-col items-center px-4 pb-7 pt-5 min-[900px]:order-2 min-[900px]:justify-center min-[900px]:px-10 min-[900px]:py-7">
        <div class="mb-4 flex w-full max-w-[380px] items-center justify-between">
            <span class="font-display text-xl font-bold tracking-tight">SafariHub</span>
            <div class="flex items-center gap-2">
                @if ($step !== 3)
                    <div class="inline-flex rounded-full border border-line bg-surface p-[3px]" role="group"
                        aria-label="Choose who is signing in">
                        @foreach (['traveler' => 'Traveler', 'operator' => 'Operator'] as $key => $label)
                            <button type="button" wire:click="setRole('{{ $key }}')"
                                aria-pressed="{{ $role === $key ? 'true' : 'false' }}"
                                @class([
                                    'rounded-full px-3.5 py-1.5 text-[13px] font-semibold',
                                    'bg-accent text-on-accent' => $role === $key,
                                    'text-muted' => $role !== $key,
                                ])>{{ $label }}</button>
                        @endforeach
                    </div>
                @endif
                <x-auth.theme-toggle />
            </div>
        </div>

        <div class="w-full max-w-[380px] rounded-xl border border-line bg-surface px-[18px] pb-[18px] pt-5">
            @if ($step === 1)
                <form wire:submit="sendCode" novalidate>
                    <h1 class="mb-1.5 font-display text-2xl font-bold leading-tight tracking-tight">
                        {{ $role === 'operator' ? 'Sell your tours on SafariHub' : 'Sign in to SafariHub' }}
                    </h1>
                    <p class="mb-3.5 text-sm leading-snug text-muted">
                        {{ $role === 'operator'
                            ? 'Use the phone or email your customers can reach you on. We will send a code.'
                            : 'Enter your phone or email. We will send a code. New here? We will create your account.' }}
                    </p>

                    @error('link')
                        <p class="auth-error" role="alert">{{ $message }}</p>
                    @enderror

                    <div class="mb-2.5 flex gap-1.5 rounded-lg bg-soft p-1" role="tablist">
                        @foreach (['phone' => 'Phone', 'email' => 'Email'] as $key => $label)
                            <button type="button" role="tab" wire:click="setMethod('{{ $key }}')"
                                aria-selected="{{ $method === $key ? 'true' : 'false' }}"
                                @class([
                                    'flex-1 rounded-[5px] py-[7px] text-sm font-semibold',
                                    'bg-surface text-ink' => $method === $key,
                                    'text-muted' => $method !== $key,
                                ])>{{ $label }}</button>
                        @endforeach
                    </div>

                    <label for="identifier"
                        class="auth-label">{{ $method === 'phone' ? 'Phone number' : 'Email address' }}</label>
                    <div class="auth-field">
                        @if ($method === 'phone')
                            <span class="pl-3 font-medium text-muted">+254</span>
                            <input id="identifier" type="tel" inputmode="tel" autocomplete="tel-national"
                                placeholder="712 345 678" wire:model="identifier" class="auth-input">
                        @else
                            <input id="identifier" type="email" inputmode="email" autocomplete="email"
                                placeholder="you@example.com" wire:model="identifier" class="auth-input">
                        @endif
                    </div>
                    @error('identifier')
                        <p class="auth-error" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="auth-btn" wire:loading.attr="disabled" wire:target="sendCode">Send
                        code</button>

                    <button type="button" wire:click="setRole('{{ $role === 'operator' ? 'traveler' : 'operator' }}')"
                        class="auth-link mt-1.5">
                        {{ $role === 'operator' ? 'Looking to book a trip? Sign in as a traveler' : 'Run tours? Sign in as an operator' }}
                    </button>

                    <p class="auth-fine">By continuing you agree to the Terms and Privacy Policy. We only message you
                        about sign-in and bookings.</p>
                </form>
            @elseif ($step === 2)
                <form wire:submit="verify" x-data="{
                    digits: Array(6).fill(''),
                    seconds: 30,
                    timer: null,
                    init() {
                        this.start();
                        this.$nextTick(() => this.$refs.d0.focus());
                    },
                    start() {
                        this.seconds = 30;
                        clearInterval(this.timer);
                        this.timer = setInterval(() => {
                            if (--this.seconds <= 0) clearInterval(this.timer);
                        }, 1000);
                    },
                    sync() {
                        $wire.code = this.digits.join('');
                    },
                    type(i, e) {
                        const v = e.target.value.replace(/\D/g, '').slice(-1);
                        this.digits[i] = v;
                        e.target.value = v;
                        this.sync();
                        if (v && i < 5) this.$refs['d' + (i + 1)].focus();
                    },
                    back(i) {
                        if (!this.digits[i] && i > 0) this.$refs['d' + (i - 1)].focus();
                    },
                    paste(e) {
                        const t = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                        if (!t) return;
                        e.preventDefault();
                        this.digits = t.padEnd(6, ' ').split('').map(c => c.trim());
                        this.sync();
                        this.$refs['d' + Math.min(t.length, 5)].focus();
                    },
                    reset() {
                        this.digits = Array(6).fill('');
                        this.start();
                        this.$refs.d0.focus();
                    },
                }" x-on:code-sent.window="reset()" novalidate>
                    <h1 class="mb-1.5 font-display text-2xl font-bold leading-tight tracking-tight">Enter your code</h1>
                    <p class="mb-3.5 text-sm leading-snug text-muted">We sent a 6-digit code to <strong
                            class="font-semibold text-ink">{{ $target }}</strong>.</p>

                    <div class="mb-2 flex gap-1.5" x-on:paste="paste($event)">
                        @for ($i = 0; $i < 6; $i++)
                            <input type="text" inputmode="numeric"
                                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                aria-label="Digit {{ $i + 1 }}" x-ref="d{{ $i }}"
                                x-bind:value="digits[{{ $i }}]"
                                x-on:input="type({{ $i }}, $event)"
                                x-on:keydown.backspace="back({{ $i }})"
                                class="w-full min-w-0 rounded-md border-[1.5px] border-line bg-surface py-2.5 text-center font-display text-xl font-bold text-ink focus:border-accent focus:outline-none">
                        @endfor
                    </div>

                    @if ($errors->first('code') ?: $errors->first('identifier'))
                        <p class="auth-error" role="alert">
                            {{ $errors->first('code') ?: $errors->first('identifier') }}</p>
                    @endif

                    <div class="mb-2.5 flex items-center justify-between gap-2">
                        <button type="button" wire:click="resend" x-bind:disabled="seconds > 0"
                            x-text="seconds > 0 ? 'Resend code in 0:' + String(seconds).padStart(2, '0') : 'Resend code'"
                            x-bind:class="seconds > 0 ? 'text-muted' : 'font-semibold text-accent'"
                            class="text-sm"></button>
                        <button type="button" wire:click="changeIdentifier" class="auth-link">Change
                            {{ $method === 'phone' ? 'number' : 'email' }}</button>
                    </div>

                    <button type="submit" class="auth-btn" wire:loading.attr="disabled" wire:target="verify">Verify and
                        continue</button>

                    @if ($method === 'phone')
                        <button type="button" wire:click="setMethod('email')" class="auth-link mt-1.5">Send the code by
                            email instead</button>
                    @endif

                    <p class="auth-fine">
                        Codes expire after 10 minutes.
                        {{ $method === 'email' ? 'The email also has a one-tap sign-in link. Check spam if it is not there.' : 'Not there yet? Resend when the timer ends.' }}
                    </p>
                </form>
            @else
                <form wire:submit="finish" novalidate>
                    @if ($role === 'operator')
                        <h1 class="mb-1.5 font-display text-2xl font-bold leading-tight tracking-tight">Set up your
                            business</h1>
                        <p class="mb-3.5 text-sm leading-snug text-muted">Shown only the first time.</p>

                        <label for="name" class="auth-label">Business name</label>
                        <div class="auth-field">
                            <input id="name" type="text" autocomplete="organization"
                                placeholder="Coastline Safaris" wire:model="name" class="auth-input">
                        </div>
                        @error('name')
                            <p class="auth-error" role="alert">{{ $message }}</p>
                        @enderror

                        <label for="county" class="auth-label">Based in</label>
                        <div class="auth-field">
                            <select id="county" wire:model="county" class="auth-input">
                                @foreach ($counties as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <span class="auth-label">What do you offer?</span>
                        <div class="mb-3 flex flex-wrap gap-2">
                            @foreach ($offerOptions as $option)
                                <label class="cursor-pointer">
                                    <input type="checkbox" value="{{ $option }}" wire:model="offers"
                                        class="peer sr-only">
                                    <span
                                        class="block rounded-full border-[1.5px] border-line bg-surface px-3 py-1.5 text-sm font-medium peer-checked:border-accent peer-checked:bg-soft peer-checked:font-semibold peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('offers')
                            <p class="auth-error" role="alert">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="auth-btn" wire:loading.attr="disabled"
                            wire:target="finish">Create operator account</button>
                        <p class="auth-fine">ID checks for payouts come later, after your first package is live.</p>
                    @else
                        <h1 class="mb-1.5 font-display text-2xl font-bold leading-tight tracking-tight">What should we
                            call you?</h1>
                        <p class="mb-3.5 text-sm leading-snug text-muted">Shown only the first time.</p>

                        <label for="name" class="auth-label">First name</label>
                        <div class="auth-field">
                            <input id="name" type="text" autocomplete="given-name" placeholder="Amina"
                                wire:model="name" class="auth-input">
                        </div>
                        @error('name')
                            <p class="auth-error" role="alert">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="auth-btn" wire:loading.attr="disabled"
                            wire:target="finish">Start exploring</button>
                    @endif
                </form>
            @endif
        </div>
    </main>

    <aside
        class="order-2 bg-panel px-5 pb-9 pt-7 text-panel-ink min-[900px]:sticky min-[900px]:top-0 min-[900px]:order-1 min-[900px]:flex min-[900px]:h-screen min-[900px]:flex-col min-[900px]:justify-center min-[900px]:overflow-y-auto min-[900px]:px-12 min-[900px]:py-8 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        aria-label="Popular trips and reviews">
        @if ($role === 'operator')
            @include('livewire.auth.partials.operator-panel')
        @else
            @include('livewire.auth.partials.traveler-panel')
        @endif
    </aside>
</div>
