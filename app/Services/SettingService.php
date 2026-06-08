<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Cache prefix used for all setting entries.
     */
    private const CACHE_PREFIX = 'setting.';

    /**
     * Cache prefix for group lookups.
     */
    private const GROUP_PREFIX = 'setting.group.';

    /**
     * How long individual settings stay cached, in seconds.
     */
    private int $ttl;

    public function __construct()
    {
        $this->ttl = config('blog.cache_ttl', 3600);
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Retrieve a single setting value by key.
     *
     * @param  string  $key
     * @param  mixed   $default  Returned when the key does not exist in the database.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember(
            self::CACHE_PREFIX . $key,
            $this->ttl,
            fn () => Setting::where('key', $key)->value('value')
        );

        return $value ?? $default;
    }

    /**
     * Return all settings that belong to a given group as a key → value array.
     *
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        return Cache::remember(
            self::GROUP_PREFIX . $group,
            $this->ttl,
            fn () => Setting::where('group', $group)->pluck('value', 'key')->toArray()
        );
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    /**
     * Persist (insert or update) a single setting value and bust its cache.
     */
    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::GROUP_PREFIX . $group);
    }

    /**
     * Persist multiple settings at once, all belonging to the same group.
     *
     * @param  array<string, mixed>  $settings  Associative array of key → value pairs.
     */
    public function setBulk(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    // -------------------------------------------------------------------------
    // Cache
    // -------------------------------------------------------------------------

    /**
     * Clear every cached setting entry derived from the database.
     */
    public function clearCache(): void
    {
        // Forget individual key caches.
        Setting::all()->each(function (Setting $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget(self::GROUP_PREFIX . $setting->group);
        });
    }

    /**
     * Clear the cached value for a specific key only.
     */
    public function forgetKey(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }
}
