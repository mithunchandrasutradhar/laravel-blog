@extends('admin.layouts.admin')

@section('title', 'Pages')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pages</li>
@endsection

@section('page-title', 'Pages')
@section('page-subtitle', 'Manage static and legal pages')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0"><i class="fas fa-file-alt text-primary me-2"></i>All Pages</h6>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Page
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Slug / URL</th>
                        <th>Status</th>
                        <th>Footer</th>
                        <th>Updated</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $page->title }}</td>
                        <td>
                            <a href="{{ route('pages.show', $page->slug) }}" target="_blank"
                               class="text-muted text-decoration-none small">
                                /{{ $page->slug }} <i class="fas fa-external-link-alt ms-1" style="font-size:.65rem;"></i>
                            </a>
                        </td>
                        <td>
                            @if($page->status === 'published')
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            @if($page->show_in_footer)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $page->updated_at->format('M d, Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this page? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-file-alt fa-2x mb-2 d-block opacity-25"></i>
                            No pages yet.
                            <a href="{{ route('admin.pages.create') }}">Create your first page</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

