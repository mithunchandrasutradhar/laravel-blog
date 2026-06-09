<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'user_id',
        'featured_image',
        'short_description',
        'content',
        'meta_title',
        'meta_description',
        'canonical_url',
        'status',
        'published_at',
        'views_count',
        'reading_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count'  => 'integer',
            'reading_time' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Boot / Auto-slug
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title);
            }

            if (empty($post->reading_time)) {
                $post->reading_time = static::estimateReadingTime($post->content ?? '');
            }
        });

        static::updating(function (Post $post) {
            if ($post->isDirty('content')) {
                $post->reading_time = static::estimateReadingTime($post->content ?? '');
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The user (author) who wrote this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user() to allow $post->author syntax.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The category this post belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The tags attached to this post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    /**
     * All comments on this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Only top-level (non-reply) approved comments.
     */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)
                    ->whereNull('parent_id')
                    ->where('status', 'approved')
                    ->latest();
    }

    /**
     * View records for this post.
     */
    public function postViews(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only published posts.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->where('published_at', '<=', now());
    }

    /**
     * Only draft posts.
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    /**
     * Only scheduled posts.
     */
    public function scopeScheduled(Builder $query): void
    {
        $query->where('status', 'scheduled')
              ->where('published_at', '>', now());
    }

    /**
     * Filter posts by category.
     */
    public function scopeInCategory(Builder $query, int|string $category): void
    {
        if (is_numeric($category)) {
            $query->where('category_id', $category);
        } else {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }
    }

    /**
     * Filter posts by tag.
     */
    public function scopeWithTag(Builder $query, int|string $tag): void
    {
        $query->whereHas('tags', function ($q) use ($tag) {
            if (is_numeric($tag)) {
                $q->where('tags.id', $tag);
            } else {
                $q->where('tags.slug', $tag);
            }
        });
    }

    /**
     * Full-text search across title, short_description, and content.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->whereRaw(
            'MATCH(title, short_description, content) AGAINST(? IN BOOLEAN MODE)',
            [$term . '*']
        );
    }

    /**
     * Order by most viewed.
     */
    public function scopePopular(Builder $query): void
    {
        $query->orderByDesc('views_count');
    }

    /**
     * Order by most recent publication date.
     */
    public function scopeLatestPublished(Builder $query): void
    {
        $query->orderByDesc('published_at');
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the canonical URL for the post.
     */
    public function getUrlAttribute(): string
    {
        return $this->canonical_url ?: route('blog.show', $this->slug);
    }

    /**
     * Return the full URL to the featured image.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
    }

    /**
     * Determine whether the post is live and visible to the public.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Increment the cached views_count counter.
     */
    public function incrementViewCount(): void
    {
        $this->increment('views_count');
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a URL-safe slug that is unique within the posts table.
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base  = Str::slug($title);
        $slug  = $base;
        $count = 1;

        while (
            static::where('slug', $slug)
                  ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                  ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Estimate reading time in minutes from raw content.
     * Assumes an average reading speed of 200 words per minute.
     */
    public static function estimateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes   = (int) ceil($wordCount / 200);

        return max(1, $minutes);
    }
}
