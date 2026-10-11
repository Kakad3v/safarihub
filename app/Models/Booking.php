<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'package_id',
        'departure_id',
        'user_id',
        'travelers',
        'total',
        'deposit',
        'balance',
        'balance_due_on',
        'status',
    ];

    protected function casts(): array
    {
        return ['balance_due_on' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['deposit_paid', 'paid']);
    }

    public static function newReference(): string
    {
        do {
            $reference = 'SH-' . Str::upper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
