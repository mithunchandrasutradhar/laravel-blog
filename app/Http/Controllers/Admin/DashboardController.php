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
        $user    = auth()->user();
        $isAdmin = $user->hasPermissionTo('panel.admin') || $user->isAdmin();
        $uid     = $user->id;
        $now     = now();

        $thisMonth    = $now->copy()->startOfMonth();
        $lastMonth    = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Base post scope — admin sees all, others see own
        $postScope = $isAdmin
            ? Post::query()
            : Post::where('user_id', $uid);

        // --- Core counts ---
        $totalPosts      = (clone $postScope)->count();
        $totalViews      = (clone $postScope)->sum('views_count');
        $totalComments   = $isAdmin
            ? Comment::count()
            : Comment::whereIn('post_id', (clone $postScope)->pluck('id'))->count();
        $pendingComments = $isAdmin
            ? Comment::pending()->count()
            : Comment::pending()->whereIn('post_id', (clone $postScope)->pluck('id'))->count();
        $totalUsers      = $isAdmin ? User::count() : null;

        // --- Trends vs last month ---
        $postsThisMonth = (clone $postScope)->where('created_at', '>=', $thisMonth)->count();
        $postsLastMonth = (clone $postScope)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $postsTrend = $postsLastMonth > 0
            ? round((($postsThisMonth - $postsLastMonth) / $postsLastMonth) * 100)
            : ($postsThisMonth > 0 ? 100 : 0);

        $usersThisMonth = $isAdmin ? User::where('created_at', '>=', $thisMonth)->count() : 0;
        $usersLastMonth = $isAdmin ? User::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count() : 0;
        $usersTrend = $usersLastMonth > 0
            ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100)
            : ($usersThisMonth > 0 ? 100 : 0);

        $viewsThisMonth = $isAdmin
            ? PostView::where('created_at', '>=', $thisMonth)->count()
            : PostView::whereIn('post_id', (clone $postScope)->pluck('id'))->where('created_at', '>=', $thisMonth)->count();
        $viewsLastMonth = $isAdmin
            ? PostView::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count()
            : PostView::whereIn('post_id', (clone $postScope)->pluck('id'))->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $viewsTrend = $viewsLastMonth > 0
            ? round((($viewsThisMonth - $viewsLastMonth) / $viewsLastMonth) * 100)
            : ($viewsThisMonth > 0 ? 100 : 0);

        $stats = [
            'total_posts'      => $totalPosts,
            'published_posts'  => (clone $postScope)->where('status', 'published')->count(),
            'draft_posts'      => (clone $postScope)->where('status', 'draft')->count(),
            'total_users'      => $totalUsers,
            'active_users'     => $isAdmin ? User::active()->count() : null,
            'total_views'      => $totalViews,
            'total_comments'   => $totalComments,
            'pending_comments' => $pendingComments,
            'subscribers'      => $isAdmin ? Subscriber::verified()->count() : null,
            'posts_trend'      => $postsTrend,
            'users_trend'      => $usersTrend,
            'views_trend'      => $viewsTrend,
            'is_admin'         => $isAdmin,
        ];

        // --- Views chart: last 30 days (scoped) ---
        $ownPostIds     = $isAdmin ? null : (clone $postScope)->pluck('id');
        $viewsBaseQuery = PostView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            )
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay());

        if (! $isAdmin) {
            $viewsBaseQuery->whereIn('post_id', $ownPostIds);
        }

        $viewsRaw = $viewsBaseQuery->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $viewsChartLabels = [];
        $viewsChartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date               = $now->copy()->subDays($i)->toDateString();
            $viewsChartLabels[] = $now->copy()->subDays($i)->format('M d');
            $viewsChartData[]   = optional($viewsRaw->get($date))->views ?? 0;
        }

        // --- Posts per month: last 6 months (scoped) ---
        $postsRaw = (clone $postScope)
            ->select(
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

        // --- Recent posts (scoped) ---
        $recentPosts = (clone $postScope)->with('category')->latest()->limit(5)->get();

        // --- Recent comments (scoped to own posts for non-admin) ---
        $recentCommentsQuery = Comment::with(['post', 'user'])->latest()->limit(10);
        if (! $isAdmin) {
            $recentCommentsQuery->whereIn('post_id', $ownPostIds);
        }
        $recentComments = $recentCommentsQuery->get();

        // --- New users: admin only ---
        $newUsers = $isAdmin
            ? User::where('created_at', '>=', $now->copy()->subDays(7))->latest()->limit(5)->get()
            : collect();

        // --- Posts by status (scoped) ---
        $postsByStatus = (clone $postScope)
            ->select('status', DB::raw('COUNT(*) as count'))
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
