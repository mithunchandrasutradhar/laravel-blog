@extends('admin.layouts.admin')

@section('title', 'Subscribers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Subscribers</li>
@endsection

@section('page-title', 'Subscribers')
@section('page-subtitle', 'Manage newsletter subscribers')

@section('page-actions')
    <a href="{{ route('admin.subscribers.export') }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-csv me-1"></i>Export CSV
    </a>
@endsection

@section('content')

{{-- Stats summary --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h4 fw-bold mb-0 text-primary">{{ $totalCount ?? 0 }}</div>
            <div class="text-muted small">Total</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h4 fw-bold mb-0 text-success">{{ $verifiedCount ?? 0 }}</div>
            <div class="text-muted small">Verified</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h4 fw-bold mb-0 text-warning">{{ $unverifiedCount ?? 0 }}</div>
            <div class="text-muted small">Unverified</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
        <h6 class="fw-bold mb-0">All Subscribers</h6>
        <form method="GET" class="d-flex gap-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" style="width:200px;"
                       placeholder="Search by email..." value="{{ request('search') }}">
            </div>
            <select name="verified" class="form-select form-select-sm" style="width:130px;">
                <option value="">All</option>
                <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verified</option>
                <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Unverified</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i>
            </a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60" class="ps-3">ID</th>
                        <th>Email</th>
                        <th>Verified</th>
                        <th>Subscribed</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers ?? [] as $subscriber)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $subscriber->id }}</td>
                        <td class="small">{{ $subscriber->email }}</td>
                        <td>
                            @if($subscriber->verified_at)
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-check me-1"></i>Verified
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Unverified</span>
                            @endif
                        </td>
                        <td class="small text-muted text-nowrap">
                            {{ $subscriber->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(!$subscriber->verified_at)
                                <form method="POST" action="{{ route('admin.subscribers.verify', $subscriber->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as verified">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.subscribers.destroy', $subscriber->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-confirm-delete
                                            data-confirm-title="Remove subscriber?"
                                            data-confirm-text="They will no longer receive newsletters."
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-envelope fa-2x mb-2 d-block"></i>
                            No subscribers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($subscribers) && $subscribers->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <div class="small text-muted">
            Showing {{ $subscribers->firstItem() }}–{{ $subscribers->lastItem() }} of {{ $subscribers->total() }}
        </div>
        {{ $subscribers->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
