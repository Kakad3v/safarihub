<?php

namespace App\Models;

use App\Enums\CancellationPolicy;
use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Package extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => PackageType::class,
            'status' => PackageStatus::class,
            'cancellation_policy' => CancellationPolicy::class,
            'weekdays' => 'array',
            'duration_hours' => 'float',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Package $package) {
            $package->slug ??= static::uniqueSlug($package->title);
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'package';
        $slug = $base;
        $i = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(PackageDay::class)->orderBy('day_number');
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(PackageInclusion::class)->orderBy('sort_order');
    }

    public function included(): HasMany
    {
        return $this->inclusions()->where('is_included', true);
    }

    public function excluded(): HasMany
    {
        return $this->inclusions()->where('is_included', false);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(PackageDeparture::class)->orderBy('starts_on');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PackageStatus::Published)
            ->whereHas('operator', fn($q) => $q->where('status', 'verified'));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')->fit(Fit::Crop, 800, 600)->format('webp')->nonQueued();
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn() => $this->getFirstMediaUrl('photos', 'card') ?: null);
    }

    protected function durationLabel(): Attribute
    {
        return Attribute::get(fn() => $this->type === PackageType::Trip
            ? $this->duration_days . ' ' . Str::plural('day', $this->duration_days)
            : (float) $this->duration_hours . ' ' . Str::plural('hour', (int) ceil($this->duration_hours)));
    }
}
