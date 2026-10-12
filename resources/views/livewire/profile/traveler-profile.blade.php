<div>
    <x-site.header />

    <main class="mx-auto max-w-6xl px-4 pb-16 pt-6">
        <section
            class="flex flex-col gap-4 rounded-xl border border-line bg-surface p-4 min-[760px]:flex-row min-[760px]:items-center">
            <x-avatar :user="$user" class="size-20 text-2xl" />
            <div class="min-w-0 flex-1">
                <h1 class="font-display text-[26px] font-bold leading-tight tracking-tight">
                    {{ $user->name ?: 'Your profile' }}</h1>
                <p class="text-sm text-muted">{{ $user->country ?: 'Traveler' }} &middot; Joined
                    {{ $user->created_at->format('F Y') }}</p>
            </div>
            <div class="grid grid-cols-3 gap-2.5 text-center min-[760px]:w-72">
                @foreach ([['Trips', $bookings->count()], ['Memories', $memories->count()], ['Reviews', $reviewCount]] as [$label, $value])
                    <div class="rounded-lg bg-soft px-2 py-2">
                        <b class="block font-display text-lg font-bold">{{ $value }}</b>
                        <small class="text-xs text-muted">{{ $label }}</small>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2">
                <button type="button" wire:click="$toggle('editing')"
                    class="rounded-md border-[1.5px] border-line px-4 py-2 text-sm font-semibold">{{ $editing ? 'Cancel' : 'Edit profile' }}</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-md border-[1.5px] border-line px-4 py-2 text-sm font-semibold text-muted">Sign
                        out</button>
                </form>
            </div>
        </section>

        @if ($editing)
            <form wire:submit="saveProfile" class="mt-4 max-w-md rounded-xl border border-line bg-surface p-4"
                novalidate>
                <h2 class="mb-3 font-display text-lg font-bold">Edit your profile</h2>

                <label for="name" class="auth-label">First name</label>
                <div class="auth-field"><input id="name" type="text" wire:model="name" autocomplete="given-name"
                        class="auth-input"></div>
                @error('name')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror

                <label for="country" class="auth-label">Where are you from? <span
                        class="font-normal text-muted">(optional)</span></label>
                <div class="auth-field"><input id="country" type="text" wire:model="country" placeholder="Kenya"
                        class="auth-input"></div>

                <label for="avatar" class="auth-label">Profile photo</label>
                <div class="mb-3 flex items-center gap-3">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" alt=""
                            class="size-14 rounded-full object-cover">
                    @else
                        <x-avatar :user="$user" class="size-14" />
                    @endif
                    <input id="avatar" type="file" accept="image/*" wire:model="avatar"
                        class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-soft file:px-3 file:py-2 file:text-sm file:font-semibold">
                </div>
                @error('avatar')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror

                <button type="submit" class="auth-btn" wire:loading.attr="disabled"
                    wire:target="saveProfile,avatar">Save changes</button>
            </form>
        @endif

        <section class="mt-10" aria-label="Memories">
            <h2 class="font-display text-2xl font-bold tracking-tight">Your memories</h2>
            <p class="mt-1 text-sm text-muted">Photos from your trips. They are private until you choose to share one.
            </p>

            <form wire:submit="addMemory"
                class="mt-4 grid gap-3 rounded-xl border border-line bg-surface p-4 min-[760px]:grid-cols-[auto_1fr] min-[760px]:items-start"
                novalidate>
                <div class="flex items-center gap-3">
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="size-20 rounded-lg object-cover">
                    @endif
                    <div>
                        <label for="photo" class="auth-label">Add a photo</label>
                        <input id="photo" type="file" accept="image/*" wire:model="photo"
                            class="block w-full max-w-[16rem] text-sm file:mr-3 file:rounded-md file:border-0 file:bg-soft file:px-3 file:py-2 file:text-sm file:font-semibold">
                        <div wire:loading wire:target="photo" class="mt-1 text-sm text-muted">Uploading...</div>
                    </div>
                </div>

                <div class="grid gap-3 min-[560px]:grid-cols-[1fr_auto] min-[560px]:items-end">
                    <div>
                        <label for="caption" class="auth-label">Caption <span
                                class="font-normal text-muted">(optional)</span></label>
                        <div class="auth-field !mb-0"><input id="caption" type="text" wire:model="caption"
                                maxlength="200" placeholder="Sunrise over the Mara" class="auth-input"></div>
                        @if ($tripOptions->isNotEmpty())
                            <label for="trip" class="auth-label mt-2.5">From which trip? <span
                                    class="font-normal text-muted">(optional)</span></label>
                            <div class="auth-field !mb-0">
                                <select id="trip" wire:model="packageId" class="auth-input">
                                    <option value="">None</option>
                                    @foreach ($tripOptions as $id => $title)
                                        <option value="{{ $id }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <button type="submit"
                        class="rounded-md bg-accent px-5 py-3 font-semibold text-on-accent disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="photo,addMemory">Add memory</button>
                </div>

                @error('photo')
                    <p class="auth-error min-[760px]:col-span-2" role="alert">{{ $message }}</p>
                @enderror
                @error('caption')
                    <p class="auth-error min-[760px]:col-span-2" role="alert">{{ $message }}</p>
                @enderror
            </form>

            @if ($memories->isEmpty())
                <div class="mt-4 rounded-xl border border-line bg-surface p-6 text-center">
                    <p class="font-semibold">Your memories will appear here.</p>
                    <p class="mt-1 text-sm text-muted">Add the photos from your trips. Nobody else can see them unless
                        you share.</p>
                </div>
            @else
                <div class="mt-4 grid grid-cols-2 gap-2 min-[640px]:grid-cols-3 min-[900px]:grid-cols-4">
                    @foreach ($memories as $memory)
                        <button type="button" wire:key="memory-{{ $memory->id }}"
                            wire:click="view({{ $memory->id }})"
                            class="relative aspect-square overflow-hidden rounded-xl bg-soft"
                            aria-label="Open photo{{ $memory->caption ? ': ' . $memory->caption : '' }}">
                            <img src="{{ $memory->url }}" alt="{{ $memory->caption }}" loading="lazy"
                                class="h-full w-full object-cover">
                            @if ($memory->share_token)
                                <span
                                    class="absolute left-2 top-2 rounded-full bg-black/60 px-2 py-0.5 text-[11px] font-semibold text-white">Shared</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-10" aria-label="Your trips">
            <h2 class="font-display text-2xl font-bold tracking-tight">Your trips</h2>
            @forelse ($bookings as $booking)
                @php($cover = $booking->package->photos->first())
                <div wire:key="booking-{{ $booking->id }}"
                    class="mt-3 flex gap-3 rounded-xl border border-line bg-surface p-3">
                    <div class="size-20 shrink-0 overflow-hidden rounded-lg bg-soft">
                        @if ($cover)
                            <img src="{{ $cover->url }}" alt=""
                                style="object-position: {{ $cover->focus }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('trips.show', $booking->package) }}" wire:navigate
                            class="font-display text-base font-bold leading-tight hover:underline">{{ $booking->package->title }}</a>
                        <p class="text-sm text-muted">{{ $booking->departure->rangeLabel() }} &middot;
                            {{ $booking->travelers }} {{ str('traveler')->plural($booking->travelers) }} &middot;
                            {{ $booking->reference }}</p>
                        <p class="mt-1.5 text-sm">
                            @if ($booking->status === 'pending')
                                <a href="{{ route('bookings.pay', $booking) }}" class="font-semibold text-accent">Pay
                                    your deposit of KES {{ number_format($booking->deposit) }}</a>
                            @elseif ($booking->status === 'deposit_paid')
                                <span class="rounded-full bg-soft px-2.5 py-0.5 text-xs font-semibold">Balance KES
                                    {{ number_format($booking->balance) }} due
                                    {{ $booking->balance_due_on->format('j M') }}</span>
                            @else
                                <span class="rounded-full bg-soft px-2.5 py-0.5 text-xs font-semibold">Paid in
                                    full</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="mt-3 rounded-xl border border-line bg-surface p-6 text-center">
                    <p class="font-semibold">No trips yet.</p>
                    <a href="{{ route('home') }}" wire:navigate class="auth-link mt-1">Browse trips</a>
                </div>
            @endforelse
        </section>
    </main>

    @if ($current = $memories->firstWhere('id', $viewing))
        <div wire:key="viewer"
            class="fixed inset-0 z-30 flex items-end justify-center bg-black/60 min-[640px]:items-center">
            <button type="button" wire:click="closeView" class="absolute inset-0" aria-label="Close"></button>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-t-2xl bg-surface p-4 min-[640px]:rounded-2xl"
                role="dialog" aria-modal="true" x-data="{ copied: false }"
                x-on:keydown.escape.window="$wire.closeView()">
                <img src="{{ $current->url }}" alt="{{ $current->caption }}"
                    class="max-h-[60vh] w-full rounded-lg object-contain">
                @if ($current->caption)
                    <p class="mt-3 font-semibold">{{ $current->caption }}</p>
                @endif
                @if ($current->package)
                    <a href="{{ route('trips.show', $current->package) }}" wire:navigate
                        class="text-sm text-accent">{{ $current->package->title }}</a>
                @endif

                <div class="mt-4 rounded-lg bg-soft p-3">
                    @if ($current->share_token)
                        <p class="mb-2 text-sm text-muted">Anyone with this link can see this photo. Nothing else on
                            your profile is shown.</p>
                        <div class="flex gap-2">
                            <input readonly value="{{ route('memories.shared', $current->share_token) }}"
                                class="min-w-0 flex-1 rounded-md border-[1.5px] border-line bg-surface px-3 py-2 text-sm"
                                aria-label="Share link">
                            <button type="button"
                                class="rounded-md bg-accent px-3 py-2 text-sm font-semibold text-on-accent"
                                x-on:click="navigator.clipboard.writeText('{{ route('memories.shared', $current->share_token) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                x-text="copied ? 'Copied' : 'Copy'"></button>
                        </div>
                        <button type="button" wire:click="toggleShare({{ $current->id }})"
                            class="auth-link mt-2">Stop sharing</button>
                    @else
                        <p class="mb-2 text-sm text-muted">This photo is private. Only you can see it.</p>
                        <button type="button" wire:click="toggleShare({{ $current->id }})"
                            class="rounded-md bg-accent px-4 py-2 text-sm font-semibold text-on-accent">Share with
                            friends</button>
                    @endif
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <button type="button" wire:click="deleteMemory({{ $current->id }})"
                        wire:confirm="Delete this memory? This cannot be undone."
                        class="text-sm font-semibold text-danger">Delete</button>
                    <button type="button" wire:click="closeView"
                        class="rounded-md border-[1.5px] border-line px-4 py-2 text-sm font-semibold">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
