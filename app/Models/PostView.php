<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostView extends Model
{
    /**
     * Disable the updated_at column — post_views only has created_at.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'ip_address',
        'user_agent',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The post this view record belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Filter views within a given number of minutes (for deduplication).
     */
    public function scopeRecentFromIp(Builder $query, string $ip, int $minutes = 60): void
    {
        $query->where('ip_address', $ip)
              ->where('created_at', '>=', now()->subMinutes($minutes));
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Record a view for a post, skipping duplicates from the same IP within
     * the throttle window, and increment the post's cached views_count.
     *
     * @param  Post    $post
     * @param  string  $ipAddress
     * @param  string  $userAgent
     * @param  int     $throttleMinutes  Minimum gap between views from the same IP.
     * @return bool  True when the view was recorded, false when throttled.
     */
    public static function record(
        Post $post,
        string $ipAddress,
        string $userAgent = '',
        int $throttleMinutes = 60
    ): bool {
        $alreadyViewed = static::where('post_id', $post->id)
            ->recentFromIp($ipAddress, $throttleMinutes)
            ->exists();

        if ($alreadyViewed) {
            return false;
        }

        static::create([
            'post_id'    => $post->id,
            'ip_address' => $ipAddress,
            'user_agent' => mb_substr($userAgent, 0, 500),
        ]);

        $post->increment('views_count');

        return true;
    }
}
