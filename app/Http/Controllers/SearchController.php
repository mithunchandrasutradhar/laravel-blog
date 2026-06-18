<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Minimum keyword length to trigger a FULLTEXT search.
     */
    private const MIN_QUERY_LENGTH = 2;

    /**
     * Results per page.
     */
    private const PER_PAGE = 12;

    /**
     * Display full-text search results.
     *
     * Uses the MySQL FULLTEXT index defined on (title, short_description, content)
     * via the Post::scopeSearch() scope. Falls back to a LIKE query when the
     * term is too short for FULLTEXT.
     */
    public function index(Request $request): View
    {
        $query = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], trim($request->input('q', '')));

        $posts = null;
        $total = 0;

        if (strlen($query) >= self::MIN_QUERY_LENGTH) {
            $postQuery = Post::published()->with(['category', 'author', 'tags']);

            if (strlen($query) >= 4) {
                // Use FULLTEXT BOOLEAN MODE for adequate-length queries
                $postQuery->search($query);
            } else {
                // Short term – fall back to simple LIKE search
                $postQuery->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('short_description', 'LIKE', "%{$query}%");
                });
            }

            $posts = $postQuery->latestPublished()->paginate(self::PER_PAGE)->withQueryString();
            $total = $posts->total();
        }

        return view('search.index', compact('posts', 'query', 'total'));
    }
}
