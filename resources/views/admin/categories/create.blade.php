@extends('admin.layouts.admin')

@section('title', 'Add Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}" class="text-decoration-none">Categories</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('page-title', 'Add New Category')

@push('styles')
<style>
    .image-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s;
        overflow: hidden;
    }
    .image-preview-box:hover { border-color: #0d6efd; }
    .image-preview-box img { width: 100%; height: 140px; object-fit: cover; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data"
      x-data="categoryForm()" @media-picked.window="onMediaPicked($event.detail)">
    @csrf

    <div class="row g-4">
        <div class="col-xl-8">

            {{-- Basic Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Category Details</h6>
                </div>
                <div class="card-body">
                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Category name..." value="{{ old('name') }}"
                               @input="generateSlug($event.target.value)" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Slug --}}
                    <div class="mb-3">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted" style="font-size:.75rem;"
                                    @click="slugEditing = !slugEditing">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </label>
                        <input type="text" name="slug" id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               x-model="slug" :readonly="!slugEditing"
                               :class="slugEditing ? '' : 'bg-light'"
                               value="{{ old('slug') }}">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4" placeholder="Brief description of this category...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Parent --}}
                    <div class="mb-0">
                        <label for="parent_id" class="form-label fw-semibold">Parent Category</label>
                        <select name="parent_id" id="parent_id"
                                class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">None (Top-level)</option>
                            @foreach($parents ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        <input type="text" name="meta_title" class="form-control"
                               placeholder="Meta title..." value="{{ old('meta_title') }}" maxlength="60">
                        <div class="form-text">Leave blank to auto-generate from name. Max 60 chars.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"
                                  placeholder="Meta description..." maxlength="160">{{ old('meta_description') }}</textarea>
                        <div class="form-text">Max 160 chars.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control"
                               placeholder="keyword1, keyword2, keyword3..."
                               value="{{ old('meta_keywords') }}" maxlength="500">
                        <div class="form-text">Comma-separated keywords. Max 500 chars.</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-xl-4">

            {{-- Image --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-image text-info me-2"></i>Category Image</h6>
                </div>
                <div class="card-body">
                    <div class="image-preview-box mb-2" @click="openMediaPicker()" style="cursor:pointer;">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Preview">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-images fa-2x mb-2 d-block"></i>
                                <span class="small">Click to choose from media library</span>
                            </div>
                        </template>
                    </div>
                    <input type="hidden" name="image_path" x-bind:value="selectedMediaPath">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1"
                                @click="openMediaPicker()">
                            <i class="fas fa-images me-1"></i>Choose Image
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" x-show="imagePreview"
                                @click="imagePreview = null; selectedMediaPath = ''">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Icon Picker --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-icons text-warning me-2"></i>Category Icon</h6>
                </div>
                <div class="card-body">
                    @include('admin.partials.icon-picker', ['currentIcon' => old('icon', 'fas fa-folder'), 'inputName' => 'icon'])
                    @error('icon')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Create Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-100 btn-sm">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

@include('admin.partials.media-picker-modal')

@endsection

@push('scripts')
<script>
    function categoryForm() {
        return {
            slug:              '',
            slugEditing:       false,
            imagePreview:      {!! json_encode(old('image_path') ? asset('storage/' . old('image_path')) : null) !!},
            selectedMediaPath: {!! json_encode(old('image_path', '')) !!},

            generateSlug(name) {
                if (this.slugEditing) return;
                this.slug = name
                    .toLowerCase().trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },

            openMediaPicker() {
                window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'featured' } }));
            },

            onMediaPicked(detail) {
                if (detail.context !== 'featured') return;
                this.imagePreview      = detail.url;
                this.selectedMediaPath = detail.file_name;
            },
        }
    }
</script>
@endpush
