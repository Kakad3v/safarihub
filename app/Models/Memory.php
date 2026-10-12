<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Memory extends Model
{
    protected $fillable = ['user_id', 'package_id', 'path', 'caption', 'share_token', 'taken_on'];

    protected function casts(): array
    {
        return ['taken_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn() => Storage::disk('public')->url($this->path));
    }
}
