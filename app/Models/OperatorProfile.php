<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OperatorProfile extends Model
{
    protected $fillable = ['business_name', 'county', 'offerings'];

    protected static function booted(): void
    {
        static::creating(function (OperatorProfile $profile) {
            $base = Str::slug($profile->business_name) ?: 'operator';

            $slug = $base;
            
            $i = 2;

            while (static::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $profile->slug ??= $slug;
        });
    }

    protected function casts(): array
    {
        return ['offerings' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
