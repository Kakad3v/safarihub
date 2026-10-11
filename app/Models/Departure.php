<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonInterface;

class Departure extends Model
{
    protected $fillable = ['package_id', 'starts_on', 'ends_on', 'seats'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_on', '>=', today())->orderBy('starts_on');
    }

    public function seatsLeft(): int
    {
        $booked = $this->booked ?? $this->bookings()->active()->sum('travelers');

        return max(0, $this->seats - (int) $booked);
    }

    public function balanceDueOn(): CarbonInterface
    {
        return $this->starts_on->copy()->subDays(config('safarihub.booking.balance_due_days'));
    }

    public function rangeLabel(): string
    {
        return $this->starts_on->isSameMonth($this->ends_on)
            ? $this->starts_on->format('j') . ' to ' . $this->ends_on->format('j M')
            : $this->starts_on->format('j M') . ' to ' . $this->ends_on->format('j M');
    }
}
