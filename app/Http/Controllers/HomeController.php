<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const LATEST_COUNT   = 6;
    private const TRENDING_COUNT = 5;
    private const FEATURED_COUNT = 3;
    private const CACHE_TTL      = 900; // 15 minutes

    public function index(): View
    {
        $data = Cache::remember('home.page_data', self::CACHE_TTL, function () {

            // Featured posts
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

            // Trending posts — sorted by MySQL, returns exactly TRENDING_COUNT rows
            $featuredIds = $featuredPosts->pluck('id')->all();

            $trendingPosts = Post::published()
                ->with(['category', 'author'])
                ->withCount('approvedComments')
                ->when($featuredIds, fn ($q) => $q->whereNotIn('id', $featuredIds))
                ->orderByRaw(
                    '(views_count + (SELECT COUNT(*) FROM comments c WHERE c.post_id = posts.id AND c.status = ?) * 3) DESC',
                    ['approved']
                )
                ->limit(self::TRENDING_COUNT)
                ->get();

            // Categories with published post counts
            $homeCategories = Category::withCount(['posts' => fn ($q) => $q->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->get();

            // Site stats
            $stats = [
                'total_posts'      => Post::published()->count(),
                'total_categories' => Category::withPublishedPosts()->count(),
                'site_name'        => Setting::get('site_name', config('app.name')),
                'site_description' => Setting::get('site_description'),
            ];

            // Videos
            $homeVideos = Video::active()->with('category')->ordered()->limit(3)->get();

            return compact(
                'featuredPosts',
                'latestPosts',
                'trendingPosts',
                'homeCategories',
                'stats',
                'homeVideos'
            );
        });

        $data['featuredPost'] = $data['featuredPosts']->first();

        return view('home.index', $data);
    }
}
