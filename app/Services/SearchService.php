<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Perform a full-text search against published posts.
     *
     * The MySQL FULLTEXT index must cover the `title`, `short_description`,
     * and `content` columns on the `posts` table.
     *
     * @param  string               $query    The raw search term entered by the user.
     * @param  array<string, mixed> $filters  Optional filters:
     *                                          - category_id  int
     *                                          - tag          string (slug)
     *                                          - date_from    string (Y-m-d)
     *                                          - date_to      string (Y-m-d)
     *                                          - author_id    int
     * @param  int                  $perPage  Results per page.
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], int $perPage = null): LengthAwarePaginator
    {
        $perPage   = $perPage ?? config('blog.posts_per_page', 12);
        $sanitized = $this->sanitizeQuery($query);

        $builder = Post::published()
            ->with(['category', 'author', 'tags'])
            ->select('posts.*')
            ->when(! empty($sanitized), function ($q) use ($sanitized) {
                // Add a relevance score so we can order by it.
                $q->selectRaw(
                    'MATCH(title, short_description, content) AGAINST(? IN BOOLEAN MODE) AS relevance',
                    [$sanitized]
                )->whereRaw(
                    'MATCH(title, short_description, content) AGAINST(? IN BOOLEAN MODE)',
                    [$sanitized]
                )->orderByDesc('relevance');
            });

        // ------------------------------------------------------------------
        // Optional filters
        // ------------------------------------------------------------------

        if (! empty($filters['category_id'])) {
            $builder->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['tag'])) {
            $builder->whereHas('tags', fn ($q) => $q->where('tags.slug', $filters['tag']));
        }

        if (! empty($filters['author_id'])) {
            $builder->where('user_id', (int) $filters['author_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->whereDate('published_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->whereDate('published_at', '<=', $filters['date_to']);
        }

        return $builder->paginate($perPage)->withQueryString();
    }

    /**
     * Retrieve search suggestions (post titles only) for an autocomplete field.
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, title: string, url: string}>
     */
    public function suggest(string $query, int $limit = 8): \Illuminate\Support\Collection
    {
        $sanitized = $this->sanitizeQuery($query);

        if (empty($sanitized)) {
            return collect();
        }

        return Post::published()
            ->select(['id', 'title', 'slug'])
            ->whereRaw(
                'MATCH(title, short_description, content) AGAINST(? IN BOOLEAN MODE)',
                [$sanitized]
            )
            ->limit($limit)
            ->get()
            ->map(fn (Post $post) => [
                'id'    => $post->id,
                'title' => $post->title,
                'url'   => route('posts.show', $post->slug),
            ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Sanitize and convert a plain-language query into a MySQL BOOLEAN MODE
     * compatible search string.
     *
     * Each word gets a leading `+` so that all terms are required; the last
     * word also gets a trailing `*` to support prefix matching.
     */
    private function sanitizeQuery(string $query): string
    {
        $query = trim($query);

        if (empty($query)) {
            return '';
        }

        // Strip characters that have special meaning in BOOLEAN MODE to prevent
        // SQL errors.
        $query = preg_replace('/[+\-><()\~*@"]+/', ' ', $query);
        $query = preg_replace('/\s+/', ' ', $query);
        $words = array_filter(explode(' ', trim($query)));

        if (empty($words)) {
            return '';
        }

        $terms = [];
        $last  = array_pop($words);

        foreach ($words as $word) {
            if (strlen($word) >= 3) {
                $terms[] = '+' . $word;
            }
        }

        // Append wildcard to the final word for prefix-based matching.
        if (strlen($last) >= 3) {
            $terms[] = '+' . $last . '*';
        }

        return implode(' ', $terms);
    }
}
