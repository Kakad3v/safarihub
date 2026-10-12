<?php

namespace App\Http\Controllers\Payments;

use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PaystackCallbackController
{
    public function __invoke(Request $request, PaymentService $payments): RedirectResponse
    {
        $reference = $request->query('reference', $request->query('trxref'));

        $payment = Payment::query()->where('reference', $reference)->with('booking')->firstOrFail();

        abort_unless($payment->booking->user_id === $request->user()->id, 403);

        try {
            $payment = $payments->settle($payment);
        } catch (Throwable $e) {
            report($e);
        }

        if (in_array($payment->status, ['failed', 'abandoned'], true)) {
            return redirect()
                ->route('bookings.pay', $payment->booking)
                ->with('payment_error', 'The payment did not go through. No money was taken.');
        }

        return redirect()->route('bookings.show', $payment->booking);
    }
}
