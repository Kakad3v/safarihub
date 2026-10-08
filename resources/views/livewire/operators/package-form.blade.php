<div class="mx-auto max-w-5xl px-4 pb-32 pt-6">
    @unless ($verified)
        <div class="rounded-lg border border-line bg-soft p-4 text-sm">
            <span class="font-semibold">Your operator account is pending verification.</span>
            You can build and save packages now. They go live once we approve your account.
        </div>
    @endunless

    <div class="mt-6 gap-10 lg:grid lg:grid-cols-5">
        <div class="lg:col-span-3">
            <h1 class="font-display text-3xl font-bold tracking-tight">{{ $packageId ? 'Edit package' : 'New package' }}
            </h1>

            <div class="mt-5 flex gap-2">
                @foreach (\App\Livewire\Operators\PackageForm::STEPS as $i => $label)
                    <div class="flex-1">
                        <div class="h-1.5 rounded-full {{ $i <= $step ? 'bg-accent' : 'bg-line' }}"></div>
                        <p class="mt-1.5 text-xs font-semibold {{ $i === $step ? 'text-ink' : 'text-muted' }}">
                            {{ $label }}</p>
                    </div>
                @endforeach
            </div>

            @if ($step === 0)
                <div class="mt-6 space-y-5">
                    <div>
                        <p class="field-label">What are you listing?</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (['trip' => ['Trip', 'Multi-day, with an itinerary and set departure dates.'], 'experience' => ['Experience', 'A few hours or one day, like a snorkel trip or game drive.']] as $value => [$name, $text])
                                <button type="button" wire:click="$set('type', '{{ $value }}')"
                                    class="rounded-lg border-[1.5px] p-4 text-left {{ $type === $value ? 'border-accent bg-soft' : 'border-line bg-surface' }}">
                                    <span class="block font-semibold">{{ $name }}</span>
                                    <span class="mt-1 block text-sm text-muted">{{ $text }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <x-field name="title" label="Title">
                        <input id="title" wire:model.live.debounce.300ms="title" class="field"
                            placeholder="{{ $type === 'trip' ? 'e.g. 3-Day Masai Mara Safari' : 'e.g. Wasini Island Dolphin & Snorkel Day' }}">
                    </x-field>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-field name="destination_id" label="Destination">
                            <select id="destination_id" wire:model.live="destination_id" class="field">
                                <option value="">Choose…</option>
                                @foreach ($destinations as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <x-field name="category_id" label="Category">
                            <select id="category_id" wire:model="category_id" class="field">
                                <option value="">Choose…</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </x-field>
                    </div>

                    <x-field name="summary" label="Short summary" :hint="strlen($summary) . '/160'">
                        <input id="summary" wire:model.live.debounce.300ms="summary" maxlength="160" class="field"
                            placeholder="One line travelers see on the card">
                    </x-field>

                    <x-field name="description" label="Description">
                        <textarea id="description" wire:model="description" rows="5" class="field"
                            placeholder="What makes this special? Who is it for? What will they remember?"></textarea>
                    </x-field>
                </div>
            @endif

            @if ($step === 1)
                <div class="mt-6 space-y-5">
                    @if ($type === 'trip')
                        <div>
                            <p class="field-label">Day-by-day itinerary</p>
                            <div class="space-y-3">
                                @foreach ($plan as $i => $day)
                                    <div wire:key="day-{{ $i }}">
                                        <div class="flex items-start gap-2">
                                            <span
                                                class="mt-2.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-accent text-xs font-bold text-on-accent">{{ $i + 1 }}</span>
                                            <textarea wire:model="plan.{{ $i }}" rows="2" class="field" placeholder="What happens on this day?"></textarea>
                                            <button type="button" wire:click="removeDay({{ $i }})"
                                                class="mt-2 px-1 text-xl leading-none text-muted"
                                                aria-label="Remove day">×</button>
                                        </div>
                                        @error("plan.$i")
                                            <p class="field-error ml-9">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="addDay" class="mt-3 text-sm font-semibold text-accent">+
                                Add day</button>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3">
                            <x-field name="duration_hours" label="Duration (hours)">
                                <input id="duration_hours" type="number" min="0.5" step="0.5"
                                    wire:model.live="duration_hours" class="field">
                            </x-field>
                            <x-field name="start_time" label="Start time">
                                <input id="start_time" type="time" wire:model="start_time" class="field">
                            </x-field>
                        </div>
                        <x-field name="meeting_point" label="Meeting point">
                            <input id="meeting_point" wire:model="meeting_point" class="field"
                                placeholder="e.g. Shimoni jetty, or hotel pickup in Diani">
                        </x-field>
                        <x-field name="bring" label="What should guests bring? (optional)">
                            <input id="bring" wire:model="bring" class="field"
                                placeholder="Sunscreen, swimwear, a towel">
                        </x-field>
                    @endif

                    <div>
                        <p class="field-label">What's included</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (\App\Livewire\Operator\PackageForm::INCLUSIONS as $item)
                                <button type="button" wire:click="toggle('included', '{{ $item }}')"
                                    aria-pressed="{{ in_array($item, $included, true) ? 'true' : 'false' }}"
                                    class="chip">{{ $item }}</button>
                            @endforeach
                        </div>
                    </div>

                    <x-field name="excluded" label="Not included (optional)">
                        <textarea id="excluded" wire:model="excluded" rows="2" class="field"
                            placeholder="One per line, e.g. Drinks, tips, travel insurance"></textarea>
                    </x-field>
                </div>
            @endif

            @if ($step === 2)
                <div class="mt-6 space-y-5">
                    <div class="grid grid-cols-2 gap-3">
                        <x-field name="price" label="Price per adult (KES)">
                            <input id="price" type="number" min="0"
                                wire:model.live.debounce.400ms="price" class="field" placeholder="28500">
                        </x-field>
                        <x-field name="max_group_size" label="Max group size">
                            <input id="max_group_size" type="number" min="1" wire:model="max_group_size"
                                class="field" placeholder="8">
                        </x-field>
                    </div>

                    <x-field name="child_discount_percent" label="Child discount (%)"
                        hint="Applied to children aged 3 to 12. Leave at 0 for none.">
                        <input id="child_discount_percent" type="number" min="0" max="100"
                            wire:model="child_discount_percent" class="field">
                    </x-field>

                    @if ($type === 'trip')
                        <div>
                            <p class="field-label">Departure dates</p>
                            <div class="space-y-2">
                                @foreach ($departures as $i => $date)
                                    <div wire:key="dep-{{ $i }}">
                                        <div class="flex gap-2">
                                            <input type="date" wire:model="departures.{{ $i }}"
                                                min="{{ today()->toDateString() }}" class="field">
                                            <button type="button" wire:click="removeDeparture({{ $i }})"
                                                class="px-2 text-xl text-muted" aria-label="Remove date">×</button>
                                        </div>
                                        @error("departures.$i")
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="addDeparture"
                                class="mt-3 text-sm font-semibold text-accent">+ Add departure</button>
                        </div>
                    @else
                        <div>
                            <p class="field-label">Days it runs</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (\App\Livewire\Operator\PackageForm::DAYS as $d)
                                    <button type="button" wire:click="toggle('weekdays', '{{ $d }}')"
                                        aria-pressed="{{ in_array($d, $weekdays, true) ? 'true' : 'false' }}"
                                        class="chip w-14 !rounded-md text-center">{{ $d }}</button>
                                @endforeach
                            </div>
                            @error('weekdays')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <p class="field-label">Cancellation policy</p>
                        <div class="space-y-2">
                            @foreach ($policies as $policy)
                                <button type="button"
                                    wire:click="$set('cancellation_policy', '{{ $policy->value }}')"
                                    class="block w-full rounded-lg border-[1.5px] p-3 text-left {{ $cancellation_policy === $policy->value ? 'border-accent bg-soft' : 'border-line bg-surface' }}">
                                    <span class="block text-sm font-semibold">{{ $policy->label() }}</span>
                                    <span class="block text-xs text-muted">{{ $policy->description() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg border border-line bg-surface p-4 text-sm">
                        <div class="flex justify-between"><span class="text-muted">Platform fee
                                ({{ $feePercent }}%)</span><span>− KES
                                {{ number_format(round((($price ?? 0) * $feePercent) / 100)) }}</span></div>
                        <div class="mt-2 flex justify-between font-semibold"><span>You receive per
                                adult</span><span>KES
                                {{ number_format(round((($price ?? 0) * (100 - $feePercent)) / 100)) }}</span></div>
                    </div>
                </div>
            @endif

            @if ($step === 3)
                <div class="mt-6 space-y-5">
                    <div>
                        <p class="field-label">Photos</p>
                        <label
                            class="block cursor-pointer rounded-lg border-2 border-dashed border-line bg-surface p-8 text-center hover:border-accent">
                            <p class="font-semibold">Tap to add photos</p>
                            <p class="mt-1 text-sm text-muted">JPG or PNG, up to 5 MB each. The first photo is your
                                cover.</p>
                            <input type="file" wire:model="photos" accept="image/*" multiple class="hidden">
                        </label>
                        <p wire:loading wire:target="photos" class="mt-2 text-sm text-muted">Uploading…</p>
                        @error('photos')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error('photos.*')
                            <p class="field-error">{{ $message }}</p>
                        @enderror

                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach ($existing as $i => $photo)
                                <div wire:key="ex-{{ $photo['id'] }}"
                                    class="relative aspect-square overflow-hidden rounded-md bg-soft">
                                    <img src="{{ $photo['url'] }}" alt=""
                                        class="h-full w-full object-cover">
                                    <button type="button" wire:click="removeExisting({{ $photo['id'] }})"
                                        class="absolute right-1 top-1 h-6 w-6 rounded-full bg-black/60 text-sm leading-none text-white"
                                        aria-label="Remove photo">×</button>
                                    @if ($i === 0)
                                        <span
                                            class="absolute bottom-1 left-1 rounded-full bg-surface px-2 py-0.5 text-[10px] font-semibold">Cover</span>
                                    @endif
                                </div>
                            @endforeach
                            @foreach ($photos as $i => $photo)
                                <div wire:key="new-{{ $i }}"
                                    class="relative aspect-square overflow-hidden rounded-md bg-soft">
                                    @if ($photo->isPreviewable())
                                        <img src="{{ $photo->temporaryUrl() }}" alt=""
                                            class="h-full w-full object-cover">
                                    @endif
                                    <button type="button" wire:click="removePhoto({{ $i }})"
                                        class="absolute right-1 top-1 h-6 w-6 rounded-full bg-black/60 text-sm leading-none text-white"
                                        aria-label="Remove photo">×</button>
                                    @if (count($existing) === 0 && $i === 0)
                                        <span
                                            class="absolute bottom-1 left-1 rounded-full bg-surface px-2 py-0.5 text-[10px] font-semibold">Cover</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="field-hint">
                            {{ count($existing) + count($photos) < 5 ? 'Listings with 5 or more photos get more inquiries.' : count($existing) + count($photos) . ' photos added' }}
                        </p>
                    </div>

                    <x-field name="video_url" label="Video link (optional)">
                        <input id="video_url" wire:model="video_url" class="field"
                            placeholder="YouTube or Vimeo link">
                    </x-field>
                </div>
            @endif
        </div>

        <aside class="hidden lg:col-span-2 lg:block">
            <div class="sticky top-24">
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-muted">Live preview</p>
                <article class="overflow-hidden rounded-lg border border-line bg-surface shadow-sm">
                    <div class="relative aspect-[4/3] bg-soft">
                        @php
                            $cover = $existing[0]['url'] ?? null;

                            if (!$cover && isset($photos[0]) && $photos[0]->isPreviewable()) {
                                $cover = $photos[0]->temporaryUrl();
                            }
                        @endphp
                        @if ($cover)
                            <img src="{{ $cover }}" alt="" class="h-full w-full object-cover">
                        @endif
                        <span
                            class="absolute left-3 top-3 rounded-full bg-surface px-3 py-1 text-xs font-semibold">{{ $type === 'trip' ? 'Trip' : 'Experience' }}</span>
                        <p
                            class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-4 pt-10 font-display text-lg font-bold text-white">
                            {{ $placeName }}</p>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold leading-snug">{{ $title ?: 'Your title appears here' }}</h3>
                        <p class="mt-1 text-sm text-muted">
                            {{ $type === 'trip' ? count(array_filter($plan)) . ' ' . Str::plural('day', count(array_filter($plan))) : (float) $duration_hours . ' ' . Str::plural('hour', (int) ceil($duration_hours ?? 1)) }}
                        </p>
                        <p class="mt-4 text-sm text-muted">From <span class="text-lg font-bold text-ink">KES
                                {{ number_format($price ?? 0) }}</span></p>
                    </div>
                </article>
            </div>
        </aside>
    </div>

    <div class="fixed inset-x-0 bottom-0 border-t border-line bg-canvas">
        <div class="mx-auto flex max-w-5xl gap-3 px-4 py-3">
            @if ($step > 0)
                <button type="button" wire:click="back" class="btn-line">Back</button>
            @endif
            <button type="button" wire:click="save('draft')" wire:loading.attr="disabled" wire:target="save"
                class="btn-line">Save draft</button>
            @if ($step < 3)
                <button type="button" wire:click="next" class="btn-accent flex-1">Continue</button>
            @else
                <button type="button" wire:click="save('publish')" wire:loading.attr="disabled"
                    wire:target="save,photos" class="btn-accent flex-1">
                    {{ $packageId ? 'Save changes' : 'Submit for review' }}
                </button>
            @endif
        </div>
    </div>
</div>
