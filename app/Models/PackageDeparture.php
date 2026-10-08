<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageDeparture extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_on' => 'date'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
