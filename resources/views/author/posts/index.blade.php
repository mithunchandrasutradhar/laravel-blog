@extends('author.layouts.author')

@section('title', 'My Posts')

@section('page-title', 'My Posts')
@section('page-subtitle', 'Manage all posts you have written')

@section('page-actions')
    <a href="{{ route('author.posts.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>New Post
    </a>
@endsection

@section('content')

{{-- ── Filter Bar ── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('author.posts.index') }}" class="row g-2 align-items-end">

            {{-- Search --}}
            <div class="col-md-5">
                <label class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search your posts..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Status --}}
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>

            {{-- Actions --}}
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('author.posts.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                    <i class="fas fa-times"></i>
                </a>
            </div>

        </form>
    </div>
</div>

{{-- ── Posts Table ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <span class="text-muted small">
            {{ $posts->total() ?? 0 }} post{{ ($posts->total() ?? 0) !== 1 ? 's' : '' }} found
        </span>
        <a href="{{ route('author.posts.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Post
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="70" class="ps-3">Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts ?? [] as $post)
                    <tr>
                        {{-- Thumbnail --}}
                        <td class="ps-3">
                            @if($post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}"
                                     alt="{{ $post->title }}"
                                     class="rounded"
                                     style="width:56px;height:38px;object-fit:cover;">
                            @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                     style="width:56px;height:38px;">
                                    <i class="fas fa-image text-muted small"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Title --}}
                        <td>
                            <a href="{{ route('author.posts.edit', $post->id) }}"
                               class="fw-semibold text-dark text-decoration-none">
                                {{ Str::limit($post->title, 50) }}
                            </a>
                            @if($post->is_featured ?? false)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">
                                    <i class="fas fa-star me-1"></i>Featured
                                </span>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td>
                            <span class="small text-muted">{{ $post->category->name ?? '—' }}</span>
                        </td>

                        {{-- Status badge --}}
                        <td>
                            @if($post->status === 'published')
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-check-circle me-1" style="font-size:.65rem;"></i>Published
                                </span>
                            @elseif($post->status === 'draft')
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    <i class="fas fa-file-alt me-1" style="font-size:.65rem;"></i>Draft
                                </span>
                            @elseif($post->status === 'scheduled')
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-clock me-1" style="font-size:.65rem;"></i>Scheduled
                                </span>
                            @else
                                <span class="badge bg-light text-secondary">{{ ucfirst($post->status) }}</span>
                            @endif
                        </td>

                        {{-- Views --}}
                        <td class="small text-muted">{{ number_format($post->views_count ?? 0) }}</td>

                        {{-- Date --}}
                        <td class="small text-muted text-nowrap">
                            {{ $post->created_at->format('M d, Y') }}
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="d-flex gap-1">
                                {{-- Edit --}}
                                <a href="{{ route('author.posts.edit', $post->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- View live --}}
                                @if($post->status === 'published')
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary" title="View live">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif

                                {{-- Delete --}}
                                <form method="POST"
                                      action="{{ route('author.posts.destroy', $post->id) }}"
                                      class="d-inline"
                                      id="deletePostForm{{ $post->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-confirm-delete
                                            data-confirm-title="Delete this post?"
                                            data-confirm-text="&quot;{{ addslashes(Str::limit($post->title, 40)) }}&quot; will be permanently deleted.">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-newspaper fa-3x mb-3 d-block text-muted opacity-50"></i>
                            <p class="mb-2">You haven't written any posts yet.</p>
                            <a href="{{ route('author.posts.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Write your first post
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if(isset($posts) && $posts->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
        <div class="small text-muted">
            Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }}
        </div>
        {{ $posts->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
