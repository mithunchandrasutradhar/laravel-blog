<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        // ── Date range ──────────────────────────────────────────────────────────
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // Clamp: from must not be after to
        if ($from->gt($to)) {
            $from = $to->copy()->subDays(29)->startOfDay();
        }

        // Previous period (same length, immediately before)
        $periodDays = (int) $from->diffInDays($to) + 1;
        $prevFrom   = $from->copy()->subDays($periodDays)->startOfDay();
        $prevTo     = $from->copy()->subSecond();

        // ── Daily views chart ────────────────────────────────────────────────────
        $dailyRows = PostView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT ip_address) as unique_views')
            )
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels     = [];
        $chartData       = [];
        $uniqueChartData = [];

        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $date              = $cursor->toDateString();
            $chartLabels[]     = $cursor->format('M d');
            $chartData[]       = (int) ($dailyRows->get($date)?->views ?? 0);
            $uniqueChartData[] = (int) ($dailyRows->get($date)?->unique_views ?? 0);
            $cursor->addDay();
        }

        // ── Summary stats ────────────────────────────────────────────────────────
        $totalViews  = PostView::whereBetween('created_at', [$from, $to])->count();
        $uniqueViews = PostView::whereBetween('created_at', [$from, $to])
            ->distinct('ip_address')->count('ip_address');

        $prevTotalViews  = PostView::whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $prevUniqueViews = PostView::whereBetween('created_at', [$prevFrom, $prevTo])
            ->distinct('ip_address')->count('ip_address');

        $viewsChange  = $prevTotalViews  > 0
            ? round((($totalViews  - $prevTotalViews)  / $prevTotalViews)  * 100, 1) : 0;
        $uniqueChange = $prevUniqueViews > 0
            ? round((($uniqueViews - $prevUniqueViews) / $prevUniqueViews) * 100, 1) : 0;

        $totalPublished  = Post::published()->count();
        $avgViewsPerPost = $totalPublished > 0 ? round($totalViews / $totalPublished, 1) : 0;

        $stats = [
            'total_views'           => $totalViews,
            'views_change'          => $viewsChange,
            'unique_views'          => $uniqueViews,
            'unique_change'         => $uniqueChange,
            'avg_views_per_post'    => $avgViewsPerPost,
            'published_in_period'   => Post::published()->whereBetween('published_at', [$from, $to])->count(),
            'comments_in_period'    => Comment::whereBetween('created_at', [$from, $to])->count(),
            'subscribers_in_period' => Subscriber::whereBetween('created_at', [$from, $to])->count(),
            'new_users_in_period'   => User::whereBetween('created_at', [$from, $to])->count(),
        ];

        // ── Top 10 posts by views in period ─────────────────────────────────────
        $topPosts = Post::select('posts.*', DB::raw('COUNT(post_views.id) as period_views'))
            ->join('post_views', 'posts.id', '=', 'post_views.post_id')
            ->whereBetween('post_views.created_at', [$from, $to])
            ->groupBy('posts.id')
            ->orderByDesc('period_views')
            ->limit(10)
            ->with('category')
            ->get();

        // ── Device breakdown ─────────────────────────────────────────────────────
        $deviceTypes = PostView::select(
                DB::raw("
                    CASE
                        WHEN LOWER(user_agent) LIKE '%mobile%' THEN 'Mobile'
                        WHEN LOWER(user_agent) LIKE '%tablet%' THEN 'Tablet'
                        ELSE 'Desktop'
                    END as device"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('device')
            ->orderByDesc('count')
            ->get();

        // ── Category stats (views in period) ─────────────────────────────────────
        $categoryStats = Category::select('categories.*', DB::raw('COUNT(post_views.id) as total_views'))
            ->join('posts', 'categories.id', '=', 'posts.category_id')
            ->join('post_views', 'posts.id', '=', 'post_views.post_id')
            ->whereBetween('post_views.created_at', [$from, $to])
            ->groupBy('categories.id')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', compact(
            'chartLabels',
            'chartData',
            'uniqueChartData',
            'topPosts',
            'stats',
            'deviceTypes',
            'categoryStats',
            'from',
            'to',
        ));
    }
}
