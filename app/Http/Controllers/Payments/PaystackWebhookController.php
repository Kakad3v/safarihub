<?php

namespace App\Http\Controllers\Payments;

use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaystackGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaystackWebhookController
{
    public function __invoke(Request $request, PaystackGateway $gateway, PaymentService $payments): Response
    {
        abort_unless($gateway->validSignature($request->getContent(), $request->header('x-paystack-signature')), 401);

        $reference = $request->json('data.reference');

        if ($request->json('event') === 'charge.success' && $reference) {
            $payment = Payment::query()->where('reference', $reference)->first();

            if ($payment) {
                $payments->settle($payment);
            }
        }

        return response('OK');
    }
}
