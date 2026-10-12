You are booked

Booking {{ $booking->reference }}
{{ $booking->package->title }}, {{ $booking->departure->rangeLabel() }}
Paid today: KES {{ number_format($payment->amount) }}

View your booking and the operator's contact details:
{{ route('bookings.show', $booking) }}
