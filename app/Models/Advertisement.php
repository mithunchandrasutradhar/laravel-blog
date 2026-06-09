<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'position',
        'code',
        'image',
        'url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only active advertisements.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Filter by position.
     */
    public function scopeAtPosition(Builder $query, string $position): void
    {
        $query->where('position', $position);
    }

    public function scopeByPosition(Builder $query, string $position): void
    {
        $query->where('position', $position);
    }

    /**
     * Filter by type.
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Return a random active advertisement for a given position.
     */
    public static function forPosition(string $position): ?static
    {
        return static::active()
                     ->atPosition($position)
                     ->inRandomOrder()
                     ->first();
    }

    /**
     * Return all active advertisements for a given position.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function allForPosition(string $position): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                     ->atPosition($position)
                     ->get();
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the full URL to the banner image.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Determine whether this is an AdSense-type advertisement.
     */
    public function isAdsense(): bool
    {
        return $this->type === 'adsense';
    }

    /**
     * Determine whether this is a banner-type advertisement.
     */
    public function isBanner(): bool
    {
        return $this->type === 'banner';
    }
}
