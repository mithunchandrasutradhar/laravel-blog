<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Cache key prefix used when caching settings.
     */
    private const CACHE_KEY = 'settings.';

    /**
     * Cache TTL in seconds (24 hours).
     */
    private const CACHE_TTL = 86400;

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve the value of a setting by key.
     *
     * @param  string  $key      The setting key.
     * @param  mixed   $default  Fallback value when the key is not found.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember(
            self::CACHE_KEY . $key,
            self::CACHE_TTL,
            fn () => static::where('key', $key)->value('value')
        );

        return $value ?? $default;
    }

    /**
     * Persist (insert or update) a setting value and clear its cache entry.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  string  $group
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget(self::CACHE_KEY . $key);
    }

    /**
     * Remove a setting by key and clear its cache entry.
     */
    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY . $key);
    }

    /**
     * Return all settings for a given group as a key → value array.
     *
     * @param  string  $group
     * @return array<string, mixed>
     */
    public static function group(string $group): array
    {
        return Cache::remember(
            self::CACHE_KEY . 'group.' . $group,
            self::CACHE_TTL,
            fn () => static::where('group', $group)->pluck('value', 'key')->toArray()
        );
    }

    /**
     * Flush all cached settings.
     */
    public static function flushCache(): void
    {
        // Purge all cached entries that start with our prefix by re-caching
        // all keys currently stored in the database.
        static::all()->each(function (Setting $setting) {
            Cache::forget(self::CACHE_KEY . $setting->key);
        });
    }
}
