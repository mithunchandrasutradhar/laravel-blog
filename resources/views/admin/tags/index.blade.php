@extends('admin.layouts.admin')

@section('title', 'Tags')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tags</li>
@endsection

@section('page-title', 'Tags')
@section('page-subtitle', 'Manage post tags')

@section('content')

<div class="row g-4">

    {{-- ── Inline Create Form ── --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-plus text-primary me-2"></i>Add New Tag</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.tags.store') }}" x-data="tagForm()">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Tag name..." value="{{ old('name') }}"
                               @input="generateSlug($event.target.value)" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label small fw-semibold">
                            Slug
                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-muted" style="font-size:.7rem;"
                                    @click="slugEditing = !slugEditing">
                                <i class="fas fa-edit"></i>
                            </button>
                        </label>
                        <input type="text" name="slug" id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               x-model="slug" :readonly="!slugEditing"
                               :class="slugEditing ? '' : 'bg-light'"
                               value="{{ old('slug') }}">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold">Description</label>
                        <textarea name="description" id="description"
                                  class="form-control" rows="2"
                                  placeholder="Optional description...">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Add Tag
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Tags Table ── --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0">All Tags</h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">{{ $tags->total() ?? count($tags ?? []) }} tags</span>
                    <form method="GET" class="d-flex">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" style="width:160px;"
                                   placeholder="Search..." value="{{ request('search') }}">
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60" class="ps-3">ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Posts</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tags ?? [] as $tag)
                            <tr x-data="{ editing: false, name: '{{ addslashes($tag->name) }}', slug: '{{ addslashes($tag->slug) }}' }">
                                <td class="ps-3 text-muted small">{{ $tag->id }}</td>
                                <td>
                                    <span x-show="!editing" class="fw-semibold">{{ $tag->name }}</span>
                                    <input x-show="editing" type="text" x-model="name"
                                           class="form-control form-control-sm d-inline-block" style="width:auto;">
                                </td>
                                <td>
                                    <span x-show="!editing">
                                        <code class="small bg-light px-2 py-1 rounded">{{ $tag->slug }}</code>
                                    </span>
                                    <input x-show="editing" type="text" x-model="slug"
                                           class="form-control form-control-sm d-inline-block" style="width:auto;">
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $tag->posts_count ?? 0 }}</span>
                                </td>
                                <td>
                                    {{-- Quick inline save --}}
                                    <form method="POST" action="{{ route('admin.tags.update', $tag->id) }}"
                                          x-show="editing" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" :value="name">
                                        <input type="hidden" name="slug" :value="slug">
                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Save">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                @click="editing=false" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>

                                    <div class="d-flex gap-1" x-show="!editing">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                @click="editing=true" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.tags.destroy', $tag->id) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-confirm-delete
                                                    data-confirm-title="Delete tag?"
                                                    data-confirm-text="This tag will be removed from all posts."
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
                                    <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                    No tags found. Add one using the form.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(isset($tags) && method_exists($tags, 'hasPages') && $tags->hasPages())
            <div class="card-footer bg-transparent border-0 py-3">
                {{ $tags->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function tagForm() {
        return {
            slug: '',
            slugEditing: false,
            generateSlug(name) {
                if (this.slugEditing) return;
                this.slug = name.toLowerCase().trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }
        }
    }
</script>
@endpush
