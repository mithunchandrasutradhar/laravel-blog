<?php

namespace App\Services;

use App\Models\Advertisement;
use Illuminate\Support\Facades\Cache;

class AdvertisementService
{
    /**
     * Cache TTL in seconds.
     */
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('blog.cache_ttl', 3600);
    }

    // -------------------------------------------------------------------------
    // Retrieval
    // -------------------------------------------------------------------------

    /**
     * Return a single active advertisement for a given position.
     * When multiple ads are active at the same position one is chosen at random.
     *
     * Returns null when no ad is configured for the position.
     */
    public function getForPosition(string $position): ?Advertisement
    {
        $ads = Cache::remember(
            "ads.position.{$position}",
            $this->cacheTtl,
            fn () => Advertisement::active()->atPosition($position)->get()
        );

        if ($ads->isEmpty()) {
            return null;
        }

        return $ads->random();
    }

    /**
     * Return all active advertisements for a given position.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Advertisement>
     */
    public function getAllForPosition(string $position): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "ads.all.{$position}",
            $this->cacheTtl,
            fn () => Advertisement::active()->atPosition($position)->get()
        );
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    /**
     * Render the HTML output for the given advertisement.
     *
     * For `adsense` type ads the raw embed code is returned as-is.
     * For `banner` type ads an <a> wrapping an <img> is generated.
     *
     * Returns an empty string when the advertisement is null or inactive.
     */
    public function renderAd(?Advertisement $ad): string
    {
        if (! $ad || ! $ad->is_active) {
            return '';
        }

        if ($ad->isAdsense()) {
            return $ad->code ?? '';
        }

        if ($ad->isBanner()) {
            $imageUrl = $ad->image_url;
            $link     = htmlspecialchars($ad->url ?? '#', ENT_QUOTES, 'UTF-8');
            $name     = htmlspecialchars($ad->name ?? 'Advertisement', ENT_QUOTES, 'UTF-8');

            if (! $imageUrl) {
                return '';
            }

            $imageSrc = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');

            return <<<HTML
<a href="{$link}" target="_blank" rel="noopener sponsored" class="advertisement-banner" aria-label="{$name}">
    <img src="{$imageSrc}" alt="{$name}" loading="lazy" class="ad-image">
</a>
HTML;
        }

        // Fallback: return raw code if type is unrecognised.
        return $ad->code ?? '';
    }

    /**
     * Shorthand: retrieve and render an ad for a position in one call.
     */
    public function renderForPosition(string $position): string
    {
        return $this->renderAd($this->getForPosition($position));
    }

    // -------------------------------------------------------------------------
    // Cache management
    // -------------------------------------------------------------------------

    /**
     * Flush all cached advertisement entries.
     */
    public function flushCache(): void
    {
        // Retrieve all distinct positions and purge their cache entries.
        Advertisement::distinct()->pluck('position')->each(function (string $pos) {
            Cache::forget("ads.position.{$pos}");
            Cache::forget("ads.all.{$pos}");
        });
    }
}
