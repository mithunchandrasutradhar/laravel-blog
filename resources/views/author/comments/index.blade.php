@extends('author.layouts.author')

@section('title', 'My Comments')
@section('page-title', 'My Comments')
@section('page-subtitle', 'Moderate comments on your posts')

@section('content')

{{-- ── Filter Bar ── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('author.comments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control"
                           placeholder="Author name, email, or content..."
                           value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3">
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
                <a href="{{ route('author.comments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Comments Table ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-comments text-warning me-2"></i>Comments on My Posts
        </h6>
        <div class="d-flex gap-2">
            <span class="badge bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-clock me-1"></i>{{ $pendingCount }} Pending
            </span>
            <span class="badge bg-success bg-opacity-10 text-success">
                <i class="fas fa-check me-1"></i>{{ $approvedCount }} Approved
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Post</th>
                        <th>Commenter</th>
                        <th>Email</th>
                        <th>Comment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                    <tr>
                        <td class="ps-3">
                            <span class="small fw-semibold text-dark" title="{{ $comment->post->title ?? '' }}">
                                {{ Str::limit($comment->post->title ?? '—', 30) }}
                            </span>
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-comments fa-2x mb-2 d-block opacity-50"></i>
                            No comments found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($comments->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <div class="small text-muted">
            Showing {{ $comments->firstItem() }}–{{ $comments->lastItem() }} of {{ $comments->total() }}
        </div>
        {{ $comments->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Per-row action forms (outside any table to avoid nesting issues) --}}
@foreach($comments as $comment)
<form id="approve-{{ $comment->id }}" method="POST"
      action="{{ route('author.comments.approve', $comment) }}" style="display:none;">
    @csrf @method('PATCH')
</form>
<form id="reject-{{ $comment->id }}" method="POST"
      action="{{ route('author.comments.reject', $comment) }}" style="display:none;">
    @csrf @method('PATCH')
</form>
<form id="delete-{{ $comment->id }}" method="POST"
      action="{{ route('author.comments.destroy', $comment) }}" style="display:none;">
    @csrf @method('DELETE')
</form>
@endforeach

@endsection
