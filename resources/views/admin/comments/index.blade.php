@extends('admin.layouts.admin')

@section('title', 'Comments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Comments</li>
@endsection

@section('page-title', 'Comments')
@section('page-subtitle', 'Moderate and manage reader comments')

@section('content')

{{-- ── Filter Bar ── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.comments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Author name, email, or content..."
                           value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('admin.comments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Bulk Actions Form ── --}}
<form id="bulkCommentsForm" method="POST" action="{{ route('admin.comments.bulk-action') }}">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput" value="">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label small" for="selectAll">Select All</label>
                </div>
                <div id="bulkActions" class="d-none d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="submitBulk('approve')">
                        <i class="fas fa-check me-1"></i>Approve
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="submitBulk('reject')">
                        <i class="fas fa-ban me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="submitBulk('delete')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2 text-muted small">
                @php
                    $pendingCount  = $pendingCount  ?? 0;
                    $approvedCount = $approvedCount ?? 0;
                @endphp
                <span class="badge bg-warning bg-opacity-10 text-warning">{{ $pendingCount }} Pending</span>
                <span class="badge bg-success bg-opacity-10 text-success">{{ $approvedCount }} Approved</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="ps-3"></th>
                            <th width="50">ID</th>
                            <th>Post</th>
                            <th>Author</th>
                            <th>Email</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th width="130">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comments ?? [] as $comment)
                        <tr>
                            <td class="ps-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input row-check" type="checkbox"
                                           name="ids[]" value="{{ $comment->id }}">
                                </div>
                            </td>
                            <td class="text-muted small">{{ $comment->id }}</td>
                            <td>
                                <a href="{{ route('admin.posts.edit', $comment->post_id) }}"
                                   class="text-decoration-none text-dark small fw-semibold"
                                   title="{{ $comment->post->title ?? '' }}">
                                    {{ Str::limit($comment->post->title ?? 'N/A', 30) }}
                                </a>
                            </td>
                            <td class="small fw-semibold">{{ $comment->commenter_name }}</td>
                            <td class="small text-muted">{{ $comment->commenter_email }}</td>
                            <td>
                                <p class="mb-0 small text-muted" title="{{ $comment->body }}">
                                    {{ Str::limit($comment->body, 60) }}
                                </p>
                            </td>
                            <td>
                                @if($comment->status === 'approved')
                                    <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                @elseif($comment->status === 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="small text-muted text-nowrap">
                                {{ $comment->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                {{-- Buttons reference forms defined OUTSIDE the bulk form via form= attribute --}}
                                <div class="d-flex gap-1 flex-wrap">
                                    @if($comment->status !== 'approved')
                                    <button type="submit" form="approve-{{ $comment->id }}"
                                            class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif

                                    @if($comment->status !== 'rejected')
                                    <button type="submit" form="reject-{{ $comment->id }}"
                                            class="btn btn-sm btn-warning" title="Reject">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endif

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-confirm-delete
                                            data-form="delete-{{ $comment->id }}"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                                No comments found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($comments) && $comments->hasPages())
        <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
            <div class="small text-muted">
                Showing {{ $comments->firstItem() }}–{{ $comments->lastItem() }} of {{ $comments->total() }}
            </div>
            {{ $comments->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</form>

{{-- Per-row action forms outside the bulk form to avoid nested-form conflicts --}}
@if(isset($comments))
@foreach($comments as $comment)
<form id="approve-{{ $comment->id }}" method="POST" action="{{ route('admin.comments.approve', $comment) }}" style="display:none;">
    @csrf @method('PATCH')
</form>
<form id="reject-{{ $comment->id }}" method="POST" action="{{ route('admin.comments.reject', $comment) }}" style="display:none;">
    @csrf @method('PATCH')
</form>
<form id="delete-{{ $comment->id }}" method="POST" action="{{ route('admin.comments.destroy', $comment) }}" style="display:none;">
    @csrf @method('DELETE')
</form>
@endforeach
@endif

@endsection

@push('scripts')
<script>
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        toggleBulkActions();
    });

    document.querySelectorAll('.row-check').forEach(cb => {
        cb.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const checked = document.querySelectorAll('.row-check:checked').length;
        document.getElementById('bulkActions').classList.toggle('d-none', checked === 0);
    }

    function submitBulk(action) {
        const checked = document.querySelectorAll('.row-check:checked').length;
        if (!checked) return;

        const messages = {
            approve: { title: 'Approve selected?', text: `Approve ${checked} comment(s).` },
            reject:  { title: 'Reject selected?',  text: `Reject ${checked} comment(s).` },
            delete:  { title: 'Delete selected?',  text: `Permanently delete ${checked} comment(s).` },
        };

        Swal.fire({
            title: messages[action].title,
            text: messages[action].text,
            icon: action === 'delete' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'delete' ? '#dc3545' : '#0d6efd',
            confirmButtonText: 'Yes, proceed',
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('bulkActionInput').value = action;
                document.getElementById('bulkCommentsForm').submit();
            }
        });
    }
</script>
@endpush
