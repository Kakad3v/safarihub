<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'reference',
        'gateway',
        'purpose',
        'amount',
        'currency',
        'channel',
        'status',
        'gateway_id',
        'paid_at',
        'raw',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'raw' => 'array'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public static function newReference(): string
    {
        do {
            $reference = 'SHP-' . Str::upper(Str::random(10));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
