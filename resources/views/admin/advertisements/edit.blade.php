@extends('admin.layouts.admin')

@section('title', 'Edit Advertisement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.advertisements.index') }}" class="text-decoration-none">Advertisements</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('page-title', 'Edit Advertisement')
@section('page-subtitle', $advertisement->name ?? '')

@push('styles')
<style>
    .banner-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8f9fa;
    }
    .banner-preview-box img { max-width: 100%; max-height: 200px; object-fit: contain; }
    .media-picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .5rem; }
    .media-picker-item { aspect-ratio: 1; cursor: pointer; border-radius: .375rem; overflow: hidden; border: 2px solid transparent; transition: border-color .15s; }
    .media-picker-item:hover, .media-picker-item.selected { border-color: #0d6efd; }
    .media-picker-item img { width: 100%; height: 100%; object-fit: cover; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.advertisements.update', $advertisement->id) }}"
      enctype="multipart/form-data" x-data="adEditForm()">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-xl-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">Advertisement Details</h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $advertisement->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror"
                                    x-model="adType" required>
                                <option value="adsense" {{ old('type', $advertisement->type) === 'adsense' ? 'selected' : '' }}>Google AdSense</option>
                                <option value="banner"  {{ old('type', $advertisement->type) === 'banner'  ? 'selected' : '' }}>Banner Image</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="position" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                            <select name="position" id="position" class="form-select @error('position') is-invalid @enderror" required>
                                <option value="header"     {{ old('position', $advertisement->position) === 'header'     ? 'selected' : '' }}>Header</option>
                                <option value="sidebar"    {{ old('position', $advertisement->position) === 'sidebar'    ? 'selected' : '' }}>Sidebar</option>
                                <option value="in-article" {{ old('position', $advertisement->position) === 'in-article' ? 'selected' : '' }}>In-Article</option>
                                <option value="footer"     {{ old('position', $advertisement->position) === 'footer'     ? 'selected' : '' }}>Footer</option>
                            </select>
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- AdSense --}}
                    <div x-show="adType === 'adsense'" x-transition>
                        <div class="mb-3">
                            <label for="code" class="form-label fw-semibold">AdSense Code</label>
                            <textarea name="code" id="code"
                                      class="form-control font-monospace @error('code') is-invalid @enderror"
                                      rows="8">{{ old('code', $advertisement->code) }}</textarea>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Banner --}}
                    <div x-show="adType === 'banner'" x-transition>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Banner Image</label>
                            <div class="banner-preview-box mb-2">
                                <img x-show="bannerPreview" :src="bannerPreview" alt="Banner preview">
                                <div x-show="!bannerPreview" class="text-center text-muted p-3">
                                    <i class="fas fa-image fa-2x mb-2 d-block"></i>
                                    <span class="small">No image selected</span>
                                </div>
                            </div>
                            <input type="hidden" name="media_path" :value="mediaPath">
                            <input type="hidden" name="remove_image" :value="removeImage ? '1' : ''">
                            <input type="file" name="image" class="d-none" accept="image/*"
                                   x-ref="bannerInput" @change="previewUpload($event)">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm"
                                        @click="openPicker()">
                                    <i class="fas fa-photo-video me-1"></i>Media Library
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                        @click="$refs.bannerInput.click()">
                                    <i class="fas fa-upload me-1"></i>Upload File
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        x-show="bannerPreview"
                                        @click="clearBanner()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="url" class="form-label fw-semibold">Click URL</label>
                            <input type="url" name="url" id="url"
                                   class="form-control @error('url') is-invalid @enderror"
                                   value="{{ old('url', $advertisement->url) }}">
                            @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-cog text-primary me-2"></i>Settings</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                               value="1" {{ old('is_active', $advertisement->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Update Advertisement
                    </button>
                    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-outline-secondary w-100 btn-sm">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Media Picker Modal ── --}}
    <div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Select from Media Library</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm" placeholder="Search images..."
                               x-model="pickerSearch" @input.debounce.400ms="pickerLoad(1)">
                    </div>
                    <div x-show="pickerLoading" class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                    <div x-show="!pickerLoading && pickerImages.length === 0" class="text-center py-4 text-muted">
                        <i class="fas fa-image fa-2x mb-2 d-block"></i>No images found.
                    </div>
                    <div class="media-picker-grid" x-show="!pickerLoading">
                        <template x-for="img in pickerImages" :key="img.id">
                            <div class="media-picker-item"
                                 :class="{ selected: pickerSelected === img.id }"
                                 @click="pickerSelect(img)">
                                <img :src="img.url" :alt="img.name" loading="lazy">
                            </div>
                        </template>
                    </div>
                    <div class="text-center mt-3" x-show="pickerHasMore && !pickerLoading">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                @click="pickerLoad(pickerPage + 1, true)">
                            Load more
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm"
                            :disabled="!pickerSelected"
                            @click="confirmPicker()">
                        Use Selected Image
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
function adEditForm() {
    return {
        adType: '{{ old("type", $advertisement->type ?? "") }}',
        bannerPreview: {{ $advertisement->image ? json_encode(asset('storage/' . $advertisement->image)) : 'null' }},
        mediaPath: '',
        removeImage: false,

        // Media picker state
        pickerModal: null,
        pickerImages: [],
        pickerSearch: '',
        pickerPage: 1,
        pickerHasMore: false,
        pickerLoading: false,
        pickerSelected: null,
        pickerSelectedImg: null,

        openPicker() {
            if (!this.pickerModal) {
                this.pickerModal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
            }
            this.pickerSelected = null;
            this.pickerSelectedImg = null;
            this.pickerLoad(1);
            this.pickerModal.show();
        },

        async pickerLoad(page = 1, append = false) {
            this.pickerLoading = true;
            this.pickerPage = page;
            const params = new URLSearchParams({ page });
            if (this.pickerSearch) params.set('q', this.pickerSearch);
            try {
                const res = await fetch(`{{ route('admin.media.list') }}?` + params, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                this.pickerImages = append ? [...this.pickerImages, ...json.data] : json.data;
                this.pickerHasMore = json.has_more;
            } finally {
                this.pickerLoading = false;
            }
        },

        pickerSelect(img) {
            this.pickerSelected = img.id;
            this.pickerSelectedImg = img;
        },

        confirmPicker() {
            if (!this.pickerSelectedImg) return;
            this.bannerPreview = this.pickerSelectedImg.url;
            this.mediaPath     = this.pickerSelectedImg.file_name;
            this.removeImage   = false;
            this.$refs.bannerInput.value = '';
            this.pickerModal.hide();
        },

        previewUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                this.bannerPreview = e.target.result;
                this.mediaPath = '';
                this.removeImage = false;
            };
            reader.readAsDataURL(file);
        },

        clearBanner() {
            this.bannerPreview = null;
            this.mediaPath = '';
            this.removeImage = true;
            this.$refs.bannerInput.value = '';
        }
    }
}
</script>
@endpush
