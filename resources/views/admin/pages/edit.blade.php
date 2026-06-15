@extends('admin.layouts.admin')

@section('title', 'Edit Page')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}" class="text-decoration-none">Pages</a></li>
    <li class="breadcrumb-item active">{{ $page->title }}</li>
@endsection

@section('page-title', 'Edit Page')
@section('page-subtitle', $page->title)

@section('content')

{{-- Update form wraps main content + sidebar (no delete form inside) --}}
<form id="update-form" method="POST" action="{{ route('admin.pages.update', $page) }}" x-data="pageForm()">
    @csrf @method('PUT')

    <div class="row g-4">

        {{-- Main Content --}}
        <div class="col-xl-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Page Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                               value="{{ old('title', $page->title) }}" x-model="title" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">
                            URL Slug
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted" style="font-size:.75rem;"
                                    @click="slugEditing = !slugEditing">
                                <i class="fas fa-edit"></i> <span x-text="slugEditing ? 'Lock' : 'Edit'"></span>
                            </button>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text text-muted small">/</span>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   x-model="slug" :readonly="!slugEditing" :class="slugEditing ? '' : 'bg-light'"
                                   value="{{ old('slug', $page->slug) }}">
                        </div>
                        @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">
                            Public URL: <a href="{{ route('blog.show', $page->slug) }}" target="_blank">{{ url('/') }}/{{ $page->slug }}</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content Editor --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Content</h6>
                </div>
                <div class="card-body">
                    <textarea name="content" id="page_content" class="form-control" rows="16">{{ old('content', $page->content) }}</textarea>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-search text-success me-2"></i>SEO</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control"
                               value="{{ old('meta_title', $page->meta_title) }}" maxlength="70"
                               placeholder="Defaults to page title if empty">
                        <div class="form-text">Max 70 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"
                                  maxlength="160">{{ old('meta_description', $page->meta_description) }}</textarea>
                        <div class="form-text">Max 160 characters.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control"
                               value="{{ old('canonical_url', $page->canonical_url) }}"
                               placeholder="Leave blank to use the page URL">
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-xl-4">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Publish</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" {{ old('status', $page->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $page->status) === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="show_in_footer"
                               id="show_in_footer" value="1"
                               {{ old('show_in_footer', $page->show_in_footer) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="show_in_footer">
                            Show in Footer
                        </label>
                        <div class="form-text">Adds a link in the website footer.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" form="update-form" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Page
                        </button>
                        <a href="{{ route('blog.show', $page->slug) }}" target="_blank"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Page
                        </a>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-link btn-sm text-muted">Back to Pages</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm border-danger-subtle">
                <div class="card-body p-3">
                    <p class="small fw-semibold text-danger mb-2"><i class="fas fa-trash me-1"></i>Delete Page</p>
                    {{-- form="delete-form" links this button to the standalone delete form below --}}
                    <button type="submit" form="delete-form"
                            onclick="return confirm('Permanently delete this page?')"
                            class="btn btn-outline-danger btn-sm w-100">
                        Delete this page
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>

{{-- Standalone delete form — outside the update form so _method values never conflict --}}
<form id="delete-form" method="POST" action="{{ route('admin.pages.destroy', $page) }}">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script src="{{ asset('js/ckeditor5-classic.js') }}"></script>
<script>
    ClassicEditor.create(document.querySelector('#page_content'), {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', 'removeFormat', '|',
                'link', 'blockQuote', 'horizontalLine', '|',
                'bulletedList', 'numberedList', '|',
                'alignment', '|',
                'undo', 'redo', '|',
                'sourceEditing'
            ]
        }
    }).catch(console.error);

    function pageForm() {
        return {
            title: {!! json_encode(old('title', $page->title)) !!},
            slug:  {!! json_encode(old('slug', $page->slug)) !!},
            slugEditing: false,
        };
    }
</script>
@endpush

