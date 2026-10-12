<div>
    <x-site.header />

    <main class="mx-auto max-w-6xl px-4 pb-16 pt-6">
        <section
            class="flex flex-col gap-4 rounded-xl border border-line bg-surface p-4 min-[760px]:flex-row min-[760px]:items-center">
            <x-avatar :user="$user" class="size-20 text-2xl" />
            <div class="min-w-0 flex-1">
                <h1 class="font-display text-[26px] font-bold leading-tight tracking-tight">
                    {{ $profile?->business_name ?? 'Your business' }}</h1>
                <p class="text-sm text-muted">{{ $profile?->county }} &middot; Operator</p>
            </div>
            <div class="grid grid-cols-3 gap-2.5 text-center min-[760px]:w-72">
                @foreach ([['Packages', $packages->count()], ['Reviews', $reviewCount], ['Rating', $average ? number_format($average, 1) : '-']] as [$label, $value])
                    <div class="rounded-lg bg-soft px-2 py-2">
                        <b class="block font-display text-lg font-bold">{{ $value }}</b>
                        <small class="text-xs text-muted">{{ $label }}</small>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2">
                @if ($profile)
                    <a href="{{ route('operators.show', $profile) }}" wire:navigate
                        class="rounded-md border-[1.5px] border-line px-4 py-2 text-sm font-semibold">View public
                        page</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-md border-[1.5px] border-line px-4 py-2 text-sm font-semibold text-muted">Sign
                        out</button>
                </form>
            </div>
        </section>

        <div class="mt-6 grid gap-6 min-[900px]:grid-cols-[minmax(0,420px)_1fr]">
            <form wire:submit="save" class="rounded-xl border border-line bg-surface p-4" novalidate>
                <h2 class="mb-3 font-display text-lg font-bold">Business profile</h2>

                <label for="businessName" class="auth-label">Business name</label>
                <div class="auth-field"><input id="businessName" type="text" wire:model="businessName"
                        autocomplete="organization" class="auth-input"></div>
                @error('businessName')
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

                <label for="bio" class="auth-label">About your business <span class="font-normal text-muted">(shown
                        on your public page)</span></label>
                <div class="auth-field">
                    <textarea id="bio" wire:model="bio" rows="4" maxlength="600"
                        placeholder="Tell travelers who you are and what makes your trips special." class="auth-input resize-none"></textarea>
                </div>
                @error('bio')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror

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

                <label for="logo" class="auth-label">Logo or photo</label>
                <div class="mb-3 flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt=""
                            class="size-14 rounded-full object-cover">
                    @else
                        <x-avatar :user="$user" class="size-14" />
                    @endif
                    <input id="logo" type="file" accept="image/*" wire:model="logo"
                        class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-soft file:px-3 file:py-2 file:text-sm file:font-semibold">
                </div>
                @error('logo')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror

                <button type="submit" class="auth-btn" wire:loading.attr="disabled" wire:target="save,logo">Save
                    changes</button>
                @if ($saved)
                    <p class="mt-2 text-sm font-semibold text-ok" role="status">Saved.</p>
                @endif
            </form>

            <section aria-label="Your packages">
                <h2 class="mb-3 font-display text-lg font-bold">Your packages</h2>
                @forelse ($packages as $package)
                    <div wire:key="pkg-{{ $package->id }}"
                        class="mb-2.5 flex items-center justify-between gap-3 rounded-xl border border-line bg-surface p-3.5">
                        <div class="min-w-0">
                            <a href="{{ route('trips.show', $package) }}" wire:navigate
                                class="font-display text-base font-bold leading-tight hover:underline">{{ $package->title }}</a>
                            <p class="text-sm text-muted">
                                {{ $package->place }} &middot; KES {{ number_format($package->price) }} &middot;
                                @if ($package->reviews_count)
                                    &#9733; {{ number_format($package->reviews_avg_rating, 1) }}
                                    ({{ $package->reviews_count }})
                                @else
                                    No reviews yet
                                @endif
                            </p>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-soft' => $package->status === 'published',
                            'bg-line text-muted' => $package->status !== 'published',
                        ])>{{ ucfirst($package->status) }}</span>
                    </div>
                @empty
                    <div class="rounded-xl border border-line bg-surface p-6 text-center">
                        <p class="font-semibold">You have not added a package yet.</p>
                        <p class="mt-1 text-sm text-muted">The form to add one is the next thing we are building.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </main>
</div>
