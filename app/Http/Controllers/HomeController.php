<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
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
        // Featured posts: most-viewed published posts (editors may later add
        // a dedicated "featured" boolean column; for now we use views_count).
        $featuredPosts = Post::published()
            ->with(['category', 'author'])
            ->popular()
            ->limit(self::FEATURED_COUNT)
            ->get();

        // Latest posts
        $latestPosts = Post::published()
            ->with(['category', 'author'])
            ->latestPublished()
            ->limit(self::LATEST_COUNT)
            ->get();

        // Trending (popular) posts – exclude the featured ones to avoid repetition
        $featuredIds = $featuredPosts->pluck('id');

        $trendingPosts = Post::published()
            ->with(['category', 'author'])
            ->whereNotIn('id', $featuredIds)
            ->popular()
            ->limit(self::TRENDING_COUNT)
            ->get();

        // Top-level categories with post counts
        $categories = Category::topLevel()
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'published')
                  ->where('published_at', '<=', now());
            }])
            ->withPublishedPosts()
            ->orderByDesc('posts_count')
            ->get();

        // Simple site stats (used in sidebar / footer widgets)
        $stats = [
            'total_posts'       => Post::published()->count(),
            'total_categories'  => Category::withPublishedPosts()->count(),
            'site_name'         => Setting::get('site_name', config('app.name')),
            'site_description'  => Setting::get('site_description'),
        ];

        return view('home', compact(
            'featuredPosts',
            'latestPosts',
            'trendingPosts',
            'categories',
            'stats'
        ));
    }
}
