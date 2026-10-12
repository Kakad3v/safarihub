<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Departure;
use App\Models\Payment;
use App\Services\Payments\PaystackGateway;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class Pay extends Component
{
    public Booking $booking;

    public string $mode = 'deposit';

    public string $method = 'card';

    public bool $agree = false;

    public string $error = '';

    public function mount(Booking $booking): void
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        if (in_array($booking->status, ['paid', 'cancelled'], true)) {
            $this->redirectRoute('bookings.show', $booking, navigate: true);

            return;
        }

        $this->booking = $booking->load(['package.photos', 'departure', 'package.user.operatorProfile']);
        $this->mode = $booking->status === 'deposit_paid' ? 'balance' : 'deposit';
        $this->error = (string) session('payment_error', '');
    }

    public function setMode(string $mode): void
    {
        if (array_key_exists($mode, $this->options())) {
            $this->mode = $mode;
        }
    }

    public function setMethod(string $method): void
    {
        if (array_key_exists($method, $this->methods())) {
            $this->method = $method;
        }
    }

    public function refreshHold(): void
    {
        $held = DB::transaction(function () {
            $departure = Departure::query()->lockForUpdate()->findOrFail($this->booking->departure_id);
            $booked = (int) $departure->bookings()->active()->where('id', '!=', $this->booking->id)->sum('travelers');

            if ($this->booking->travelers > $departure->seats - $booked) {
                return false;
            }

            $this->booking->update([
                'status' => 'pending',
                'expires_at' => now()->addMinutes(config('safarihub.booking.hold_minutes')),
            ]);

            return true;
        });

        $this->error = $held ? '' : 'Those seats have been taken. Choose another date on the trip page.';
    }

    public function pay(PaystackGateway $gateway): void
    {
        $this->validate(
            ['agree' => ['accepted']],
            ['agree.accepted' => 'Please agree to the cancellation terms to continue.']
        );

        $booking = $this->booking->fresh();
        $isBalance = $booking->status === 'deposit_paid';

        if (! $isBalance && ! $booking->holdIsActive()) {
            $this->error = 'Your seat hold ended. Hold your seats again to continue.';

            return;
        }

        $options = $this->options();

        if (! isset($options[$this->mode])) {
            return;
        }

        $payment = $booking->payments()->create([
            'reference' => Payment::newReference(),
            'gateway' => 'paystack',
            'purpose' => $this->mode,
            'amount' => $options[$this->mode]['amount'],
            'currency' => 'KES',
            'status' => 'pending',
        ]);

        try {
            $url = $gateway->initialize($payment, $this->customerEmail(), $this->channels(), route('payments.callback'));
        } catch (Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed']);

            $this->error = app()->isLocal()
                ? 'Paystack said: ' . ($e instanceof RequestException ? ($e->response->json('message') ?: $e->getMessage()) : $e->getMessage())
                : 'We could not open the secure checkout. Please try again in a moment.';

            return;
        }

        $this->redirect($url);
    }

    public function options(): array
    {
        $booking = $this->booking;

        if ($booking->status === 'deposit_paid') {
            $remaining = $booking->total - $booking->paidAmount();

            return ['balance' => [
                'label' => 'Pay the balance',
                'hint' => 'The rest of your booking',
                'amount' => $remaining,
            ]];
        }

        return [
            'deposit' => [
                'label' => 'Pay a deposit',
                'hint' => 'Balance of KES ' . number_format($booking->total - $booking->deposit) . ' due ' . $booking->departure->balanceDueOn()->format('j M'),
                'amount' => $booking->deposit,
            ],
            'full' => [
                'label' => 'Pay in full',
                'hint' => 'Nothing more to pay later',
                'amount' => $booking->total,
            ],
        ];
    }

    public function methods(): array
    {
        $methods = [
            'card' => ['Card', 'Visa, Mastercard or American Express. Works from any country.', 'You will enter your card details on a secure page. Your bank may ask you to confirm the payment.'],
        ];

        if (config('services.paystack.apple_pay')) {
            $methods['apple_pay'] = ['Apple Pay', 'Shown on supported Apple devices.', 'On the secure page, pay with Face ID or Touch ID. If your device does not support it, you can pay by card instead.'];
        }

        $methods['mpesa'] = ['M-Pesa', 'For Kenyan M-Pesa numbers.', 'On the secure page, enter your M-Pesa number and approve the prompt on your phone.'];

        return $methods;
    }

    public function render()
    {
        $booking = $this->booking;
        $options = $this->options();
        $isBalance = $booking->status === 'deposit_paid';

        if (! isset($options[$this->mode])) {
            $this->mode = array_key_first($options);
        }

        return view('livewire.bookings.pay', [
            'options' => $options,
            'methods' => $this->methods(),
            'isBalance' => $isBalance,
            'expired' => ! $isBalance && ! $booking->holdIsActive(),
            'amount' => $options[$this->mode]['amount'],
            'paid' => $booking->paidAmount(),
        ])->title('Pay for your booking');
    }

    private function channels(): array
    {
        return match ($this->method) {
            'mpesa' => ['mobile_money'],
            'apple_pay' => ['apple_pay', 'card'],
            default => ['card'],
        };
    }

    private function customerEmail(): string
    {
        $user = Auth::user();

        return $user->email ?: 'traveler-' . $user->id . '@' . config('services.paystack.fallback_email_domain');
    }
}
