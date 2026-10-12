<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class Show extends Component
{
    public Booking $booking;

    public function mount(Booking $booking): void
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        $this->booking = $booking->load(['package.user.operatorProfile', 'departure']);
    }

    public function refreshStatus(PaymentService $payments): void
    {
        $this->booking->payments()
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subHours(2))
            ->latest()
            ->limit(3)
            ->get()
            ->each(function ($payment) use ($payments) {
                try {
                    $payments->settle($payment);
                } catch (Throwable $e) {
                    report($e);
                }
            });

        $this->booking->refresh();
    }

    public function render()
    {
        $booking = $this->booking;
        $confirmed = in_array($booking->status, ['deposit_paid', 'paid'], true);
        $processing = ! $confirmed && $booking->payments()
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subHours(2))
            ->exists();

        return view('livewire.bookings.show', [
            'confirmed' => $confirmed,
            'processing' => $processing,
            'paid' => $booking->paidAmount(),
            'lastPayment' => $booking->payments()->where('status', 'success')->latest('paid_at')->first(),
        ])->title($confirmed ? 'You are booked' : 'Your booking');
    }
}
