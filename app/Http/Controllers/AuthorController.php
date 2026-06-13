<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class AuthorController extends Controller
{
    /**
     * Posts per page.
     */
    private const PER_PAGE = 12;

    /**
     * Display an author's public profile with their published posts.
     *
     * The route uses {username} instead of {id} so we do a manual lookup.
     * Laravel's route model binding cannot resolve by "name" directly, so we
     * query by name slug convention or add a getRouteKeyName override.
     */
    public function show(string $username): View
    {
        // Look up the author by name converted to a URL-friendly username.
        // Assumes User::name is stored as the display name and a virtual
        // "username" slug matches str_slug(name). If a dedicated `username`
        // column exists this can be swapped to a direct where('username') call.
        $author = is_numeric($username)
            ? User::findOrFail((int) $username)
            : User::whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($username)])->firstOrFail();

        // Ensure author is active and has the author/admin role
        abort_if(! $author->isActive(), 404);

        $posts = Post::published()
            ->where('user_id', $author->id)
            ->with(['category', 'tags', 'author'])
            ->latestPublished()
            ->paginate(self::PER_PAGE);

        $stats = [
            'total_posts'     => Post::published()->where('user_id', $author->id)->count(),
            'total_views'     => Post::where('user_id', $author->id)->sum('views_count'),
            'total_comments'  => Post::where('posts.user_id', $author->id)
                ->join('comments', 'posts.id', '=', 'comments.post_id')
                ->where('comments.status', 'approved')
                ->count(),
        ];

        return view('authors.show', compact('author', 'posts', 'stats'));
    }
}
