@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . (auth()->user()->name ?? 'Admin') . '! Here\'s what\'s happening.')

@section('page-actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>New Post
    </a>
@endsection

@section('content')

{{-- ── Stats Cards ── --}}
<div class="row g-3 mb-4">
    {{-- Total Posts --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-newspaper fa-2x text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Total Posts</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['total_posts'] ?? 0 }}</div>
                    <div class="small mt-1">
                        @if(($stats['posts_trend'] ?? 0) >= 0)
                            <span class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $stats['posts_trend'] ?? 0 }}%</span>
                        @else
                            <span class="text-danger"><i class="fas fa-arrow-down me-1"></i>{{ abs($stats['posts_trend'] ?? 0) }}%</span>
                        @endif
                        <span class="text-muted ms-1">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Users --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-users fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Total Users</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['total_users'] ?? 0 }}</div>
                    <div class="small mt-1">
                        @if(($stats['users_trend'] ?? 0) >= 0)
                            <span class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $stats['users_trend'] ?? 0 }}%</span>
                        @else
                            <span class="text-danger"><i class="fas fa-arrow-down me-1"></i>{{ abs($stats['users_trend'] ?? 0) }}%</span>
                        @endif
                        <span class="text-muted ms-1">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Views --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-info bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-eye fa-2x text-info"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Total Views</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['total_views'] ?? 0) }}</div>
                    <div class="small mt-1">
                        @if(($stats['views_trend'] ?? 0) >= 0)
                            <span class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $stats['views_trend'] ?? 0 }}%</span>
                        @else
                            <span class="text-danger"><i class="fas fa-arrow-down me-1"></i>{{ abs($stats['views_trend'] ?? 0) }}%</span>
                        @endif
                        <span class="text-muted ms-1">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Comments --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-comments fa-2x text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Total Comments</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['total_comments'] ?? 0 }}</div>
                    <div class="small mt-1">
                        <span class="text-warning">
                            <i class="fas fa-clock me-1"></i>{{ $stats['pending_comments'] ?? 0 }} pending
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick Actions ── --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Post
    </a>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-secondary">
        <i class="fas fa-folder-plus me-2"></i>New Category
    </a>
    <a href="{{ route('admin.tags.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-tag me-2"></i>New Tag
    </a>
    <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-upload me-2"></i>Upload Media
    </a>
</div>

{{-- ── Charts Row ── --}}
<div class="row g-3 mb-4">
    {{-- Line chart: Views last 30 days --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Views — Last 30 Days</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary">Daily</span>
            </div>
            <div class="card-body pt-0">
                <canvas id="viewsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Bar chart: Posts per month --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar text-success me-2"></i>Posts per Month</h6>
                <span class="badge bg-success bg-opacity-10 text-success">Monthly</span>
            </div>
            <div class="card-body pt-0">
                <canvas id="postsChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Tables Row ── --}}
<div class="row g-3">
    {{-- Recent Posts --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-newspaper text-primary me-2"></i>Recent Posts</h6>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Title</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPosts ?? [] as $post)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.posts.edit', $post->id) }}"
                                       class="text-decoration-none fw-semibold text-dark small">
                                        {{ Str::limit($post->title, 30) }}
                                    </a>
                                </td>
                                <td>
                                    @if($post->status === 'published')
                                        <span class="badge bg-success bg-opacity-10 text-success">Published</span>
                                    @elseif($post->status === 'draft')
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Draft</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Scheduled</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ number_format($post->views_count ?? 0) }}</td>
                                <td class="small text-muted">{{ $post->created_at->format('M d') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No posts yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Comments --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-comments text-warning me-2"></i>Recent Comments</h6>
                <a href="{{ route('admin.comments.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Author</th>
                                <th>Comment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentComments ?? [] as $comment)
                            <tr>
                                <td class="ps-3 small fw-semibold">{{ Str::limit($comment->author_name, 12) }}</td>
                                <td class="small text-muted">{{ Str::limit($comment->content, 25) }}</td>
                                <td>
                                    @if($comment->status === 'approved')
                                        <span class="badge bg-success bg-opacity-10 text-success">OK</span>
                                    @elseif($comment->status === 'pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No comments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- New Users --}}
    <div class="col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-plus text-success me-2"></i>New Users</h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($newUsers ?? [] as $user)
                    <li class="list-group-item px-3 py-2 d-flex align-items-center gap-2">
                        <div class="avatar-sm bg-primary d-flex align-items-center justify-content-center text-white fw-bold rounded-circle flex-shrink-0"
                             style="width:32px;height:32px;font-size:.7rem;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-semibold text-truncate">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $user->created_at->diffForHumans() }}</div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">No new users.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Views Line Chart ──
    const viewsCtx = document.getElementById('viewsChart').getContext('2d');
    @php
        $viewsDataArr = $viewsChartData ?? array_fill(0, 30, 0);
        $viewsLabelsArr = $viewsChartLabels ?? array_map(function($i){ return date('M d', strtotime("-$i days")); }, range(29, 0));
        $postsDataArr = $postsChartData ?? array_fill(0, 6, 0);
        $postsLabelsArr = $postsChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun'];
    @endphp
    const viewsData = {!! json_encode($viewsDataArr) !!};
    const viewsLabels = {!! json_encode($viewsLabelsArr) !!};

    new Chart(viewsCtx, {
        type: 'line',
        data: {
            labels: viewsLabels,
            datasets: [{
                label: 'Views',
                data: viewsData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: { size: 11 } } },
                y: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // ── Posts Bar Chart ──
    const postsCtx = document.getElementById('postsChart').getContext('2d');
    const postsData = {!! json_encode($postsDataArr) !!};
    const postsLabels = {!! json_encode($postsLabelsArr) !!};

    new Chart(postsCtx, {
        type: 'bar',
        data: {
            labels: postsLabels,
            datasets: [{
                label: 'Posts',
                data: postsData,
                backgroundColor: 'rgba(25,135,84,.7)',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } }
            }
        }
    });

});
</script>
@endpush
