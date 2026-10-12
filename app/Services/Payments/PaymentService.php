<?php

namespace App\Services\Payments;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    public function __construct(private PaystackGateway $gateway) {}

    public function settle(Payment $payment): Payment
    {
        if ($payment->status === 'success') {
            return $payment;
        }

        $data = $this->gateway->verify($payment->reference);
        $confirmed = false;

        DB::transaction(function () use ($payment, $data, &$confirmed) {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->status === 'success') {
                return;
            }

            $status = $data['status'] ?? 'pending';

            if ($status === 'success') {
                $matches = (int) ($data['amount'] ?? 0) === $locked->amount * 100
                    && strtoupper((string) ($data['currency'] ?? '')) === $locked->currency;

                if (! $matches) {
                    $locked->update(['status' => 'review', 'raw' => $data]);
                    Log::warning('Paystack amount or currency mismatch', ['payment' => $locked->id]);

                    return;
                }

                $locked->update([
                    'status' => 'success',
                    'channel' => $data['channel'] ?? null,
                    'gateway_id' => isset($data['id']) ? (string) $data['id'] : null,
                    'paid_at' => now(),
                    'raw' => $data,
                ]);

                $this->applyToBooking($locked);
                $confirmed = true;
            } elseif (in_array($status, ['failed', 'abandoned', 'reversed'], true)) {
                $locked->update([
                    'status' => $status === 'reversed' ? 'failed' : $status,
                    'raw' => $data,
                ]);
            }
        });

        $payment->refresh();

        if ($confirmed) {
            $this->notify($payment);
        }

        return $payment;
    }

    private function applyToBooking(Payment $payment): void
    {
        $booking = Booking::query()->lockForUpdate()->findOrFail($payment->booking_id);
        $paid = (int) $booking->payments()->where('status', 'success')->sum('amount');

        $booking->update([
            'status' => $paid >= $booking->total ? 'paid' : 'deposit_paid',
            'expires_at' => null,
        ]);
    }

    private function notify(Payment $payment): void
    {
        $booking = $payment->booking()->with(['user', 'package', 'departure'])->first();

        if (! $booking->user->email) {
            return;
        }

        rescue(fn() => Mail::to($booking->user->email)->send(new BookingConfirmedMail($booking, $payment)));
    }
}
