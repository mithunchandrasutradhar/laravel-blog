@extends('admin.layouts.admin')

@section('title', 'Videos')

@section('breadcrumb')
    <li class="breadcrumb-item active">Videos</li>
@endsection

@section('page-title', 'YouTube Videos')
@section('page-subtitle', 'Manage videos shown on the home page and Videos page')

@section('page-actions')
    <a href="{{ route('admin.videos.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add New Video
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">All Videos</h6>
        <span class="text-muted small">{{ $videos->total() }} video{{ $videos->total() !== 1 ? 's' : '' }}</span>
    </div>

    <div class="card-body p-0">
        @if($videos->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fab fa-youtube fa-3x mb-3 d-block" style="color:#e2e8f0;"></i>
                <p class="mb-0">No videos yet. <a href="{{ route('admin.videos.create') }}">Add your first video</a>.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60" class="ps-3">ID</th>
                        <th width="100">Thumbnail</th>
                        <th>Title</th>
                        <th width="140">Category</th>
                        <th width="90">Order</th>
                        <th width="90">Status</th>
                        <th width="110">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($videos as $video)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $video->id }}</td>
                        <td>
                            @if($video->youtube_id)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}"
                                 class="rounded" width="80" height="45" style="object-fit:cover;">
                            @else
                            <div class="rounded d-flex align-items-center justify-content-center bg-light"
                                 style="width:80px;height:45px;">
                                <i class="fab fa-youtube text-danger"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $video->title }}</div>
                            @if($video->description)
                            <div class="text-muted small">{{ Str::limit($video->description, 70) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($video->category)
                                <span class="badge rounded-pill"
                                      style="background:{{ $video->category->color ?? '#4f46e5' }}20;color:{{ $video->category->color ?? '#4f46e5' }};font-weight:600;">
                                    <i class="{{ $video->category->icon ?? 'fas fa-folder' }} me-1"></i>
                                    {{ $video->category->name }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $video->sort_order }}</td>
                        <td>
                            @if($video->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Hidden</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.videos.edit', $video) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.videos.destroy', $video) }}" method="POST"
                                      onsubmit="return confirm('Delete this video?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($videos->hasPages())
        <div class="p-3 border-top">
            {{ $videos->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@endsection
