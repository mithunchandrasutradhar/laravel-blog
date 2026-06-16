<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the author's personal dashboard with their own stats.
     */
    public function index(): View
    {
        $authorId = auth()->id();

        // --- Own post stats ---
        $stats = [
            'total_posts'      => Post::where('user_id', $authorId)->count(),
            'published_posts'  => Post::where('user_id', $authorId)->published()->count(),
            'draft_posts'      => Post::where('user_id', $authorId)->draft()->count(),
            'total_views'      => Post::where('user_id', $authorId)->sum('views_count'),
            'total_comments'   => Comment::whereHas('post', fn ($q) => $q->where('user_id', $authorId))
                ->where('status', 'approved')
                ->count(),
            'pending_comments' => Comment::whereHas('post', fn ($q) => $q->where('user_id', $authorId))
                ->where('status', 'pending')
                ->count(),
        ];

        // --- Daily views for author's posts (last 30 days) ---
        $postIds = Post::where('user_id', $authorId)->pluck('id');

        $dailyViews = PostView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as views')
        )
        ->whereIn('post_id', $postIds)
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
            $chartData[]   = $dailyViews->get($date)?->views ?? 0;
        }

        // --- Author's top posts ---
        $topPosts = Post::where('user_id', $authorId)
            ->with('category')
            ->popular()
            ->limit(5)
            ->get();

        // --- Recent comments on author's posts (all statuses) ---
        $recentComments = Comment::with(['post', 'user'])
            ->whereHas('post', fn ($q) => $q->where('user_id', $authorId))
            ->latest()
            ->limit(5)
            ->get();

        // --- Recent own posts ---
        $recentPosts = Post::where('user_id', $authorId)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        return view('author.dashboard', compact(
            'stats',
            'chartLabels',
            'chartData',
            'topPosts',
            'recentComments',
            'recentPosts'
        ));
    }
}
