<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Create a new post, optionally attaching tags and a featured image.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPost(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $tagIds = $data['tags'] ?? [];
            unset($data['tags']);

            $data['user_id'] = $data['user_id'] ?? Auth::id();

            $post = Post::create($data);

            if (! empty($tagIds)) {
                $this->syncTags($post, $tagIds);
            }

            $this->flushPostCache();

            return $post->load(['category', 'tags', 'author']);
        });
    }

    /**
     * Update an existing post.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $tagIds = $data['tags'] ?? null;
            unset($data['tags']);

            // Regenerate slug only when the title changed and no explicit slug given.
            if (isset($data['title']) && $data['title'] !== $post->title && empty($data['slug'])) {
                $data['slug'] = Post::generateUniqueSlug($data['title'], $post->id);
            }

            $post->update($data);

            if ($tagIds !== null) {
                $this->syncTags($post, $tagIds);
            }

            $this->flushPostCache();

            return $post->fresh(['category', 'tags', 'author']);
        });
    }

    /**
     * Soft-delete a post and clean up associated media and tag pivots.
     */
    public function deletePost(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            // Remove featured image from storage.
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            // Detach all tags.
            $post->tags()->detach();

            // Soft-delete the post.
            $result = $post->delete();

            $this->flushPostCache();

            return (bool) $result;
        });
    }

    // -------------------------------------------------------------------------
    // Status transitions
    // -------------------------------------------------------------------------

    /**
     * Immediately publish a post.
     */
    public function publishPost(Post $post): Post
    {
        $post->update([
            'status'       => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        $this->flushPostCache();

        return $post;
    }

    /**
     * Retract a published post back to draft.
     */
    public function unpublishPost(Post $post): Post
    {
        $post->update(['status' => 'draft']);

        $this->flushPostCache();

        return $post;
    }

    /**
     * Schedule a post to be published at a future date/time.
     *
     * @param  \DateTimeInterface|string  $publishAt
     */
    public function schedulePost(Post $post, \DateTimeInterface|string $publishAt): Post
    {
        $post->update([
            'status'       => 'scheduled',
            'published_at' => $publishAt,
        ]);

        $this->flushPostCache();

        return $post;
    }

    // -------------------------------------------------------------------------
    // Retrieval helpers
    // -------------------------------------------------------------------------

    /**
     * Return paginated featured posts (pinned / manually curated).
     */
    public function getFeaturedPosts(int $limit = 5): Collection
    {
        return Cache::remember('posts.featured.' . $limit, config('blog.cache_ttl'), function () use ($limit) {
            return Post::published()
                ->with(['category', 'author'])
                ->orderByDesc('views_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Return the most recently published posts.
     */
    public function getLatestPosts(int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('blog.posts_per_page', 12);

        return Post::published()
            ->with(['category', 'author', 'tags'])
            ->latestPublished()
            ->paginate($perPage);
    }

    /**
     * Return trending posts ordered by view count over the last 7 days.
     */
    public function getTrendingPosts(int $limit = 10, int $days = 7): Collection
    {
        return Cache::remember("posts.trending.{$limit}.{$days}", config('blog.cache_ttl'), function () use ($limit, $days) {
            return Post::published()
                ->with(['category', 'author'])
                ->withCount(['postViews as recent_views' => function ($q) use ($days) {
                    $q->where('created_at', '>=', now()->subDays($days));
                }])
                ->orderByDesc('recent_views')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Return posts related to the given post (same category or shared tags).
     */
    public function getRelatedPosts(Post $post, int $limit = 4): Collection
    {
        return Cache::remember("posts.related.{$post->id}.{$limit}", config('blog.cache_ttl'), function () use ($post, $limit) {
            $tagIds = $post->tags->pluck('id');

            return Post::published()
                ->with(['category', 'author'])
                ->where('id', '!=', $post->id)
                ->where(function ($query) use ($post, $tagIds) {
                    $query->where('category_id', $post->category_id);

                    if ($tagIds->isNotEmpty()) {
                        $query->orWhereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds));
                    }
                })
                ->latestPublished()
                ->limit($limit)
                ->get();
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Sync tags by accepting a mixed array of IDs and/or name strings.
     *
     * @param  array<int|string>  $tags
     */
    private function syncTags(Post $post, array $tags): void
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
            } else {
                $tagIds[] = Tag::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($tag)],
                    ['name' => $tag]
                )->id;
            }
        }

        $post->tags()->sync($tagIds);
    }

    /**
     * Bust all known post-related cache keys.
     */
    private function flushPostCache(): void
    {
        foreach (['featured', 'trending', 'latest'] as $key) {
            Cache::forget("posts.{$key}");
        }
    }
}
