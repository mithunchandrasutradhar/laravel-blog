<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now       = now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // --- Core counts ---
        $totalPosts    = Post::count();
        $totalUsers    = User::count();
        $totalViews    = Post::sum('views_count');
        $totalComments = Comment::count();
        $pendingComments = Comment::pending()->count();

        // --- Trend vs last month (percentage change) ---
        $postsThisMonth = Post::where('created_at', '>=', $thisMonth)->count();
        $postsLastMonth = Post::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $postsTrend = $postsLastMonth > 0
            ? round((($postsThisMonth - $postsLastMonth) / $postsLastMonth) * 100)
            : ($postsThisMonth > 0 ? 100 : 0);

        $usersThisMonth = User::where('created_at', '>=', $thisMonth)->count();
        $usersLastMonth = User::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $usersTrend = $usersLastMonth > 0
            ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100)
            : ($usersThisMonth > 0 ? 100 : 0);

        $viewsThisMonth = PostView::where('created_at', '>=', $thisMonth)->count();
        $viewsLastMonth = PostView::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $viewsTrend = $viewsLastMonth > 0
            ? round((($viewsThisMonth - $viewsLastMonth) / $viewsLastMonth) * 100)
            : ($viewsThisMonth > 0 ? 100 : 0);

        $stats = [
            'total_posts'      => $totalPosts,
            'published_posts'  => Post::published()->count(),
            'draft_posts'      => Post::draft()->count(),
            'total_users'      => $totalUsers,
            'active_users'     => User::active()->count(),
            'total_views'      => $totalViews,
            'total_comments'   => $totalComments,
            'pending_comments' => $pendingComments,
            'subscribers'      => Subscriber::verified()->count(),
            'posts_trend'      => $postsTrend,
            'users_trend'      => $usersTrend,
            'views_trend'      => $viewsTrend,
        ];

        // --- Views chart: last 30 days ---
        $viewsRaw = PostView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            )
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $viewsChartLabels = [];
        $viewsChartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date               = $now->copy()->subDays($i)->toDateString();
            $viewsChartLabels[] = $now->copy()->subDays($i)->format('M d');
            $viewsChartData[]   = optional($viewsRaw->get($date))->views ?? 0;
        }

        // --- Posts per month: last 6 months ---
        $postsRaw = Post::select(
                DB::raw('YEAR(created_at) as y'),
                DB::raw('MONTH(created_at) as m'),
                DB::raw('COUNT(*) as cnt')
            )
            ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->groupBy('y', 'm')
            ->orderBy('y')->orderBy('m')
            ->get()
            ->keyBy(fn ($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $postsChartLabels = [];
        $postsChartData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $d                  = $now->copy()->subMonths($i);
            $key                = $d->format('Y-m');
            $postsChartLabels[] = $d->format('M');
            $postsChartData[]   = optional($postsRaw->get($key))->cnt ?? 0;
        }

        // --- Recent posts (latest 5) ---
        $recentPosts = Post::with('category')
            ->latest()
            ->limit(5)
            ->get();

        // --- Recent comments ---
        $recentComments = Comment::with(['post', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        // --- New users (last 7 days) ---
        $newUsers = User::where('created_at', '>=', $now->copy()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        // --- Posts per status breakdown ---
        $postsByStatus = Post::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.dashboard.index', compact(
            'stats',
            'viewsChartLabels',
            'viewsChartData',
            'postsChartLabels',
            'postsChartData',
            'recentPosts',
            'recentComments',
            'newUsers',
            'postsByStatus'
        ));
    }
}
