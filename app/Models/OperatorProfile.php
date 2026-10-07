<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorProfile extends Model
{
    protected $fillable = ['business_name', 'county', 'offerings'];

    protected function casts(): array
    {
        return ['offerings' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
