<?php

namespace App\Livewire\Operators;

use App\Models\OperatorProfile;
use App\Models\Review;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public OperatorProfile $profile;

    public function mount(OperatorProfile $operatorProfile): void
    {
        $this->profile = $operatorProfile->load('user');
    }

    public function render()
    {
        $user = $this->profile->user;

        $reviews = Review::query()->whereHas('package', fn($q) => $q->where('user_id', $user->id));
        $count = (clone $reviews)->count();

        return view('livewire.operators.show', [
            'operator' => $user,
            'packages' => $user->packages()->published()->with('photos')->withCount('reviews')->withAvg('reviews', 'rating')->latest()->get(),
            'reviews' => (clone $reviews)->with(['user:id,name', 'package:id,title,slug'])->latest()->limit(10)->get(),
            'reviewCount' => $count,
            'average' => $count ? round((clone $reviews)->avg('rating'), 1) : null,
        ])->title($this->profile->business_name);
    }
}
