@extends('admin.layouts.admin')

@section('title', 'Activity Log')

@section('breadcrumb')
    <li class="breadcrumb-item active">Activity Log</li>
@endsection

@section('page-title', 'Activity Log')
@section('page-subtitle', 'A record of every event across the platform')

@section('page-actions')
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmClear()">
        <i class="fas fa-trash me-1"></i>Clear Log
    </button>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-primary mb-0">{{ number_format($totalLogs) }}</div>
            <div class="small text-muted">Total Events</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-success mb-0">{{ $modules->count() }}</div>
            <div class="small text-muted">Modules Tracked</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.activity-log.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="User, event or description…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Module</label>
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All Modules</option>
                        @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $mod)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">From date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">To date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Log Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold">
            Events
            @if(request()->hasAny(['search','module','date_from','date_to']))
                <span class="badge bg-primary ms-1">Filtered</span>
            @endif
        </span>
        <span class="small text-muted">{{ number_format($logs->total()) }} record(s)</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
            <thead class="table-light">
                <tr>
                    <th style="width:160px;">Time</th>
                    <th style="width:140px;">User</th>
                    <th style="width:180px;">Event</th>
                    <th>Description</th>
                    <th style="width:120px;">Subject</th>
                    <th style="width:110px;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-muted text-nowrap">
                        {{ $log->created_at->format('M j, Y') }}<br>
                        <span style="font-size:.78rem;">{{ $log->created_at->format('g:i:s A') }}</span>
                    </td>
                    <td>
                        @if($log->causer_name)
                            <span class="fw-medium">{{ $log->causer_name }}</span>
                            @if($log->causer_id)
                            <br><span class="text-muted" style="font-size:.75rem;">#{{ $log->causer_id }}</span>
                            @endif
                        @else
                            <span class="text-muted fst-italic">System</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $color = $log->event_color;
                            $icon  = $log->event_icon;
                        @endphp
                        <span class="badge rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1"
                              style="background:{{ $color }}18;color:{{ $color }};font-size:.72rem;">
                            <i class="fas {{ $icon }}" style="font-size:.65rem;"></i>
                            {{ str_replace('_', ' ', $log->event) }}
                        </span>
                    </td>
                    <td>{{ $log->description }}</td>
                    <td class="text-muted">
                        @if($log->subject_type)
                            <span class="badge bg-light text-secondary border" style="font-size:.7rem;">
                                {{ $log->subject_type }}
                                @if($log->subject_id) #{{ $log->subject_id }} @endif
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted font-monospace" style="font-size:.78rem;">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-2x mb-3 d-block opacity-50"></i>
                        No log entries found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <div class="small text-muted">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
        </div>
        {{ $logs->links() }}
    </div>
    @endif
</div>

{{-- Hidden clear form --}}
<form id="clearForm" method="POST" action="{{ route('admin.activity-log.clear') }}" class="d-none">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function confirmClear() {
    Swal.fire({
        title: 'Clear the entire activity log?',
        text: 'This will permanently delete all log entries. This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, clear it',
        cancelButtonText: 'Cancel',
    }).then(function (result) {
        if (result.isConfirmed) {
            document.getElementById('clearForm').submit();
        }
    });
}
</script>
@endpush
