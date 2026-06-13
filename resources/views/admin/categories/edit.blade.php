@extends('admin.layouts.admin')

@section('title', 'Edit Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}" class="text-decoration-none">Categories</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('page-title', 'Edit Category')
@section('page-subtitle', $category->name ?? '')

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

<form method="POST" action="{{ route('admin.categories.update', $category->id) }}" enctype="multipart/form-data"
      x-data="categoryEditForm()">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-xl-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Category Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $category->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted" style="font-size:.75rem;"
                                    @click="slugEditing = !slugEditing">
                                <i class="fas fa-edit"></i> <span x-text="slugEditing ? 'Lock' : 'Edit'"></span>
                            </button>
                        </label>
                        <input type="text" name="slug" id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               x-model="slug" :readonly="!slugEditing"
                               :class="slugEditing ? '' : 'bg-light'">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4">{{ old('description', $category->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="parent_id" class="form-label fw-semibold">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">None (Top-level)</option>
                            @foreach($categories ?? [] as $cat)
                                @if($cat->id !== $category->id)
                                <option value="{{ $cat->id }}"
                                    {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-search text-success me-2"></i>SEO</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control"
                               value="{{ old('meta_title', $category->meta_title) }}" maxlength="60">
                        <div class="form-text">Max 60 chars.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"
                                  maxlength="160">{{ old('meta_description', $category->meta_description) }}</textarea>
                        <div class="form-text">Max 160 chars.</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-xl-4">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-image text-info me-2"></i>Category Image</h6>
                </div>
                <div class="card-body">
                    <div class="image-preview-box mb-2" @click="$refs.imageInput.click()">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Preview">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
                                <span class="small">Click to change image</span>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="image" class="d-none" accept="image/*"
                           x-ref="imageInput" @change="previewImage($event)">
                    <input type="hidden" name="remove_image" x-bind:value="removeImage ? '1' : ''">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1"
                                @click="$refs.imageInput.click()">
                            <i class="fas fa-upload me-1"></i>Change
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" x-show="imagePreview"
                                @click="imagePreview=null; removeImage=true; $refs.imageInput.value=''">
                            <i class="fas fa-trash"></i>
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
                    @include('admin.partials.icon-picker', ['currentIcon' => old('icon', $category->icon ?? 'fas fa-folder'), 'inputName' => 'icon'])
                    @error('icon')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Posts</span>
                            <strong>{{ $category->posts_count ?? 0 }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Created</span>
                            <strong>{{ $category->created_at->format('M d, Y') }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Update Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-100 btn-sm">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function categoryEditForm() {
        return {
            slug: '{{ old("slug", $category->slug ?? "") }}',
            slugEditing: false,
            imagePreview: {{ $category->image ? '"' . asset('storage/' . $category->image) . '"' : 'null' }},
            removeImage: false,

            previewImage(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => {
                    this.imagePreview = e.target.result;
                    this.removeImage = false;
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
