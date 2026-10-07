<h2 class="mb-2.5 max-w-[16em] font-display text-[clamp(24px,2.6vw,34px)] font-bold leading-[1.12] tracking-tight">Show
    your packages to travelers ready to book</h2>
<p class="mb-[18px] max-w-[30em] text-[15px] text-panel-mute">List your tours, get inquiries in one inbox, and see what
    you have earned. Set up takes a few minutes.</p>

<div class="mb-3 grid grid-cols-3 gap-2.5">
    @foreach ([['KES 84,200', 'earned this month'], ['6', 'new inquiries'], ['4.8', 'average rating']] as [$value, $label])
        <div class="rounded-lg border border-panel-line p-2.5">
            <b class="block font-display text-xl font-bold">{{ $value }}</b>
            <small class="block text-[13px] leading-snug text-panel-mute">{{ $label }}</small>
        </div>
    @endforeach
</div>

<div class="mb-[18px] rounded-lg border border-panel-line px-3 py-2.5 text-sm leading-snug">
    <b class="font-semibold">New inquiry from Wanjiru</b><br>
    Masai Mara 3-day safari, 4 travelers, 12 Nov<br>
    <small class="text-[13px] text-panel-mute">Reply in chat. No phone numbers shared.</small>
</div>

<div class="grid max-w-[34em] gap-3 border-t border-panel-line pt-3.5">
    <div>
        <p class="mb-[3px] text-sm leading-snug">I stopped giving my number to strangers. Inquiries land in one inbox
            and I reply when I can.</p>
        <span class="text-[13px] text-panel-mute">Coastline Safaris, Diani</span>
    </div>
</div>
