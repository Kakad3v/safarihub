<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\LoginCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.auth')]
class SignIn extends Component
{
    public const COUNTIES = ['Mombasa', 'Kwale', 'Kilifi', 'Nairobi', 'Narok', 'Other county'];

    public const OFFERS = ['Safaris', 'Beach stays', 'Hikes', 'Day trips', 'Boat trips'];

    #[Url(as: 'as')]
    public string $role = 'traveler';

    public string $method = 'phone';

    public int $step = 1;

    public string $identifier = '';

    public string $target = '';

    public string $code = '';

    public string $name = '';

    public string $county = 'Mombasa';

    public array $offers = [];

    public function mount(): void
    {
        if (! in_array($this->role, ['traveler', 'operator'], true)) {
            $this->role = 'traveler';
        }

        if ($message = session('link_error')) {
            $this->addError('link', $message);
        }

        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (filled($user->name)) {
            $this->redirectIntended($this->home($user), navigate: true);

            return;
        }

        $this->role = $user->role;
        $this->step = 3;
    }

    public function setRole(string $role): void
    {
        if ($this->step === 3 || ! in_array($role, ['traveler', 'operator'], true)) {
            return;
        }

        $this->role = $role;
        $this->resetFlow();
    }

    public function setMethod(string $method): void
    {
        if (! in_array($method, ['phone', 'email'], true)) {
            return;
        }

        $this->method = $method;
        $this->resetFlow();
    }

    public function sendCode(LoginCodeService $codes): void
    {
        $this->validate(
            ['identifier' => $this->method === 'email'
                ? ['required', 'email:rfc', 'max:255']
                : ['required', 'regex:/^\+?[\d\s]{9,15}$/']],
            [
                'identifier.required' => $this->method === 'email' ? 'Enter your email address.' : 'Enter your phone number.',
                'identifier.email' => 'Enter a valid email address.',
                'identifier.regex' => 'Enter a valid phone number.',
            ]
        );

        $this->target = $codes->normalize($this->identifier, $this->method);

        $codes->send($this->target, $this->channel(), $this->role, request()->ip());

        $this->code = '';
        $this->step = 2;
        $this->dispatch('code-sent');
    }

    public function resend(LoginCodeService $codes): void
    {
        $codes->send($this->target, $this->channel(), $this->role, request()->ip());

        $this->code = '';
        $this->dispatch('code-sent');
    }

    public function verify(LoginCodeService $codes): void
    {
        $this->validate(
            ['code' => ['required', 'digits:6']],
            ['code.required' => 'Enter all 6 digits of the code.', 'code.digits' => 'Enter all 6 digits of the code.']
        );

        $user = $codes->verify($this->target, $this->code);

        Auth::login($user, remember: true);
        session()->regenerate();

        if (filled($user->name)) {
            $this->redirectIntended($this->home($user), navigate: true);

            return;
        }

        $this->role = $user->role;
        $this->step = 3;
    }

    public function changeIdentifier(): void
    {
        $this->code = '';
        $this->resetErrorBag();
        $this->step = 1;
    }

    public function finish(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        if ($user->isOperator()) {
            $data = $this->validate([
                'name' => ['required', 'string', 'max:120'],
                'county' => ['required', Rule::in(self::COUNTIES)],
                'offers' => ['required', 'array', 'min:1'],
                'offers.*' => [Rule::in(self::OFFERS)],
            ], [
                'name.required' => 'Enter your business name.',
                'offers.required' => 'Choose at least one thing you offer.',
                'offers.min' => 'Choose at least one thing you offer.',
            ]);

            $user->update(['name' => $data['name']]);

            $user->operatorProfile()->updateOrCreate([], [
                'business_name' => $data['name'],
                'county' => $data['county'],
                'offerings' => $data['offers'],
            ]);
        } else {
            $data = $this->validate(
                ['name' => ['required', 'string', 'max:60']],
                ['name.required' => 'Enter your first name.']
            );

            $user->update(['name' => $data['name']]);
        }

        $this->redirectIntended($this->home($user), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.sign-in', [
            'counties' => self::COUNTIES,
            'offerOptions' => self::OFFERS,
        ]);
    }

    private function channel(): string
    {
        return $this->method === 'email' ? 'email' : 'sms';
    }

    private function home(User $user): string
    {
        return $user->isOperator() ? url('/operator') : url('/');
    }

    private function resetFlow(): void
    {
        $this->identifier = '';
        $this->code = '';
        $this->step = 1;
        $this->resetErrorBag();
    }
}
