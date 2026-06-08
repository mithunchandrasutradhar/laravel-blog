@extends('admin.layouts.admin')

@section('title', 'Posts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Posts</li>
@endsection

@section('page-title', 'All Posts')
@section('page-subtitle', 'Manage all blog posts')

@section('page-actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add New Post
    </a>
@endsection

@section('content')

{{-- ── Filter Bar ── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="row g-2 align-items-end">
            {{-- Search --}}
            <div class="col-md-4">
                <label class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search posts..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Status --}}
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>

            {{-- Category --}}
            <div class="col-md-3">
                <label class="form-label small mb-1">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Bulk Actions Form ── --}}
<form id="bulkForm" method="POST" action="{{ route('admin.posts.bulk-delete') }}">
    @csrf
    @method('DELETE')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label small" for="selectAll">Select All</label>
                </div>
                <div id="bulkActions" class="d-none">
                    <button type="button" class="btn btn-danger btn-sm" data-confirm-delete
                            data-form="bulkForm"
                            data-confirm-title="Delete selected posts?"
                            data-confirm-text="All selected posts will be permanently deleted.">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>
                </div>
            </div>
            <span class="text-muted small">{{ $posts->total() ?? 0 }} posts found</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="ps-3"></th>
                            <th width="50">ID</th>
                            <th width="70">Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts ?? [] as $post)
                        <tr>
                            <td class="ps-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input row-check" type="checkbox"
                                           name="ids[]" value="{{ $post->id }}">
                                </div>
                            </td>
                            <td class="text-muted small">{{ $post->id }}</td>
                            <td>
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}"
                                         alt="" class="rounded" style="width:50px;height:35px;object-fit:cover;">
                                @else
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                         style="width:50px;height:35px;">
                                        <i class="fas fa-image text-muted small"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.posts.edit', $post->id) }}"
                                   class="fw-semibold text-dark text-decoration-none">
                                    {{ Str::limit($post->title, 45) }}
                                </a>
                                @if($post->is_featured ?? false)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Featured</span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">{{ $post->category->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $post->author->name ?? '—' }}</span>
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
                            <td class="small text-muted text-nowrap">
                                {{ $post->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.posts.edit', $post->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('posts.show', $post->slug) }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-confirm-delete title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-newspaper fa-2x mb-2 d-block"></i>
                                No posts found. <a href="{{ route('admin.posts.create') }}">Create one?</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($posts) && $posts->hasPages())
        <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
            <div class="small text-muted">
                Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }}
            </div>
            {{ $posts->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Select all checkbox
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
</script>
@endpush
