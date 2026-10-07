<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public const CATEGORIES = ['Safaris', 'Beach stays', 'Hikes', 'Day trips', 'Boat trips'];

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
        $all = collect($this->trips());
        $needle = strtolower($this->search);

        $trips = $all
            ->when($this->category !== '', fn($c) => $c->where('category', $this->category))
            ->when($needle !== '', fn($c) => $c->filter(
                fn($t) => str_contains(strtolower($t['title'] . ' ' . $t['place'] . ' ' . $t['operator']), $needle)
            ))
            ->when($this->onlySaved, fn($c) => $c->whereIn('id', $this->saved))
            ->values();

        $destinations = collect($this->destinations())
            ->map(fn($d) => $d + ['count' => $all->where('place', $d['place'])->count()]);

        return view('livewire.home', [
            'trips' => $trips,
            'featured' => $all->first(),
            'destinations' => $destinations,
            'reviews' => $this->reviews(),
            'categories' => self::CATEGORIES,
            'user' => Auth::user(),
        ]);
    }

    private function trips(): array
    {
        return [
            ['id' => 1, 'title' => '3-day safari', 'place' => 'Masai Mara', 'category' => 'Safaris', 'operator' => 'Coastline Safaris', 'rating' => 4.9, 'reviews' => 128, 'price' => 28500, 'duration' => '3 days', 'color' => '#D9A93E', 'image' => 'masai-mara'],
            ['id' => 2, 'title' => 'Dolphin day trip', 'place' => 'Wasini', 'category' => 'Boat trips', 'operator' => 'Pwani Dhow Tours', 'rating' => 4.8, 'reviews' => 96, 'price' => 6500, 'duration' => '1 day', 'color' => '#5FB3BF', 'image' => 'wasini'],
            ['id' => 3, 'title' => 'Beach escape, 2 nights', 'place' => 'Diani', 'category' => 'Beach stays', 'operator' => 'Coastline Safaris', 'rating' => 4.7, 'reviews' => 74, 'price' => 14000, 'duration' => '2 nights', 'color' => '#E6CFA0', 'image' => 'diani'],
            ['id' => 4, 'title' => 'Snorkelling day trip', 'place' => 'Mpunguti', 'category' => 'Day trips', 'operator' => 'Pwani Dhow Tours', 'rating' => 4.8, 'reviews' => 52, 'price' => 5500, 'duration' => '1 day', 'color' => '#3E9FB0', 'image' => null],
            ['id' => 5, 'title' => 'Sunrise hike', 'place' => 'Chullu hills', 'category' => 'Hikes', 'operator' => 'Taita Trails', 'rating' => 4.6, 'reviews' => 31, 'price' => 3500, 'duration' => '1 day', 'color' => '#7FA66A', 'image' => null],
            ['id' => 6, 'title' => 'Sunset dhow cruise', 'place' => 'Diani', 'category' => 'Boat trips', 'operator' => 'Pwani Dhow Tours', 'rating' => 4.9, 'reviews' => 88, 'price' => 4000, 'duration' => 'Evening', 'color' => '#E59D6B', 'image' => 'diani'],
        ];
    }

    private function destinations(): array
    {
        return [
            ['place' => 'Masai Mara', 'tagline' => 'Safaris on the savannah', 'color' => '#D9A93E', 'image' => 'masai-mara'],
            ['place' => 'Diani', 'tagline' => 'White sand and dhow sunsets', 'color' => '#E6CFA0', 'image' => 'diani'],
            ['place' => 'Wasini', 'tagline' => 'Dolphins and coral gardens', 'color' => '#5FB3BF', 'image' => 'wasini'],
            ['place' => 'Mpunguti', 'tagline' => 'Marine park snorkelling', 'color' => '#3E9FB0', 'image' => null],
            ['place' => 'Chullu hills', 'tagline' => 'Sunrise hikes in Taita', 'color' => '#7FA66A', 'image' => null],
        ];
    }

    private function reviews(): array
    {
        return [
            ['The guide knew every animal by name and the price was exactly what we paid. No surprises.', 'Wanjiru', 'Masai Mara safari', 5.0],
            ['Booked on my phone in five minutes and chatted with the operator before paying.', 'Brian', 'Wasini dolphin trip', 4.9],
            ['Sunrise from the top was worth the early start. Our guide kept the pace easy for everyone.', 'Amina', 'Chullu hills hike', 4.7],
        ];
    }
}
