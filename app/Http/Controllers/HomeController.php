<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Video;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Number of posts shown in the "Latest Posts" section.
     */
    private const LATEST_COUNT = 6;

    /**
     * Number of posts shown in the "Trending / Popular" section.
     */
    private const TRENDING_COUNT = 5;

    /**
     * Number of posts shown in the "Featured" slot.
     */
    private const FEATURED_COUNT = 3;

    /**
     * Display the blog home page.
     *
     * Loads featured, latest, and trending posts along with top-level
     * categories that have at least one published post, and simple stats
     * for the site header / sidebar.
     */
    public function index(): View
    {
        // Featured posts: admin-flagged via is_featured; fall back to most-viewed
        $featuredPosts = Post::published()
            ->with(['category', 'categories', 'author'])
            ->featured()
            ->latestPublished()
            ->limit(self::FEATURED_COUNT)
            ->get();

        if ($featuredPosts->isEmpty()) {
            $featuredPosts = Post::published()
                ->with(['category', 'categories', 'author'])
                ->popular()
                ->limit(self::FEATURED_COUNT)
                ->get();
        }

        // Latest posts
        $latestPosts = Post::published()
            ->with(['category', 'categories', 'author'])
            ->latestPublished()
            ->limit(self::LATEST_COUNT)
            ->get();

        // Trending posts ranked by views + (approved comments × 3)
        $featuredIds = $featuredPosts->pluck('id');

        $trendingPosts = Post::published()
            ->with(['category', 'categories', 'author'])
            ->withCount('approvedComments')
            ->whereNotIn('id', $featuredIds)
            ->latestPublished()
            ->limit(50)
            ->get()
            ->sortByDesc(fn ($p) => $p->views_count + ($p->approved_comments_count * 3))
            ->take(self::TRENDING_COUNT)
            ->values();

        // All categories, ordered so populated ones appear first
        $homeCategories = Category::withCount(['posts' => function ($q) {
                $q->whereIn('status', ['published', 'scheduled'])
                  ->where('published_at', '<=', now());
            }])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->get();

        // Simple site stats (used in sidebar / footer widgets)
        $stats = [
            'total_posts'       => Post::published()->count(),
            'total_categories'  => Category::withPublishedPosts()->count(),
            'site_name'         => Setting::get('site_name', config('app.name')),
            'site_description'  => Setting::get('site_description'),
        ];

        // Single hero post (first featured post)
        $featuredPost = $featuredPosts->first();

        // Active videos for home page preview (max 3)
        $homeVideos = Video::active()->with('category')->ordered()->limit(3)->get();

        return view('home.index', compact(
            'featuredPost',
            'featuredPosts',
            'latestPosts',
            'trendingPosts',
            'homeCategories',
            'stats',
            'homeVideos'
        ));
    }
}
