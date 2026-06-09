<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Minimum query length for autocomplete search.
     */
    private const MIN_LENGTH = 2;

    /**
     * Maximum number of autocomplete suggestions to return.
     */
    private const LIMIT = 10;

    /**
     * Return JSON autocomplete suggestions for the given search term.
     *
     * Used by the frontend search input to provide live suggestions without
     * a full page reload.
     */
    public function index(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < self::MIN_LENGTH) {
            return response()->json([]);
        }

        $posts = Post::published()
            ->select(['id', 'title', 'slug', 'short_description', 'featured_image'])
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(self::LIMIT)
            ->get()
            ->map(fn ($post) => [
                'id'          => $post->id,
                'title'       => $post->title,
                'url'         => route('blog.show', $post->slug),
                'description' => $post->short_description
                    ? str($post->short_description)->limit(80)->toString()
                    : null,
                'image'       => $post->featured_image_url,
            ]);

        return response()->json($posts);
    }
}
