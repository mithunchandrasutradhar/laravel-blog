<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request): View
    {
        // Date range – default to last 30 days
        $days  = (int) $request->input('days', 30);
        $days  = in_array($days, [7, 30, 90, 365]) ? $days : 30;
        $start = now()->subDays($days - 1)->startOfDay();

        // --- Daily views chart ---
        $dailyViews = PostView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as views')
        )
        ->where('created_at', '>=', $start)
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $chartLabels = [];
        $chartData   = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartData[]   = $dailyViews->get($date)?->views ?? 0;
        }

        // --- Top posts by views in the period ---
        $topPosts = Post::select('posts.*', DB::raw('COUNT(post_views.id) as period_views'))
            ->join('post_views', 'posts.id', '=', 'post_views.post_id')
            ->where('post_views.created_at', '>=', $start)
            ->groupBy('posts.id')
            ->orderByDesc('period_views')
            ->limit(20)
            ->with('category')
            ->get();

        // --- Views summary ---
        $totalViews   = PostView::where('created_at', '>=', $start)->count();
        $uniqueVisitors = PostView::where('created_at', '>=', $start)
            ->distinct('ip_address')
            ->count('ip_address');

        // --- Top referrers by user-agent device type (simplified) ---
        // A real referrer would require storing the HTTP_REFERER header.
        // We give a UA-based breakdown as a proxy metric.
        $deviceTypes = PostView::select(
            DB::raw("
                CASE
                    WHEN LOWER(user_agent) LIKE '%mobile%' THEN 'Mobile'
                    WHEN LOWER(user_agent) LIKE '%tablet%' THEN 'Tablet'
                    ELSE 'Desktop'
                END as device"),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $start)
        ->groupBy('device')
        ->orderByDesc('count')
        ->get();

        // --- Posts with zero views (content gap) ---
        $unreadPosts = Post::published()
            ->where('views_count', 0)
            ->with('category')
            ->latest('published_at')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', compact(
            'chartLabels',
            'chartData',
            'topPosts',
            'totalViews',
            'uniqueVisitors',
            'deviceTypes',
            'unreadPosts',
            'days'
        ));
    }
}
