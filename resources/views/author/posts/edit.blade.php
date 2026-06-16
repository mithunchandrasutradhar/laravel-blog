@extends('author.layouts.author')

@section('title', 'Edit Post')
@section('page-title', 'Edit Post')
@section('page-subtitle', 'Editing: ' . Str::limit($post->title ?? 'Post', 60))

@section('page-actions')
    @if(($post->status ?? '') === 'published')
    <a href="{{ route('blog.show', $post->slug ?? '#') }}" target="_blank"
       class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-eye me-1"></i>View Live
    </a>
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tom-select.bootstrap5.min.css') }}">
<style>
    .ts-control { min-height: 34px; }
    .image-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        min-height: 160px;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; transition: border-color .2s; position: relative;
    }
    .image-preview-box:hover { border-color: #0d6efd; }
    .image-preview-box img { width: 100%; height: 160px; object-fit: cover; }
    .ck-editor__editable_inline { min-height: 380px; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('author.posts.update', $post) }}" enctype="multipart/form-data"
      x-data="authorPostEditForm()" id="authorPostEditForm"
      @media-picked.window="onMediaPicked($event.detail)">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ══ Main Column ══ --}}
        <div class="col-xl-8">

            {{-- Title & Slug --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               placeholder="Enter your post title..."
                               value="{{ old('title', $post->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted"
                                    style="font-size:.75rem;"
                                    @click="slugEditing = !slugEditing"
                                    x-text="slugEditing ? 'Lock' : 'Edit'"></button>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text text-muted" style="font-size:.8rem;">
                                {{ rtrim(config('app.url'), '/') }}/
                            </span>
                            <input type="text" name="slug" id="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   x-model="slug" :readonly="!slugEditing"
                                   :class="slugEditing ? '' : 'bg-light'">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Click Edit to change the URL slug.</div>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Content <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body pt-0">
                    <textarea name="content" id="content"
                              class="form-control @error('content') is-invalid @enderror"
                              rows="15">{{ old('content', $post->content) }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Short Description --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Short Description / Excerpt</h6>
                </div>
                <div class="card-body pt-0">
                    <textarea name="short_description"
                              class="form-control @error('short_description') is-invalid @enderror"
                              rows="3"
                              placeholder="Brief description shown in post listings and search results..."
                              maxlength="500">{{ old('short_description', $post->short_description) }}</textarea>
                    @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Max 500 characters.</div>
                </div>
            </div>

            {{-- Tags --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Tags</h6>
                </div>
                <div class="card-body pt-0">
                    @php $selectedTagIds = old('tags', $post->tags->pluck('id')->toArray()); @endphp
                    <select name="tags[]" id="tagsSelect" multiple
                            class="form-select @error('tags') is-invalid @enderror"
                            placeholder="Select or create tags...">
                        @foreach($tags ?? [] as $tag)
                            <option value="{{ $tag->id }}"
                                {{ in_array($tag->id, $selectedTagIds) ? 'selected' : '' }}>
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
                        <select name="status" id="status" class="form-select form-select-sm" x-model="status">
                            <option value="draft"
                                {{ old('status', $post->status) === 'draft'     ? 'selected' : '' }}>Draft</option>
                            <option value="published"
                                {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled"
                                {{ old('status', $post->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3" x-show="status === 'scheduled'" x-transition>
                        <label for="published_at" class="form-label small fw-semibold">Publish Date &amp; Time</label>
                        <input type="datetime-local" name="published_at" id="published_at"
                               class="form-control form-control-sm @error('published_at') is-invalid @enderror"
                               value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
                        @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="categorySelect" class="form-label small fw-semibold">
                            Categories <span class="text-danger">*</span>
                        </label>
                        @php
                            $selectedCatIds = old('category_ids', $selectedCatIds ?? []);
                            if (empty($selectedCatIds) && $post->category_id) {
                                $selectedCatIds = [$post->category_id];
                            }
                        @endphp
                        <select name="category_ids[]" id="categorySelect" multiple
                                class="form-select form-select-sm @error('category_ids') is-invalid @enderror"
                                placeholder="Select categories...">
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}"
                                    {{ in_array($category->id, $selectedCatIds) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="allow_comments"
                               id="allow_comments" value="1"
                               {{ old('allow_comments', $post->allow_comments ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="allow_comments">Allow Comments</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured"
                               id="is_featured" value="1"
                               {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="is_featured">Featured Post</label>
                    </div>

                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Update Post
                    </button>
                </div>
            </div>

            {{-- Featured Image --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-image text-info me-2"></i>Featured Image</h6>
                </div>
                <div class="card-body">
                    <div class="image-preview-box mb-2" @click="openMediaPicker()" style="cursor:pointer;">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Featured image preview">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-images fa-2x mb-2 d-block"></i>
                                <span class="small">Click to choose from media library</span>
                            </div>
                        </template>
                    </div>

                    {{-- Media picker path OR removal flag --}}
                    <input type="hidden" name="featured_image_path" x-bind:value="selectedMediaPath">
                    <input type="hidden" name="remove_featured_image" :value="removeFeatured ? '1' : ''">

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1"
                                @click="openMediaPicker()">
                            <i class="fas fa-images me-1"></i>Choose Image
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                x-show="imageChanged" title="Undo"
                                @click="imagePreview = originalImage; selectedMediaPath = originalPath; imageChanged = false; removeFeatured = false;">
                            <i class="fas fa-undo"></i>
                        </button>
                        @if($post->featured_image ?? false)
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                x-show="!imageChanged && !removeFeatured" title="Remove image"
                                @click="imagePreview = null; selectedMediaPath = ''; removeFeatured = true;">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
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
                        <input type="text" name="meta_title"
                               class="form-control form-control-sm @error('meta_title') is-invalid @enderror"
                               placeholder="Meta title..."
                               value="{{ old('meta_title', $post->meta_title) }}" maxlength="60">
                        @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Max 60 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description"
                                  class="form-control form-control-sm @error('meta_description') is-invalid @enderror"
                                  rows="3" placeholder="Meta description..."
                                  maxlength="160">{{ old('meta_description', $post->meta_description) }}</textarea>
                        @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Max 160 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Keywords</label>
                        <input type="text" name="meta_keywords"
                               class="form-control form-control-sm @error('meta_keywords') is-invalid @enderror"
                               placeholder="keyword1, keyword2, keyword3..."
                               value="{{ old('meta_keywords', $post->meta_keywords) }}" maxlength="500">
                        @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Comma-separated keywords. Max 500 characters.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Canonical URL</label>
                        <input type="url" name="canonical_url"
                               class="form-control form-control-sm @error('canonical_url') is-invalid @enderror"
                               placeholder="https://..."
                               value="{{ old('canonical_url', $post->canonical_url) }}">
                        @error('canonical_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Post Stats --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar text-warning me-2"></i>Post Stats</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Views</span>
                            <strong>{{ number_format($post->views_count ?? 0) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Comments</span>
                            <strong>{{ $post->comments_count ?? $post->comments()->count() }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Created</span>
                            <strong>{{ $post->created_at->format('M d, Y') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Last updated</span>
                            <strong>{{ $post->updated_at->format('M d, Y') }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</form>

@include('author.partials.media-picker-modal')

@endsection

@push('scripts')
<script>
function authorPostEditForm() {
    return {
        slug:              '{{ old("slug", $post->slug ?? "") }}',
        slugEditing:       false,
        status:            '{{ old("status", $post->status ?? "draft") }}',
        imagePreview:      {!! ($post->featured_image ?? false) ? json_encode(asset('storage/' . $post->featured_image)) : 'null' !!},
        originalImage:     {!! ($post->featured_image ?? false) ? json_encode(asset('storage/' . $post->featured_image)) : 'null' !!},
        selectedMediaPath: {!! ($post->featured_image ?? false) ? json_encode($post->featured_image) : "''" !!},
        originalPath:      {!! ($post->featured_image ?? false) ? json_encode($post->featured_image) : "''" !!},
        imageChanged:      false,
        removeFeatured:    false,

        openMediaPicker() {
            window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'featured' } }));
        },

        onMediaPicked(detail) {
            if (detail.context !== 'featured') return;
            this.imagePreview      = detail.url;
            this.selectedMediaPath = detail.file_name;
            this.imageChanged      = true;
            this.removeFeatured    = false;
        },
    }
}
</script>

<script src="{{ asset('js/ckeditor5-classic.js') }}"></script>
<script src="{{ asset('js/tom-select.min.js') }}"></script>

<script>
    let _ckAuthorEdit;

    try {
        ClassicEditor.create(document.querySelector('#content'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'removeFormat', '|',
                    'fontColor', 'highlight', '|',
                    'alignment', '|',
                    'link', 'blockQuote', 'code', 'codeBlock', '|',
                    'bulletedList', 'numberedList', 'horizontalLine', '|',
                    'imageUpload', 'insertTable', 'mediaEmbed', '|',
                    'undo', 'redo', '|', 'sourceEditing'
                ]
            },
            mediaEmbed: { previewsInData: true },
            simpleUpload: {
                uploadUrl: '{{ route("author.media.ckeditor-upload") }}',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }
        }).then(editor => {
            _ckAuthorEdit = editor;

            const toolbar  = editor.ui.view.toolbar.element;
            const sep      = document.createElement('div');
            sep.className  = 'ck ck-toolbar__separator';
            toolbar.appendChild(sep);

            const mediaBtn         = document.createElement('button');
            mediaBtn.type          = 'button';
            mediaBtn.className     = 'ck ck-button ck-off';
            mediaBtn.title         = 'Insert image from My Media';
            mediaBtn.innerHTML     =
                '<svg class="ck ck-icon ck-button__icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">' +
                '<path d="M6.91 10.54c.26-.23.64-.21.88.03l3.36 3.14 2.23-2.06a.64.64 0 0 1 .87 0l2.52 2.97V4.5H3.2v10.12l3.71-4.08zm10.27-7.51c.6 0 1.09.47 1.09 1.05v11.84c0 .59-.49 1.06-1.09 1.06H2.79c-.6 0-1.09-.47-1.09-1.06V4.08c0-.58.49-1.05 1.09-1.05h14.39zm-5.2 2.77a1.64 1.64 0 1 1 0 3.28 1.64 1.64 0 0 1 0-3.28z"/>' +
                '</svg>' +
                '<span class="ck ck-button__label">Media Library</span>';
            mediaBtn.addEventListener('mousedown', e => {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'editor' } }));
            });
            toolbar.appendChild(mediaBtn);

            document.getElementById('authorPostEditForm').addEventListener('submit', () => {
                editor.updateSourceElement();
            });

            window.addEventListener('media-picked', function(e) {
                if (e.detail.context !== 'editor' || !_ckAuthorEdit) return;
                try {
                    _ckAuthorEdit.model.change(writer => {
                        const imgName = _ckAuthorEdit.model.schema.isRegistered('imageBlock') ? 'imageBlock' : 'image';
                        const img     = writer.createElement(imgName, { src: e.detail.url, alt: e.detail.name || '' });
                        _ckAuthorEdit.model.insertContent(img, _ckAuthorEdit.model.document.selection);
                    });
                } catch (err) { console.error('CKEditor insert error', err); }
            });
        }).catch(console.error);
    } catch (e) { console.error('CKEditor init error:', e); }

    try {
        new TomSelect('#tagsSelect', {
            plugins: ['remove_button'],
            create: true,
            createOnBlur: true,
            placeholder: 'Select or create tags...',
        });
    } catch (e) { console.error('TomSelect tags error:', e); }

    try {
        new TomSelect('#categorySelect', {
            plugins: ['remove_button'],
            create: false,
            placeholder: 'Select categories...',
            maxItems: null,
        });
    } catch (e) { console.error('TomSelect categories error:', e); }
</script>
@endpush
