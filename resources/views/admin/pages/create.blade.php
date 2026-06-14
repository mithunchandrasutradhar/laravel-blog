@extends('admin.layouts.admin')

@section('title', 'Create Page')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}" class="text-decoration-none">Pages</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('page-title', 'Create Page')
@section('page-subtitle', 'Add a new static or legal page')

@section('content')

<form method="POST" action="{{ route('admin.pages.store') }}" x-data="pageForm()">
    @csrf

    <div class="row g-4">

        {{-- Main Content --}}
        <div class="col-xl-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Page Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="e.g. Terms of Service"
                               x-model="title" @input="syncSlug" required autofocus>
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
                                   value="{{ old('slug') }}" placeholder="auto-generated">
                        </div>
                        @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">Public URL: <code>{{ url('/') }}/<span x-text="slug || 'page-slug'"></span></code></div>
                    </div>
                </div>
            </div>

            {{-- Content Editor --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Content</h6>
                </div>
                <div class="card-body">
                    <textarea name="content" id="page_content" class="form-control" rows="16">{{ old('content') }}</textarea>
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
                               value="{{ old('meta_title') }}" maxlength="70"
                               placeholder="Defaults to page title if empty">
                        <div class="form-text">Max 70 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"
                                  maxlength="160" placeholder="Brief description for search engines...">{{ old('meta_description') }}</textarea>
                        <div class="form-text">Max 160 characters.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control"
                               value="{{ old('canonical_url') }}" placeholder="Leave blank to use the page URL">
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
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="show_in_footer"
                               id="show_in_footer" value="1" {{ old('show_in_footer') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="show_in_footer">
                            Show in Footer
                        </label>
                        <div class="form-text">Adds a link to this page in the website footer.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Page
                        </button>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-3">
                    <p class="small text-muted mb-1"><i class="fas fa-info-circle me-1"></i><strong>SEO tips</strong></p>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Keep titles under 70 characters</li>
                        <li>Meta description 120–160 characters</li>
                        <li>Use descriptive, keyword-rich slugs</li>
                        <li>Enable "Show in Footer" for legal pages</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
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
            title: '{{ old('title') }}',
            slug:  '{{ old('slug') }}',
            slugEditing: false,

            syncSlug() {
                if (!this.slugEditing) {
                    this.slug = this.title
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                }
            }
        };
    }
</script>
@endpush
