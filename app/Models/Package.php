<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Package extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'slug',
        'category',
        'place',
        'county',
        'summary',
        'description',
        'duration_days',
        'nights',
        'max_group',
        'price',
        'deposit_percent',
        'pickup_location',
        'pickup_time',
        'meeting_details',
        'languages',
        'includes',
        'excludes',
        'itinerary',
        'featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'itinerary' => 'array',
            'featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PackagePhoto::class)->orderBy('position');
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(Review::class)->latestOfMany();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function durationLabel(): string
    {
        return $this->duration_days > 1
            ? "{$this->duration_days} days, {$this->nights} nights"
            : '1 day';
    }

    public function quote(int $travelers, Departure $departure): array
    {
        $total = $this->price * $travelers;
        $deposit = (int) round($total * $this->deposit_percent / 100);

        return [
            'total' => $total,
            'deposit' => $deposit,
            'balance' => $total - $deposit,
            'due_on' => $departure->balanceDueOn(),
        ];
    }
}
