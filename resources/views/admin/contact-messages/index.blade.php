@extends('admin.layouts.admin')

@section('title', 'Contact Messages')

@section('breadcrumb')
    <li class="breadcrumb-item active">Contact Messages</li>
@endsection

@section('page-title', 'Contact Messages')
@section('page-subtitle', 'Messages submitted via the contact form')

@section('content')

{{-- Stats row --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-primary mb-0">{{ $messages->total() }}</div>
            <div class="small text-muted">Total Messages</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-danger mb-0">{{ $totalUnread }}</div>
            <div class="small text-muted">Unread</div>
        </div>
    </div>
</div>

{{-- Alert messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Search & Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Name, email or subject…"
                               value="{{ request('search') }}">
                    </div>
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
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read"   {{ request('status') === 'read'   ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
@if(auth()->user()->hasPermissionTo('contact_messages.delete'))
<form id="bulkForm" method="POST" action="{{ route('admin.contact-messages.bulk-destroy') }}">
    @csrf
    @method('DELETE')
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-3">
            @if(auth()->user()->hasPermissionTo('contact_messages.delete'))
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="selectAll" title="Select all">
            </div>
            @endif
            <span class="fw-semibold">Messages
                @if(request()->hasAny(['search','date_from','date_to','status']))
                    <span class="badge bg-primary ms-1">Filtered</span>
                @endif
            </span>
        </div>

        @if(auth()->user()->hasPermissionTo('contact_messages.delete'))
        <button type="submit" form="bulkForm" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn"
                onclick="return confirm('Delete selected messages?')">
            <i class="fas fa-trash me-1"></i>Delete Selected
        </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @if(auth()->user()->hasPermissionTo('contact_messages.delete'))
                    <th style="width:40px;"></th>
                    @endif
                    <th>From</th>
                    <th>Subject</th>
                    <th style="width:160px;">Date</th>
                    <th style="width:80px;">Status</th>
                    <th style="width:120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr class="{{ $msg->is_read ? '' : 'table-active' }}">
                    @if(auth()->user()->hasPermissionTo('contact_messages.delete'))
                    <td>
                        <div class="form-check mb-0">
                            <input class="form-check-input row-check" type="checkbox" name="ids[]" value="{{ $msg->id }}">
                        </div>
                    </td>
                    @endif
                    <td>
                        <div class="fw-{{ $msg->is_read ? 'normal' : 'semibold' }}">{{ $msg->name }}</div>
                        <div class="small text-muted">{{ $msg->email }}</div>
                    </td>
                    <td>
                        <span class="{{ $msg->is_read ? 'text-muted' : 'fw-semibold' }}">
                            {{ Str::limit($msg->subject, 60) }}
                        </span>
                    </td>
                    <td class="small text-muted">{{ $msg->created_at->format('M j, Y g:i A') }}</td>
                    <td>
                        @if($msg->is_read)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Read</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger">Unread</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.contact-messages.show', $msg) }}"
                               class="btn btn-sm btn-outline-primary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(auth()->user()->hasPermissionTo('contact_messages.delete'))
                            <form method="POST" action="{{ route('admin.contact-messages.destroy', $msg) }}"
                                  onsubmit="return confirm('Delete this message?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-3 d-block opacity-50"></i>
                        No messages found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($messages->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <div class="small text-muted">
            Showing {{ $messages->firstItem() }}–{{ $messages->lastItem() }} of {{ $messages->total() }}
        </div>
        {{ $messages->links() }}
    </div>
    @endif
</div>

@if(auth()->user()->hasPermissionTo('contact_messages.delete'))
</form>
@endif

@endsection

@push('scripts')
<script>
    // Select all / deselect all
    const selectAll  = document.getElementById('selectAll');
    const bulkBtn    = document.getElementById('bulkDeleteBtn');
    const rowChecks  = () => document.querySelectorAll('.row-check');

    function syncBulkBtn() {
        const any = [...rowChecks()].some(c => c.checked);
        if (bulkBtn) bulkBtn.classList.toggle('d-none', !any);
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            rowChecks().forEach(c => c.checked = selectAll.checked);
            syncBulkBtn();
        });
    }

    document.addEventListener('change', e => {
        if (e.target.classList.contains('row-check')) {
            syncBulkBtn();
            if (selectAll) {
                selectAll.checked = [...rowChecks()].every(c => c.checked);
            }
        }
    });
</script>
@endpush
