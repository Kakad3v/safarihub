<?php

namespace App\Livewire\Trips;

use App\Models\Booking;
use App\Models\Departure;
use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Tripshow extends Component
{
    public Package $package;

    public ?int $departureId = null;

    public int $travelers = 2;

    public bool $saved = false;

    public function mount(Package $package): void
    {
        abort_unless($package->status === 'published' || Auth::id() === $package->user_id, 404);

        $this->package = $package->load(['user.operatorProfile', 'photos']);
        $this->saved = in_array($package->id, session('saved_trips', []), true);
        $this->departureId = $this->departures->first(fn($d) => $d->seatsLeft() > 0)?->id;
        $this->travelers = max(1, min($this->travelers, $this->maxTravelers));
    }

    #[Computed]
    public function departures(): Collection
    {
        return $this->package->departures()
            ->upcoming()
            ->withSum(['bookings as booked' => fn($query) => $query->active()], 'travelers')
            ->get();
    }

    #[Computed]
    public function departure(): ?Departure
    {
        return $this->departures->firstWhere('id', $this->departureId);
    }

    #[Computed]
    public function maxTravelers(): int
    {
        return max(1, min($this->departure?->seatsLeft() ?? 0, $this->package->max_group));
    }

    #[Computed]
    public function quote(): ?array
    {
        return $this->departure
            ? $this->package->quote($this->travelers, $this->departure)
            : null;
    }

    #[Computed]
    public function reviewStats(): array
    {
        $count = $this->package->reviews()->count();

        return [
            'count' => $count,
            'average' => $count ? round($this->package->reviews()->avg('rating'), 1) : null,
            'spread' => $this->package->reviews()
                ->selectRaw('rating, count(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating'),
            'latest' => $this->package->reviews()->with('user:id,name')->latest()->limit(3)->get(),
        ];
    }

    public function selectDeparture(int $id): void
    {
        $departure = $this->departures->firstWhere('id', $id);

        if (! $departure || $departure->seatsLeft() < 1) {
            return;
        }

        $this->departureId = $id;
        unset($this->departure, $this->maxTravelers, $this->quote);
        $this->travelers = min($this->travelers, $this->maxTravelers);
        $this->resetErrorBag();
    }

    public function adjustTravelers(int $delta): void
    {
        $this->travelers = max(1, min($this->maxTravelers, $this->travelers + $delta));
        unset($this->quote);
        $this->resetErrorBag();
    }

    public function toggleSave(): void
    {
        $saved = session('saved_trips', []);
        $this->saved = ! in_array($this->package->id, $saved, true);

        session(['saved_trips' => $this->saved
            ? [...$saved, $this->package->id]
            : array_values(array_diff($saved, [$this->package->id]))]);
    }

    public function reserve(): void
    {
        if (! $this->departure) {
            $this->addError('departure', 'Choose a date that still has seats.');

            return;
        }

        if (! Auth::check()) {
            session(['url.intended' => route('trips.show', $this->package)]);
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if (Auth::id() === $this->package->user_id) {
            $this->addError('departure', 'You cannot book your own trip.');

            return;
        }

        $booking = DB::transaction(function () {
            $departure = Departure::query()->lockForUpdate()->findOrFail($this->departureId);
            $booked = (int) $departure->bookings()->active()->sum('travelers');

            if ($this->travelers > $departure->seats - $booked) {
                return null;
            }

            $quote = $this->package->quote($this->travelers, $departure);

            return Booking::create([
                'reference' => Booking::newReference(),
                'package_id' => $this->package->id,
                'departure_id' => $departure->id,
                'user_id' => Auth::id(),
                'travelers' => $this->travelers,
                'total' => $quote['total'],
                'deposit' => $quote['deposit'],
                'balance' => $quote['balance'],
                'balance_due_on' => $quote['due_on'],
                'status' => 'pending',
                'expires_at' => now()->addMinutes(config('safarihub.booking.hold_minutes')),
            ]);
        });

        if (! $booking) {
            $this->addError('travelers', 'Not enough seats left. Try fewer travelers or another date.');
            unset($this->departures, $this->departure, $this->maxTravelers, $this->quote);

            return;
        }

        $this->redirectRoute('bookings.pay', $booking, navigate: true);
    }

    public function render()
    {
        return view('livewire.trips.trip-show')->title($this->package->title);
    }
}
