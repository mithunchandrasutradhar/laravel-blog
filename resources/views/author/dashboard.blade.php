@extends('author.layouts.author')

@section('title', 'My Dashboard')

@section('page-title', 'My Dashboard')
@section('page-subtitle', 'Welcome back, ' . (auth()->user()->name ?? 'Author') . '! Here\'s an overview of your content.')

@section('page-actions')
    <a href="{{ route('author.posts.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>New Post
    </a>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-external-link-alt me-1"></i>View Blog
    </a>
@endsection

@section('content')

{{-- ── Stats Cards ── --}}
<div class="row g-3 mb-4">

    {{-- Total Posts --}}
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-newspaper fa-2x text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Total Posts</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['total_posts'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">all time</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Published --}}
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Published</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['published_posts'] ?? 0 }}</div>
                    <div class="small text-success mt-1">
                        <i class="fas fa-circle me-1" style="font-size:.5rem;"></i>Live
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Draft --}}
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-secondary bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-file-alt fa-2x text-secondary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Drafts</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['draft_posts'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">unpublished</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Views --}}
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-info bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-eye fa-2x text-info"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Total Views</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['total_views'] ?? 0) }}</div>
                    <div class="small text-muted mt-1">across all posts</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approved Comments --}}
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-comments fa-2x text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Comments</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['total_comments'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">approved</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Comments --}}
    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-danger bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-clock fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">Pending</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['pending_comments'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">needs review</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Views Chart ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-chart-line text-primary me-2"></i>My Post Views — Last 30 Days
        </h6>
        <span class="badge bg-primary bg-opacity-10 text-primary">Daily</span>
    </div>
    <div class="card-body pt-0">
        <canvas id="viewsChart" height="80"></canvas>
    </div>
</div>

{{-- ── Bottom Section ── --}}
<div class="row g-4">

    {{-- Left: Recent Comments on My Posts --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-comments text-warning me-2"></i>Recent Comments on My Posts
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Post</th>
                                <th>Commenter</th>
                                <th>Excerpt</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentComments ?? [] as $comment)
                            <tr>
                                <td class="ps-3">
                                    <span class="small fw-semibold text-dark" title="{{ $comment->post->title ?? '' }}">
                                        {{ Str::limit($comment->post->title ?? '—', 25) }}
                                    </span>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $comment->commenter_name }}
                                </td>
                                <td class="small text-muted">
                                    {{ Str::limit($comment->content ?? '', 40) }}
                                </td>
                                <td>
                                    @if(($comment->status ?? '') === 'approved')
                                        <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                    @elseif(($comment->status ?? '') === 'pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $comment->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-comment-slash fa-2x mb-2 d-block"></i>
                                    No comments on your posts yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Top Posts by Views --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-trophy text-warning me-2"></i>Top Posts by Views
                </h6>
                <a href="{{ route('author.posts.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Title</th>
                                <th>Views</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPosts ?? [] as $i => $post)
                            <tr>
                                <td class="ps-3 text-muted small fw-semibold">{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('author.posts.edit', $post->id) }}"
                                       class="text-decoration-none text-dark small fw-semibold">
                                        {{ Str::limit($post->title, 35) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-eye me-1" style="font-size:.65rem;"></i>{{ number_format($post->views_count ?? 0) }}
                                    </span>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $post->published_at ? $post->published_at->format('M d, Y') : ($post->created_at->format('M d, Y')) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-newspaper fa-2x mb-2 d-block"></i>
                                    No published posts yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Quick Actions ── --}}
<div class="mt-4 d-flex flex-wrap gap-2">
    <a href="{{ route('author.posts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Post
    </a>
    <a href="{{ route('author.media.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-upload me-2"></i>Upload Media
    </a>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary">
        <i class="fas fa-globe me-2"></i>View Blog
    </a>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const labels = @json($chartLabels ?? []);
    const data   = @json($chartData   ?? []);

    new Chart(document.getElementById('viewsChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Views',
                data: data,
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
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10, font: { size: 11 } }
                },
                y: {
                    grid: { color: '#f0f0f0' },
                    ticks: { font: { size: 11 } },
                    beginAtZero: true
                }
            }
        }
    });

});
</script>
@endpush
