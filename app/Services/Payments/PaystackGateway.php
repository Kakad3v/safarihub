<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PaystackGateway
{
    public function initialize(Payment $payment, string $email, array $channels, string $callbackUrl): string
    {
        $response = $this->http()->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $payment->amount * 100,
            'currency' => $payment->currency,
            'reference' => $payment->reference,
            'callback_url' => $callbackUrl,
            'channels' => $channels,
            'metadata' => [
                'booking' => $payment->booking->reference,
                'purpose' => $payment->purpose,
            ],
        ])->throw()->json();

        return $response['data']['authorization_url'];
    }

    public function verify(string $reference): array
    {
        return $this->http()
            ->get('/transaction/verify/' . rawurlencode($reference))
            ->throw()
            ->json('data', []);
    }

    public function validSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, (string) config('services.paystack.secret_key')), $signature);
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.paystack.base_url'))
            ->withToken((string) config('services.paystack.secret_key'))
            ->acceptJson()
            ->timeout(20);
    }
}
