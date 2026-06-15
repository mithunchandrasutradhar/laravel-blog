<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Public posts per page.
     */
    private const PER_PAGE = 12;

    /**
     * Display a paginated listing of published posts.
     *
     * Supports optional filtering by:
     *  - category slug  (?category=slug)
     *  - tag slug       (?tag=slug)
     *  - sort order     (?sort=latest|popular|oldest)
     *  - keyword search (?q=keyword)
     */
    public function index(Request $request): View
    {
        $query = Post::published()->with(['category', 'categories', 'author', 'tags']);

        // Filter by category
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        // Keyword search
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Sorting
        match ($request->input('sort', 'latest')) {
            'popular' => $query->popular(),
            'oldest'  => $query->oldest('published_at'),
            default   => $query->latestPublished(),
        };

        $posts             = $query->paginate(self::PER_PAGE)->withQueryString();
        $filterCategories  = Category::withCount(['posts' => fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now())])->orderBy('name')->get();
        $filterAuthors     = User::has('posts')->orderBy('name')->get();
        $allTags           = Tag::withCount('posts')->orderByDesc('posts_count')->limit(30)->get();

        return view('blog.index', compact('posts', 'filterCategories', 'filterAuthors', 'allTags'));
    }

    /**
     * Display a single published post.
     *
     * The TrackPostView middleware handles view recording after the response
     * is built, so no manual tracking is needed here.
     */
    public function show(string $slug): View
    {
        $post = Post::with([
            'category',
            'categories',
            'author',
            'tags',
            'approvedComments.user',
            'approvedComments.replies.user',
            'approvedComments.replies.parent',
            'approvedComments.replies.replies.user',
            'approvedComments.replies.replies.parent',
            'approvedComments.replies.replies.replies.user',
            'approvedComments.replies.replies.replies.parent',
        ])->where('slug', $slug)->firstOrFail();

        // Allow authors / admins to preview unpublished posts
        if (! $post->isPublished()) {
            abort_unless(
                auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $post->user_id),
                404
            );
        }

        // Related posts: same category, excluding current, latest first
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with(['category', 'categories', 'author'])
            ->latestPublished()
            ->limit(3)
            ->get();

        // Previous / next navigation
        $previousPost = Post::published()
            ->where('published_at', '<', $post->published_at)
            ->latestPublished()
            ->first();

        $nextPost = Post::published()
            ->where('published_at', '>', $post->published_at)
            ->oldest('published_at')
            ->first();

        return view('blog.show', compact('post', 'relatedPosts', 'previousPost', 'nextPost'));
    }

    /**
     * Display posts saved/bookmarked by the authenticated user.
     */
    public function saved(Request $request): View
    {
        // Saved posts are stored in session for guests or a pivot table for
        // authenticated users. This implementation uses session-based storage
        // which can be extended to a database-backed approach.
        $savedIds = session('saved_posts', []);

        $posts = Post::published()
            ->whereIn('id', $savedIds)
            ->with(['category', 'categories', 'author'])
            ->latestPublished()
            ->paginate(self::PER_PAGE);

        return view('blog.saved', compact('posts'));
    }

    /**
     * Toggle a post save/bookmark for the current session.
     */
    public function toggleSave(Request $request, Post $post): JsonResponse
    {
        $saved = session('saved_posts', []);

        if (in_array($post->id, $saved)) {
            $saved = array_values(array_diff($saved, [$post->id]));
            $isSaved = false;
        } else {
            $saved[] = $post->id;
            $isSaved = true;
        }

        session(['saved_posts' => $saved]);

        return response()->json([
            'saved'       => $isSaved,
            'saved_count' => count($saved),
        ]);
    }
}
