<?php

namespace App\Livewire\Profile;

use App\Livewire\Auth\SignIn;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class OperatorSettings extends Component
{
    use WithFileUploads;

    public string $businessName = '';

    public string $county = 'Mombasa';

    public string $bio = '';

    public array $offers = [];

    public $logo = null;

    public bool $saved = false;

    public function mount()
    {
        $user = Auth::user();

        if (! $user->isOperator()) {
            return $this->redirectRoute('me', navigate: true);
        }

        $profile = $user->operatorProfile;

        $this->businessName = $profile->business_name ?? (string) $user->name;
        $this->county = $profile->county ?? 'Mombasa';
        $this->bio = (string) ($profile->bio ?? '');
        $this->offers = $profile->offerings ?? [];
    }

    public function save(): void
    {
        $data = $this->validate([
            'businessName' => ['required', 'string', 'max:120'],
            'county' => ['required', Rule::in(SignIn::COUNTIES)],
            'bio' => ['nullable', 'string', 'max:600'],
            'offers' => ['required', 'array', 'min:1'],
            'offers.*' => [Rule::in(SignIn::OFFERS)],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'businessName.required' => 'Enter your business name.',
            'offers.required' => 'Choose at least one thing you offer.',
            'offers.min' => 'Choose at least one thing you offer.',
        ]);

        $user = Auth::user();

        if ($this->logo) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $this->logo->store('avatars', 'public');
        }

        $user->name = $data['businessName'];
        $user->save();

        $user->operatorProfile()->updateOrCreate([], [
            'business_name' => $data['businessName'],
            'county' => $data['county'],
            'bio' => $data['bio'] ?: null,
            'offerings' => $data['offers'],
        ]);

        $this->reset('logo');
        $this->saved = true;
    }

    public function updated(): void
    {
        $this->saved = false;
    }

    public function render()
    {
        $user = Auth::user();
        $reviews = Review::whereHas('package', fn($q) => $q->where('user_id', $user->id));
        $count = $reviews->count();

        return view('livewire.profile.operator-settings', [
            'user' => $user,
            'profile' => $user->operatorProfile()->first(),
            'packages' => $user->packages()->withCount('reviews')->withAvg('reviews', 'rating')->latest()->get(),
            'reviewCount' => $count,
            'average' => $count ? round($reviews->avg('rating'), 1) : null,
            'counties' => SignIn::COUNTIES,
            'offerOptions' => SignIn::OFFERS,
        ])->title('Your business');
    }
}
