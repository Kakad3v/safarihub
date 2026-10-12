<?php

namespace App\Livewire\Profile;

use App\Models\Package;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class TravelerProfile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $country = '';

    public $avatar = null;

    public bool $editing = false;

    public $photo = null;

    public string $caption = '';

    public ?int $packageId = null;

    public ?int $viewing = null;

    public function mount()
    {
        $user = Auth::user();

        if ($user->isOperator()) {
            return $this->redirectRoute('operator.profile', navigate: true);
        }

        $this->name = (string) $user->name;
        
        $this->country = (string) $user->country;
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo', ['photo' => $this->photoRules()]);
    }

    public function saveProfile(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:60'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], ['name.required' => 'Enter your first name.']);

        $user = Auth::user();
        $update = ['name' => $data['name'], 'country' => $data['country'] ?: null];

        if ($this->avatar) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $update['avatar_path'] = $this->avatar->store('avatars', 'public');
        }

        $user->update($update);
        $this->reset('avatar');
        $this->editing = false;
    }

    public function addMemory(): void
    {
        $this->validate([
            'photo' => ['required', ...$this->photoRules()],
            'caption' => ['nullable', 'string', 'max:200'],
            'packageId' => ['nullable', 'integer', Rule::in($this->tripOptions()->keys()->all())],
        ], ['photo.required' => 'Choose a photo to add.']);

        Auth::user()->memories()->create([
            'path' => $this->photo->store('memories', 'public'),
            'caption' => $this->caption ?: null,
            'package_id' => $this->packageId,
        ]);

        $this->reset('photo', 'caption', 'packageId');
    }

    public function view(int $id): void
    {
        $this->viewing = Auth::user()->memories()->findOrFail($id)->id;
    }

    public function closeView(): void
    {
        $this->viewing = null;
    }

    public function toggleShare(int $id): void
    {
        $memory = Auth::user()->memories()->findOrFail($id);
        $memory->update(['share_token' => $memory->share_token ? null : Str::random(32)]);
    }

    public function deleteMemory(int $id): void
    {
        $memory = Auth::user()->memories()->findOrFail($id);

        Storage::disk('public')->delete($memory->path);
        $memory->delete();
        $this->viewing = null;
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.profile.traveler-profile', [
            'user' => $user,
            'memories' => $user->memories()->with('package:id,title,slug')->latest()->get(),
            'bookings' => $user->bookings()
                ->with(['package.photos', 'departure'])
                ->whereIn('status', ['pending', 'deposit_paid', 'paid'])
                ->latest()
                ->get(),
            'tripOptions' => $this->tripOptions(),
            'reviewCount' => Review::where('user_id', $user->id)->count(),
        ])->title('Your profile');
    }

    private function photoRules(): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
    }

    private function tripOptions()
    {
        return Package::whereIn('id', Auth::user()->bookings()->pluck('package_id'))->pluck('title', 'id');
    }
}
