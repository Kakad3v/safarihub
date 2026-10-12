@php
    $left = $booking->total - $booking->paidAmount();
@endphp
<x-mail.layout :preheader="'Booking ' .
    $booking->reference .
    ' is confirmed. We have received KES ' .
    number_format($payment->amount) .
    '.'">
    <x-mail.heading>You are booked</x-mail.heading>
    <x-mail.text>Thanks{{ $booking->user->name ? ', ' . \Illuminate\Support\Str::before($booking->user->name, ' ') : '' }}.
        We have received your payment and your seats are confirmed.</x-mail.text>
    <x-mail.rows :rows="[
        ['Booking', $booking->reference],
        ['Trip', $booking->package->title],
        ['Dates', $booking->departure->rangeLabel()],
        ['Travelers', $booking->travelers],
        ['Paid today', 'KES ' . number_format($payment->amount)],
        [
            $left > 0 ? 'Balance due ' . $booking->departure->balanceDueOn()->format('j M') : 'Balance',
            $left > 0 ? 'KES ' . number_format($left) : 'Paid in full',
        ],
    ]" />
    <x-mail.button :url="route('bookings.show', $booking)">View your booking</x-mail.button>
    <x-mail.text :small="true">Your operator's contact details and the meeting point are on the booking
        page.</x-mail.text>
</x-mail.layout>
