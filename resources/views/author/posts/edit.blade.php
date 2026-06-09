@extends('author.layouts.author')

@section('title', 'Edit Post')

@section('page-title', 'Edit Post')
@section('page-subtitle', 'Editing: ' . Str::limit($post->title ?? 'Post', 60))

@section('page-actions')
    @if(($post->status ?? '') === 'published')
    <a href="{{ route('posts.show', $post->slug ?? '#') }}" target="_blank"
       class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-eye me-1"></i>View Live
    </a>
    @endif
@endsection

@push('styles')
<style>
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
    }
    .image-preview-box:hover { border-color: #0d6efd; }
    .image-preview-box img { width: 100%; height: 150px; object-fit: cover; }
    .ck-editor__editable_inline { min-height: 350px; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('author.posts.update', $post) }}" enctype="multipart/form-data"
      x-data="authorPostEditForm()" id="authorPostEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ══ Main Column (col-8) ══ --}}
        <div class="col-xl-8">

            {{-- Title & Slug --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               id="title"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               placeholder="Enter your post title..."
                               value="{{ old('title', $post->title) }}"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div class="mb-0">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug
                            <button type="button"
                                    class="btn btn-link btn-sm p-0 ms-2 text-muted"
                                    style="font-size:.75rem;"
                                    @click="slugEditing = !slugEditing"
                                    x-text="slugEditing ? 'Lock' : 'Edit'">
                            </button>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text text-muted" style="font-size:.8rem;">
                                {{ rtrim(config('app.url'), '/') }}/posts/
                            </span>
                            <input type="text"
                                   name="slug"
                                   id="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   x-model="slug"
                                   :readonly="!slugEditing"
                                   :class="slugEditing ? '' : 'bg-light'">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Click Edit to change the URL slug.</div>
                    </div>

                </div>
            </div>

            {{-- Short Description / Excerpt --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Short Description / Excerpt</h6>
                </div>
                <div class="card-body pt-0">
                    <textarea name="excerpt"
                              class="form-control @error('excerpt') is-invalid @enderror"
                              rows="3"
                              placeholder="Brief description shown in post listings and search results..."
                              maxlength="300">{{ old('excerpt', $post->excerpt) }}</textarea>
                    @error('excerpt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Max 300 characters.</div>
                </div>
            </div>

            {{-- Content --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Content <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body pt-0">
                    <textarea name="content"
                              id="content"
                              class="form-control @error('content') is-invalid @enderror"
                              rows="15">{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>

        {{-- ══ Sidebar Column (col-4) ══ --}}
        <div class="col-xl-4">

            {{-- Publish Settings --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-rocket text-primary me-2"></i>Publish
                    </h6>
                </div>
                <div class="card-body">

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="status" class="form-label small fw-semibold">Status</label>
                        <select name="status" id="status"
                                class="form-select form-select-sm @error('status') is-invalid @enderror"
                                x-model="status">
                            <option value="draft"
                                {{ old('status', $post->status) === 'draft'     ? 'selected' : '' }}>Draft</option>
                            <option value="published"
                                {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled"
                                {{ old('status', $post->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Publish Date (only when scheduled) --}}
                    <div class="mb-3" x-show="status === 'scheduled'" x-transition>
                        <label for="published_at" class="form-label small fw-semibold">Publish Date &amp; Time</label>
                        <input type="datetime-local"
                               name="published_at"
                               id="published_at"
                               class="form-control form-control-sm @error('published_at') is-invalid @enderror"
                               value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
                        @error('published_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-semibold">
                            Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" id="category_id"
                                class="form-select form-select-sm @error('category_id') is-invalid @enderror"
                                required>
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tags --}}
                    <div class="mb-3">
                        <label for="tags" class="form-label small fw-semibold">Tags</label>
                        <select name="tags[]"
                                id="tags"
                                class="form-select form-select-sm @error('tags') is-invalid @enderror"
                                multiple
                                size="4">
                            @foreach($tags ?? [] as $tag)
                                @php
                                    $selectedTags = old('tags', $post->tags->pluck('id')->toArray() ?? []);
                                @endphp
                                <option value="{{ $tag->id }}"
                                    {{ in_array($tag->id, $selectedTags) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Hold Ctrl / Cmd to select multiple.</div>
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
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-image text-info me-2"></i>Featured Image
                    </h6>
                </div>
                <div class="card-body">

                    {{-- Current image or new preview --}}
                    <div class="image-preview-box mb-2" @click="$refs.imageInput.click()">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Featured image preview">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
                                <span class="small">Click to upload a new image</span>
                            </div>
                        </template>
                    </div>

                    <input type="file"
                           name="featured_image"
                           id="featured_image"
                           class="d-none @error('featured_image') is-invalid @enderror"
                           accept="image/*"
                           x-ref="imageInput"
                           @change="previewImage($event)">
                    @error('featured_image')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <div class="d-flex gap-2 mt-2">
                        <button type="button"
                                class="btn btn-outline-primary btn-sm flex-grow-1"
                                @click="$refs.imageInput.click()">
                            <i class="fas fa-upload me-1"></i>Change
                        </button>

                        {{-- Remove existing image --}}
                        @if($post->featured_image ?? false)
                        <button type="button"
                                class="btn btn-outline-danger btn-sm"
                                x-show="!imageChanged"
                                title="Remove current image"
                                @click="removeFeaturedImage()">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif

                        {{-- Undo new selection --}}
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                x-show="imageChanged"
                                title="Undo image change"
                                @click="imagePreview = originalImage; imageChanged = false; $refs.imageInput.value = ''">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>

                    {{-- Hidden flag to signal removal to controller --}}
                    <input type="hidden" name="remove_featured_image" :value="removeFeatured ? '1' : ''">

                    {{-- Remove checkbox (accessible fallback) --}}
                    @if($post->featured_image ?? false)
                    <div class="form-check mt-2" x-show="!imageChanged && !removeFeatured">
                        <input class="form-check-input" type="checkbox"
                               name="remove_featured_image" value="1"
                               id="removeFeaturedImageCheck"
                               @change="removeFeatured = $event.target.checked; if(removeFeatured) imagePreview = null;">
                        <label class="form-check-label small text-muted" for="removeFeaturedImageCheck">
                            Remove current image
                        </label>
                    </div>
                    @endif

                </div>
            </div>

            {{-- SEO --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-search text-success me-2"></i>SEO
                    </h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Title</label>
                        <input type="text"
                               name="meta_title"
                               class="form-control form-control-sm @error('meta_title') is-invalid @enderror"
                               placeholder="Meta title..."
                               value="{{ old('meta_title', $post->meta_title) }}"
                               maxlength="60">
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Max 60 characters.</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description"
                                  class="form-control form-control-sm @error('meta_description') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Meta description..."
                                  maxlength="160">{{ old('meta_description', $post->meta_description) }}</textarea>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Max 160 characters.</div>
                    </div>

                </div>
            </div>

            {{-- Post Stats --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-chart-bar text-warning me-2"></i>Post Stats
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Views</span>
                            <strong>{{ number_format($post->views_count ?? 0) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Comments</span>
                            <strong>{{ $post->comments_count ?? 0 }}</strong>
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

@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.querySelector('#content'), {
    toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','blockQuote','insertTable','mediaEmbed','|','undo','redo','|','code','codeBlock'],
    height: 400,
}).catch(console.error);

function authorPostEditForm() {
    return {
        slug:          '{{ old("slug", $post->slug ?? "") }}',
        slugEditing:   false,
        status:        '{{ old("status", $post->status ?? "draft") }}',
        imagePreview:  {{ ($post->featured_image ?? false) ? '"' . asset('storage/' . $post->featured_image) . '"' : 'null' }},
        originalImage: {{ ($post->featured_image ?? false) ? '"' . asset('storage/' . $post->featured_image) . '"' : 'null' }},
        imageChanged:  false,
        removeFeatured: false,

        previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                this.imagePreview = e.target.result;
                this.imageChanged = true;
                this.removeFeatured = false;
            };
            reader.readAsDataURL(file);
        },

        removeFeaturedImage() {
            this.imagePreview  = null;
            this.removeFeatured = true;
            this.imageChanged  = false;
        }
    }
}
</script>
@endpush
