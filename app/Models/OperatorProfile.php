<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OperatorProfile extends Model
{
    protected $fillable = ['business_name', 'slug', 'county', 'bio', 'offerings'];

    protected function casts(): array
    {
        return ['offerings' => 'array'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            if (blank($profile->slug)) {
                $profile->slug = Str::slug($profile->business_name) . '-' . Str::lower(Str::random(4));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
