@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('page-title', 'Categories')
@section('page-subtitle', 'Manage post categories')

@section('page-actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add New Category
    </a>
@endsection

@section('content')

{{-- Single-row delete form (standalone, no nesting) --}}
<form method="POST" id="single-delete-form" action="">
    @csrf
    @method('DELETE')
</form>

{{-- Bulk delete form --}}
<form method="POST" action="{{ route('admin.categories.bulk-delete') }}" id="bulk-form">
    @csrf

{{-- Search --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" id="search-q" class="form-control"
                           placeholder="Category name..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm" onclick="applySearch()">
                    <i class="fas fa-filter me-1"></i>Search
                </button>
                @if(request('q'))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">All Categories</h6>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">{{ $categories->total() ?? count($categories ?? []) }} categories</span>
            <button type="button" id="bulk-delete-btn"
                    class="btn btn-sm btn-outline-danger d-none"
                    onclick="confirmBulkDelete()">
                <i class="fas fa-trash me-1"></i>Delete Selected (<span id="selected-count">0</span>)
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="ps-3">
                            <input type="checkbox" class="form-check-input" id="select-all" title="Select all">
                        </th>
                        <th width="60">ID</th>
                        <th width="80">Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Parent</th>
                        <th>Posts</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories ?? [] as $category)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" name="ids[]" value="{{ $category->id }}"
                                   class="form-check-input row-cb">
                        </td>
                        <td class="text-muted small">{{ $category->id }}</td>
                        <td>
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}"
                                     alt="{{ $category->name }}"
                                     class="rounded" style="width:45px;height:35px;object-fit:cover;">
                            @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                     style="width:45px;height:35px;">
                                    <i class="fas fa-folder text-muted small"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $category->name }}</span>
                        </td>
                        <td>
                            <code class="small bg-light px-2 py-1 rounded">{{ $category->slug }}</code>
                        </td>
                        <td class="text-muted small">
                            {{ $category->parent->name ?? '—' }}
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $category->posts_count ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.categories.edit', $category->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        title="Delete"
                                        data-delete-url="{{ route('admin.categories.destroy', $category->id) }}"
                                        onclick="singleDelete(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            No categories found.
                            @unless(request('q'))
                                <a href="{{ route('admin.categories.create') }}">Create one?</a>
                            @endunless
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($categories) && method_exists($categories, 'hasPages') && $categories->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <div class="small text-muted">
            Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
        </div>
        {{ $categories->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

</form>{{-- end bulk-form --}}

@endsection

@push('scripts')
<script>
(function () {

    // ── Search ────────────────────────────────────────────────────────────────
    window.applySearch = function () {
        var q = document.getElementById('search-q').value;
        var url = new URL(window.location.href);
        if (q.trim()) {
            url.searchParams.set('q', q.trim());
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.delete('page');
        window.location.href = url.toString();
    };

    document.getElementById('search-q').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); applySearch(); }
    });

    // ── Select all / bulk bar ─────────────────────────────────────────────────
    var selectAll = document.getElementById('select-all');
    var bulkBtn   = document.getElementById('bulk-delete-btn');
    var countSpan = document.getElementById('selected-count');

    function updateBulkBar() {
        var checked = document.querySelectorAll('.row-cb:checked').length;
        var total   = document.querySelectorAll('.row-cb').length;
        countSpan.textContent = checked;
        bulkBtn.classList.toggle('d-none', checked === 0);
        selectAll.indeterminate = checked > 0 && checked < total;
        selectAll.checked = total > 0 && checked === total;
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.row-cb').forEach(function (cb) {
            cb.checked = selectAll.checked;
        });
        updateBulkBar();
    });

    document.querySelectorAll('.row-cb').forEach(function (cb) {
        cb.addEventListener('change', updateBulkBar);
    });

    // ── Bulk delete ───────────────────────────────────────────────────────────
    window.confirmBulkDelete = function () {
        var count = document.querySelectorAll('.row-cb:checked').length;
        if (!count) return;
        if (confirm('Delete ' + count + ' category(s)? Posts will be moved to Uncategorised.')) {
            document.getElementById('bulk-form').submit();
        }
    };

    // ── Single-row delete ─────────────────────────────────────────────────────
    window.singleDelete = function (btn) {
        if (!confirm('Delete this category? Posts will be moved to Uncategorised.')) return;
        var form = document.getElementById('single-delete-form');
        form.action = btn.dataset.deleteUrl;
        form.submit();
    };

}());
</script>
@endpush
