<?php

namespace App\Livewire;

use App\Models\Package;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public const CATEGORIES = ['Safaris', 'Beach stays', 'Hikes', 'Day trips', 'Boat trips'];

    private const COLORS = [
        'Safaris' => '#D9A93E',
        'Beach stays' => '#E6CFA0',
        'Hikes' => '#7FA66A',
        'Day trips' => '#3E9FB0',
        'Boat trips' => '#5FB3BF',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'type', except: '')]
    public string $category = '';

    public bool $onlySaved = false;

    public array $saved = [];

    public function mount(): void
    {
        $this->saved = session('saved_trips', []);
    }

    public function setCategory(string $category): void
    {
        $this->category = in_array($category, self::CATEGORIES, true) ? $category : '';
    }

    public function pickPlace(string $place): void
    {
        $this->search = $place;
        $this->category = '';
        $this->onlySaved = false;
    }

    public function toggleSave(int $id): void
    {
        $this->saved = in_array($id, $this->saved, true)
            ? array_values(array_diff($this->saved, [$id]))
            : [...$this->saved, $id];

        session(['saved_trips' => $this->saved]);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'onlySaved');
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        $all = Package::published()
            ->with(['photos', 'user.operatorProfile', 'latestReview.user'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get();

        $needle = strtolower($this->search);

        $trips = $all
            ->when($this->category !== '', fn($c) => $c->where('category', $this->category))
            ->when($needle !== '', fn($c) => $c->filter(
                fn($p) => str_contains(strtolower($p->title . ' ' . $p->place . ' ' . $this->operatorName($p)), $needle)
            ))
            ->when($this->onlySaved, fn($c) => $c->whereIn('id', $this->saved))
            ->sortByDesc('featured')
            ->values();

        $destinations = $all->groupBy('place')->map(fn($group, $place) => [
            'place' => $place,
            'county' => $group->first()->county,
            'count' => $group->count(),
            'photo' => $group->first(fn($p) => $p->photos->isNotEmpty())?->photos->first(),
            'color' => self::COLORS[$group->first()->category] ?? '#BFD3D0',
        ])->sortByDesc('count')->values();

        return view('livewire.home', [
            'trips' => $trips,
            'destinations' => $destinations,
            'heroPackages' => $this->heroPackages($all),
            'reviews' => $this->reviews(),
            'categories' => self::CATEGORIES,
            'colors' => self::COLORS,
            'user' => Auth::user(),
        ]);
    }

    private function heroPackages($all): array
    {
        $featured = $all->where('featured', true)->sortBy(fn($p) => $p->featured_position ?? 99)->values();

        if ($featured->isEmpty()) {
            $featured = $all->take(3);
        }

        return $featured
            ->filter(fn($p) => $p->photos->isNotEmpty())
            ->map(fn($p) => [
                'id' => $p->id,
                'url' => route('trips.show', $p),
                'place' => $p->place,
                'tab' => $p->place,
                'title' => $p->title,
                'operator' => $this->operatorName($p),
                'duration' => $p->durationLabel(),
                'price' => $p->price,
                'color' => self::COLORS[$p->category] ?? '#BFD3D0',
                'review' => $p->latestReview?->body ?? '',
                'reviewer' => $p->latestReview
                    ? str($p->latestReview->user->name ?: 'Traveler')->before(' ') . ', ' . number_format($p->latestReview->rating, 1) . ' for ' . $p->title
                    : '',
                'images' => $p->photos->map(fn($photo) => [
                    'src' => $photo->url,
                    'alt' => (string) $photo->alt,
                    'pos' => $photo->focus,
                ])->all(),
            ])
            ->values()
            ->all();
    }

    private function reviews(): array
    {
        return Review::with(['user', 'package'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn($r) => [
                $r->body,
                (string) str($r->user->name ?: 'Traveler')->before(' '),
                $r->package->title,
                (float) $r->rating,
            ])
            ->all();
    }

    private function operatorName(Package $package): string
    {
        return $package->user->operatorProfile?->business_name ?? $package->user->name;
    }
}
