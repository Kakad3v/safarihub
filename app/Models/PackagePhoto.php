<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PackagePhoto extends Model
{
    protected $fillable = ['package_id', 'path', 'alt', 'focus', 'position'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn() => str_starts_with($this->path, '/') || str_starts_with($this->path, 'http')
            ? $this->path
            : Storage::disk('public')->url($this->path));
    }
}
