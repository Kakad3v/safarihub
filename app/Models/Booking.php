<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'balance_due_on' => 'date',
            'expires_at' => 'datetime',
        ];
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereIn('status', ['deposit_paid', 'paid'])
                ->orWhere(fn(Builder $p) => $p->where('status', 'pending')->where('expires_at', '>', now()));
        });
    }

    public function paidAmount(): int
    {
        return (int) $this->payments()->where('status', 'success')->sum('amount');
    }

    public function holdIsActive(): bool
    {
        return $this->status === 'pending' && $this->expires_at?->isFuture();
    }

    public static function newReference(): string
    {
        do {
            $reference = 'SH-' . Str::upper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
