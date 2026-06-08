<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    // -------------------------------------------------------------------------
    // Boot / Auto-slug
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = static::generateUniqueSlug($tag->name);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The posts associated with this tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }

    /**
     * Published posts associated with this tag.
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags')
                    ->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only tags that have at least one published post.
     */
    public function scopeWithPublishedPosts(Builder $query): void
    {
        $query->whereHas('posts', function ($q) {
            $q->where('status', 'published')
              ->where('published_at', '<=', now());
        });
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the number of published posts that use this tag.
     */
    public function getPublishedPostsCountAttribute(): int
    {
        return $this->publishedPosts()->count();
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Find an existing tag by name (case-insensitive) or create a new one.
     */
    public static function findOrCreateByName(string $name): static
    {
        return static::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        );
    }

    /**
     * Generate a unique slug for the tag.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base  = Str::slug($name);
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
}
