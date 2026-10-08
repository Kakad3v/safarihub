<?php

namespace App\Livewire\Operators;

use App\Enums\PackageStatus;
use App\Models\OperatorProfile;
use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class OperatorShow extends Component
{
    public OperatorProfile $profile;

    #[Url(as: 'tab', except: 'trips')]
    public string $tab = 'trips';

    public function mount(OperatorProfile $profile): void
    {
        abort_unless($profile->status === 'verified' || $this->isOwner($profile), 404);

        $this->profile = $profile;
    }

    private function isOwner(OperatorProfile $profile): bool
    {
        return auth()->id() === $profile->user_id;
    }

    public function render()
    {
        $owner = $this->isOwner($this->profile);

        $packages = Package::query()
            ->where('user_id', $this->profile->user_id)
            ->when(
                $owner,
                fn($q) => $q->where('status', '!=', PackageStatus::Archived),
                fn($q) => $q->where('status', PackageStatus::Published)
            )
            ->with(['destination:id,name', 'category:id,name', 'media'])
            ->latest()
            ->get();

        $reviews = $packages->sum('reviews_count');
        $rating = $reviews
            ? round($packages->sum(fn($p) => $p->rating_avg * $p->reviews_count) / $reviews, 1)
            : null;

        return view('livewire.operators.operator-show', [
            'owner' => $owner,
            'packages' => $packages,
            'reviews' => $reviews,
            'rating' => $rating,
        ]);
    }
}
