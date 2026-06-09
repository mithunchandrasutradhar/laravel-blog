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
    /**
     * Display the admin dashboard with key metrics.
     */
    public function index(): View
    {
        // --- Core metrics ---
        $metrics = [
            'total_posts'       => Post::count(),
            'published_posts'   => Post::published()->count(),
            'draft_posts'       => Post::draft()->count(),
            'total_users'       => User::count(),
            'active_users'      => User::active()->count(),
            'total_views'       => Post::sum('views_count'),
            'total_comments'    => Comment::count(),
            'pending_comments'  => Comment::pending()->count(),
            'subscribers'       => Subscriber::verified()->count(),
        ];

        // --- Views chart: last 30 days ---
        $viewsChart = PostView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as views')
        )
        ->where('created_at', '>=', now()->subDays(29))
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $chartLabels = [];
        $chartData   = [];

        for ($i = 29; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartData[]   = $viewsChart->get($date)?->views ?? 0;
        }

        // --- Popular posts (last 30 days) ---
        $popularPosts = Post::select('posts.*', DB::raw('COUNT(post_views.id) as recent_views'))
            ->join('post_views', 'posts.id', '=', 'post_views.post_id')
            ->where('post_views.created_at', '>=', now()->subDays(30))
            ->groupBy('posts.id')
            ->orderByDesc('recent_views')
            ->limit(10)
            ->get();

        // --- Recent comments awaiting moderation ---
        $recentComments = Comment::with(['post', 'user'])
            ->pending()
            ->latest()
            ->limit(10)
            ->get();

        // --- New users (last 7 days) ---
        $newUsers = User::where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        // --- Posts per status breakdown ---
        $postsByStatus = Post::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.dashboard.index', compact(
            'metrics',
            'chartLabels',
            'chartData',
            'popularPosts',
            'recentComments',
            'newUsers',
            'postsByStatus'
        ));
    }
}
