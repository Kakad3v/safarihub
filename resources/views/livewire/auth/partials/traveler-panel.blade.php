<h2 class="mb-2.5 max-w-[16em] font-display text-[clamp(24px,2.6vw,34px)] font-bold leading-[1.12] tracking-tight">Local
    trips, booked directly with the operator</h2>
<p class="mb-[18px] max-w-[30em] text-[15px] text-panel-mute">Browse packages with real prices and honest reviews, then
    message the operator without sharing your number.</p>

<div class="mb-[18px] grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-3">
    @foreach ([['Masai Mara', '3-day safari', '28,500', '#D9A93E'], ['Wasini', 'Dolphin day trip', '6,500', '#5FB3BF'], ['Diani', 'Beach, 2 nights', '14,000', '#E6CFA0']] as [$place, $title, $price, $color])
        <div class="overflow-hidden rounded-lg border border-panel-line">
            <div class="flex h-[60px] items-end px-2.5 py-2 font-display text-[15px] font-bold text-[#10261F]"
                style="background: {{ $color }}">{{ $place }}</div>
            <div class="px-2.5 pb-2.5 pt-[9px] text-sm leading-snug">
                <b class="block font-semibold">{{ $title }}</b>
                <small class="text-[13px] text-panel-mute">From KES {{ $price }}</small>
            </div>
        </div>
    @endforeach
</div>

<div class="grid max-w-[34em] gap-3 border-t border-panel-line pt-3.5">
    @foreach ([['The guide knew every animal by name and the price was exactly what we paid. No surprises.', 'Wanjiru, 5.0 for Masai Mara safari'], ['Booked on my phone in five minutes and chatted with the operator before paying.', 'Brian, 4.9 for Wasini day trip']] as [$quote, $who])
        <div>
            <p class="mb-[3px] text-sm leading-snug">{{ $quote }}</p>
            <span class="text-[13px] text-panel-mute">{{ $who }}</span>
        </div>
    @endforeach
</div>
