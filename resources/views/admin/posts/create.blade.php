@extends('admin.layouts.admin')

@section('title', 'Add New Post')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}" class="text-decoration-none">Posts</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('page-title', 'Add New Post')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-control { min-height: 38px; }
    .image-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s;
        overflow: hidden;
        position: relative;
    }
    .image-preview-box:hover { border-color: #0d6efd; }
    .image-preview-box img { width: 100%; height: 150px; object-fit: cover; }
    .ck-editor__editable_inline { min-height: 350px; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data"
      x-data="postForm()" id="postCreateForm">
    @csrf

    <div class="row g-4">

        {{-- ══ Main Column ══ --}}
        <div class="col-xl-8">

            {{-- Title --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                               placeholder="Enter post title..." value="{{ old('title') }}"
                               @input="generateSlug($event.target.value)" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Slug --}}
                    <div class="mb-0">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted" style="font-size:.75rem;"
                                    @click="slugEditing = !slugEditing">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text text-muted" style="font-size:.8rem;">
                                {{ rtrim(config('app.url'), '/') }}/posts/
                            </span>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                   x-model="slug" :readonly="!slugEditing"
                                   :class="slugEditing ? '' : 'bg-light'"
                                   value="{{ old('slug') }}">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Content</h6>
                </div>
                <div class="card-body pt-0">
                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror"
                              rows="15">{{ old('content') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Short Description --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Short Description / Excerpt</h6>
                </div>
                <div class="card-body pt-0">
                    <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror"
                              rows="3" placeholder="Brief description shown in listings..."
                              maxlength="300">{{ old('excerpt') }}</textarea>
                    @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Max 300 characters.</div>
                </div>
            </div>

            {{-- Tags --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Tags</h6>
                </div>
                <div class="card-body pt-0">
                    <select name="tags[]" id="tagsSelect" multiple class="form-select @error('tags') is-invalid @enderror"
                            placeholder="Select or add tags...">
                        @foreach($tags ?? [] as $tag)
                            <option value="{{ $tag->id }}"
                                {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tags')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>

        {{-- ══ Sidebar Column ══ --}}
        <div class="col-xl-4">

            {{-- Publish Settings --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-rocket text-primary me-2"></i>Publish</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label small fw-semibold">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm"
                                x-model="status" @change="status=$el.value">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>

                    <div class="mb-3" x-show="status === 'scheduled'" x-transition>
                        <label for="published_at" class="form-label small fw-semibold">Publish Date</label>
                        <input type="datetime-local" name="published_at" id="published_at"
                               class="form-control form-control-sm @error('published_at') is-invalid @enderror"
                               value="{{ old('published_at') }}">
                        @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id"
                                class="form-select form-select-sm @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                               value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="is_featured">Featured Post</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_comments" id="allow_comments"
                               value="1" checked {{ old('allow_comments', true) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="allow_comments">Allow Comments</label>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Publish Post
                    </button>
                    <button type="submit" name="status" value="draft" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                        Save as Draft
                    </button>
                </div>
            </div>

            {{-- Featured Image --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-image text-info me-2"></i>Featured Image</h6>
                </div>
                <div class="card-body">
                    <div class="image-preview-box mb-2" @click="$refs.imageInput.click()">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Preview">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
                                <span class="small">Click to upload image</span>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="featured_image" id="featured_image" class="d-none"
                           accept="image/*" x-ref="imageInput"
                           @change="previewImage($event)">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1"
                                @click="$refs.imageInput.click()">
                            <i class="fas fa-upload me-1"></i>Upload
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" x-show="imagePreview"
                                @click="imagePreview=null; $refs.imageInput.value=''">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-search text-success me-2"></i>SEO</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm"
                               placeholder="Meta title..." value="{{ old('meta_title') }}" maxlength="60">
                        <div class="form-text">Max 60 chars.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description" class="form-control form-control-sm" rows="2"
                                  placeholder="Meta description..." maxlength="160">{{ old('meta_description') }}</textarea>
                        <div class="form-text">Max 160 chars.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control form-control-sm"
                               placeholder="https://..." value="{{ old('canonical_url') }}">
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
{{-- CKEditor 5 --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
{{-- Tom Select --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    // ── CKEditor init ──
    ClassicEditor.create(document.querySelector('#content'), {
        toolbar: {
            items: [
                'heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|',
                'link', 'blockQuote', 'code', 'codeBlock', '|',
                'bulletedList', 'numberedList', 'todoList', '|',
                'imageUpload', 'insertTable', 'mediaEmbed', '|',
                'undo', 'redo', '|', 'sourceEditing'
            ]
        },
        simpleUpload: {
            uploadUrl: '{{ route("admin.media.upload") }}',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }
    }).catch(console.error);

    // ── Tom Select for tags ──
    new TomSelect('#tagsSelect', {
        plugins: ['remove_button'],
        create: true,
        createOnBlur: true,
        placeholder: 'Select or create tags...',
    });

    // ── Alpine component ──
    function postForm() {
        return {
            slug: '{{ old("slug") }}',
            slugEditing: false,
            status: '{{ old("status", "draft") }}',
            imagePreview: null,

            generateSlug(title) {
                if (this.slugEditing) return;
                this.slug = title
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },

            previewImage(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => this.imagePreview = e.target.result;
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
