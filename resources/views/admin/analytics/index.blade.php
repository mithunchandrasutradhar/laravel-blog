@extends('admin.layouts.admin')

@section('title', 'Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Analytics</li>
@endsection

@section('page-title', 'Analytics')
@section('page-subtitle', 'Track site performance and traffic')

@section('content')

{{-- ── Date Range Selector ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">From Date</label>
                <input type="date" name="from" class="form-control form-control-sm"
                       value="{{ $from->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">To Date</label>
                <input type="date" name="to" class="form-control form-control-sm"
                       value="{{ $to->format('Y-m-d') }}">
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Apply
                </button>
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        Presets
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item small" href="?from={{ now()->subDays(6)->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}">Last 7 days</a></li>
                        <li><a class="dropdown-item small" href="?from={{ now()->subDays(29)->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}">Last 30 days</a></li>
                        <li><a class="dropdown-item small" href="?from={{ now()->subDays(89)->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}">Last 90 days</a></li>
                        <li><a class="dropdown-item small" href="?from={{ now()->startOfYear()->format('Y-m-d') }}&to={{ now()->format('Y-m-d') }}">This year</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Stats Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-eye fa-2x text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Views</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['total_views']) }}</div>
                    <div class="small mt-1">
                        @if($stats['views_change'] >= 0)
                            <span class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $stats['views_change'] }}%</span>
                        @else
                            <span class="text-danger"><i class="fas fa-arrow-down me-1"></i>{{ abs($stats['views_change']) }}%</span>
                        @endif
                        <span class="text-muted ms-1">vs previous period</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-user-check fa-2x text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Unique Views</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['unique_views']) }}</div>
                    <div class="small mt-1">
                        @if($stats['unique_change'] >= 0)
                            <span class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $stats['unique_change'] }}%</span>
                        @else
                            <span class="text-danger"><i class="fas fa-arrow-down me-1"></i>{{ abs($stats['unique_change']) }}%</span>
                        @endif
                        <span class="text-muted ms-1">vs previous period</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                    <i class="fas fa-chart-bar fa-2x text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Avg. Views / Post</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['avg_views_per_post'], 1) }}</div>
                    <div class="small mt-1 text-muted">across all published posts</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Daily Views Chart ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-chart-area text-primary me-2"></i>Daily Views</h6>
        <button class="btn btn-sm btn-outline-secondary" id="chartTypeBtn" onclick="toggleChartType()">
            <i class="fas fa-chart-bar"></i> Bar
        </button>
    </div>
    <div class="card-body pt-0">
        <canvas id="dailyViewsChart" height="80"></canvas>
    </div>
</div>

{{-- ── Bottom row ── --}}
<div class="row g-4">

    {{-- Top Posts Table --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-trophy text-warning me-2"></i>Top 10 Posts by Views</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="ps-3">#</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Views</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $maxViews = $topPosts->max('period_views') ?: 1; @endphp
                            @forelse($topPosts as $index => $post)
                            <tr>
                                <td class="ps-3">
                                    @if($index === 0)
                                        <i class="fas fa-trophy text-warning"></i>
                                    @elseif($index === 1)
                                        <i class="fas fa-trophy text-secondary"></i>
                                    @elseif($index === 2)
                                        <i class="fas fa-trophy text-danger" style="opacity:.6;"></i>
                                    @else
                                        <span class="text-muted small">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $post->id) }}"
                                       class="fw-semibold text-dark text-decoration-none small">
                                        {{ Str::limit($post->title, 55) }}
                                    </a>
                                </td>
                                <td class="small text-muted">{{ $post->category->name ?? '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                                            <div class="progress-bar bg-primary"
                                                 style="width:{{ $maxViews > 0 ? ($post->period_views / $maxViews) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="small fw-bold" style="min-width:45px;">
                                            {{ number_format($post->period_views) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    {{ $post->published_at?->format('M d, Y') ?? '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No views recorded in the selected period.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-pie-chart text-info me-2"></i>Traffic by Category</h6>
            </div>
            <div class="card-body pt-0">
                @if($categoryStats->isEmpty())
                    <p class="text-muted small text-center py-3 mb-0">No category data for this period.</p>
                @else
                    <canvas id="categoryChart" height="200"></canvas>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-secondary me-2"></i>Period Summary</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                        <span class="text-muted">Date range</span>
                        <strong>{{ $from->format('M d') }} — {{ $to->format('M d, Y') }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                        <span class="text-muted">Published posts</span>
                        <strong>{{ $stats['published_in_period'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                        <span class="text-muted">New comments</span>
                        <strong>{{ $stats['comments_in_period'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                        <span class="text-muted">New subscribers</span>
                        <strong>{{ $stats['subscribers_in_period'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                        <span class="text-muted">New users</span>
                        <strong>{{ $stats['new_users_in_period'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                        <span class="text-muted">Device breakdown</span>
                        <span>
                            @foreach($deviceTypes as $d)
                                <span class="badge bg-light text-dark border me-1">{{ $d->device }}: {{ number_format($d->count) }}</span>
                            @endforeach
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Daily Views Chart ──────────────────────────────────────────────────────
    const labels     = @json($chartLabels);
    const viewsData  = @json($chartData);
    const uniqueData = @json($uniqueChartData);

    const ctx = document.getElementById('dailyViewsChart').getContext('2d');
    let chartType = 'line';

    function buildChart(type) {
        if (window.dailyChart) window.dailyChart.destroy();
        window.dailyChart = new Chart(ctx, {
            type,
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Views',
                        data: viewsData,
                        borderColor: '#0d6efd',
                        backgroundColor: type === 'line' ? 'rgba(13,110,253,.08)' : 'rgba(13,110,253,.6)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4,
                        pointRadius: type === 'line' ? 2 : 0,
                    },
                    {
                        label: 'Unique Views',
                        data: uniqueData,
                        borderColor: '#198754',
                        backgroundColor: type === 'line' ? 'rgba(25,135,84,.06)' : 'rgba(25,135,84,.6)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4,
                        pointRadius: type === 'line' ? 2 : 0,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 12 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 12, font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 }, precision: 0 } }
                }
            }
        });
    }

    buildChart(chartType);

    window.toggleChartType = function () {
        chartType = chartType === 'line' ? 'bar' : 'line';
        buildChart(chartType);
        const btn = document.getElementById('chartTypeBtn');
        btn.innerHTML = chartType === 'line'
            ? '<i class="fas fa-chart-bar"></i> Bar'
            : '<i class="fas fa-chart-line"></i> Line';
    };

    // ── Category Doughnut Chart ────────────────────────────────────────────────
    @if($categoryStats->isNotEmpty())
    const catCtx    = document.getElementById('categoryChart').getContext('2d');
    const catLabels = @json($categoryStats->pluck('name'));
    const catData   = @json($categoryStats->pluck('total_views'));
    const catColors = [
        '#0d6efd','#198754','#dc3545','#ffc107','#0dcaf0',
        '#6f42c1','#fd7e14','#20c997','#6c757d','#d63384'
    ];

    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: catColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } }
            }
        }
    });
    @endif

});
</script>
@endpush
