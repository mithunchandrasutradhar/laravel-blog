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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">All Categories</h6>
        <span class="text-muted small">{{ $categories->total() ?? count($categories ?? []) }} categories</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60" class="ps-3">ID</th>
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
                        <td class="ps-3 text-muted small">{{ $category->id }}</td>
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
                                <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-confirm-delete
                                            data-confirm-title="Delete category?"
                                            data-confirm-text="Posts in this category may be affected."
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            No categories found. <a href="{{ route('admin.categories.create') }}">Create one?</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($categories) && method_exists($categories, 'hasPages') && $categories->hasPages())
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $categories->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
