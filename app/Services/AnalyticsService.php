<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Cache TTL in seconds.
     */
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('blog.cache_ttl', 3600);
    }

    // -------------------------------------------------------------------------
    // Dashboard overview
    // -------------------------------------------------------------------------

    /**
     * Return an aggregate statistics array suitable for the admin dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('analytics.dashboard_stats', $this->cacheTtl, function () {
            $totalPosts      = Post::withoutTrashed()->count();
            $publishedPosts  = Post::published()->count();
            $draftPosts      = Post::draft()->count();
            $scheduledPosts  = Post::scheduled()->count();

            $totalComments   = Comment::count();
            $pendingComments = Comment::pending()->count();

            $totalViews      = (int) PostView::sum('id') ?: Post::sum('views_count');
            $todayViews      = PostView::whereDate('created_at', today())->count();

            $totalUsers       = User::count();
            $subscribers      = Subscriber::verified()->count();

            return compact(
                'totalPosts',
                'publishedPosts',
                'draftPosts',
                'scheduledPosts',
                'totalComments',
                'pendingComments',
                'totalViews',
                'todayViews',
                'totalUsers',
                'subscribers'
            );
        });
    }

    // -------------------------------------------------------------------------
    // Popular posts
    // -------------------------------------------------------------------------

    /**
     * Return the most-viewed published posts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularPosts(int $limit = 10, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("analytics.popular_posts.{$limit}.{$days}", $this->cacheTtl, function () use ($limit, $days) {
            return Post::published()
                ->with(['category', 'author'])
                ->withCount(['postViews as period_views' => function ($q) use ($days) {
                    $q->where('post_views.created_at', '>=', now()->subDays($days));
                }])
                ->orderByDesc('period_views')
                ->limit($limit)
                ->get();
        });
    }

    // -------------------------------------------------------------------------
    // Views chart
    // -------------------------------------------------------------------------

    /**
     * Return daily view counts grouped by date for the last N days.
     * Suitable for rendering a line/bar chart on the dashboard.
     *
     * @return array<int, array{date: string, views: int}>
     */
    public function getViewsChart(int $days = 30): array
    {
        return Cache::remember("analytics.views_chart.{$days}", $this->cacheTtl, function () use ($days) {
            $rows = PostView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            )
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Fill in zero-view days so the chart has a continuous range.
            $chart = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date    = now()->subDays($i)->toDateString();
                $chart[] = [
                    'date'  => $date,
                    'views' => $rows->has($date) ? (int) $rows[$date]->views : 0,
                ];
            }

            return $chart;
        });
    }

    // -------------------------------------------------------------------------
    // Comment statistics
    // -------------------------------------------------------------------------

    /**
     * Return comment statistics grouped by status.
     *
     * @return array<string, int>
     */
    public function getCommentStats(): array
    {
        return Cache::remember('analytics.comment_stats', $this->cacheTtl, function () {
            $stats = Comment::select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            return [
                'total'    => array_sum($stats),
                'approved' => (int) ($stats['approved'] ?? 0),
                'pending'  => (int) ($stats['pending'] ?? 0),
                'rejected' => (int) ($stats['rejected'] ?? 0),
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Cache management
    // -------------------------------------------------------------------------

    /**
     * Flush all analytics cache entries.
     */
    public function flushCache(): void
    {
        Cache::forget('analytics.dashboard_stats');
        Cache::forget('analytics.comment_stats');
    }
}
