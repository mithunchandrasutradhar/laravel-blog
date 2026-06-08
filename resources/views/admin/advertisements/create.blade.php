@extends('admin.layouts.admin')

@section('title', 'Add Advertisement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.advertisements.index') }}" class="text-decoration-none">Advertisements</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('page-title', 'Add New Advertisement')

@push('styles')
<style>
    .image-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s;
        overflow: hidden;
    }
    .image-preview-box:hover { border-color: #0d6efd; }
    .image-preview-box img { width: 100%; max-height: 200px; object-fit: contain; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.advertisements.store') }}" enctype="multipart/form-data"
      x-data="adForm()">
    @csrf

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
                               placeholder="e.g. Sidebar Banner Q1" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" id="type"
                                    class="form-select @error('type') is-invalid @enderror"
                                    x-model="adType" required>
                                <option value="">Select type...</option>
                                <option value="adsense" {{ old('type') === 'adsense' ? 'selected' : '' }}>
                                    Google AdSense
                                </option>
                                <option value="banner"  {{ old('type') === 'banner'  ? 'selected' : '' }}>
                                    Banner Image
                                </option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="position" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                            <select name="position" id="position"
                                    class="form-select @error('position') is-invalid @enderror" required>
                                <option value="">Select position...</option>
                                <option value="header"     {{ old('position') === 'header'     ? 'selected' : '' }}>Header</option>
                                <option value="sidebar"    {{ old('position') === 'sidebar'    ? 'selected' : '' }}>Sidebar</option>
                                <option value="in-article" {{ old('position') === 'in-article' ? 'selected' : '' }}>In-Article</option>
                                <option value="footer"     {{ old('position') === 'footer'     ? 'selected' : '' }}>Footer</option>
                            </select>
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- AdSense Code --}}
                    <div x-show="adType === 'adsense'" x-transition>
                        <div class="mb-3">
                            <label for="ad_code" class="form-label fw-semibold">
                                AdSense Code <span class="text-danger">*</span>
                            </label>
                            <textarea name="ad_code" id="ad_code"
                                      class="form-control font-monospace @error('ad_code') is-invalid @enderror"
                                      rows="8" placeholder="Paste your AdSense &lt;script&gt; code here...">{{ old('ad_code') }}</textarea>
                            @error('ad_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Paste the full AdSense snippet from your Google AdSense account.</div>
                        </div>
                    </div>

                    {{-- Banner Image + URL --}}
                    <div x-show="adType === 'banner'" x-transition>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Banner Image</label>
                            <div class="image-preview-box mb-2" @click="$refs.bannerInput.click()">
                                <template x-if="bannerPreview">
                                    <img :src="bannerPreview" alt="Banner Preview">
                                </template>
                                <template x-if="!bannerPreview">
                                    <div class="text-center text-muted p-3">
                                        <i class="fas fa-image fa-2x mb-2 d-block"></i>
                                        <span class="small">Click to upload banner</span>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="banner_image" class="d-none" accept="image/*"
                                   x-ref="bannerInput" @change="previewBanner($event)">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        @click="$refs.bannerInput.click()">
                                    <i class="fas fa-upload me-1"></i>Upload Image
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" x-show="bannerPreview"
                                        @click="bannerPreview=null; $refs.bannerInput.value=''">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="banner_url" class="form-label fw-semibold">Click URL</label>
                            <input type="url" name="banner_url" id="banner_url"
                                   class="form-control @error('banner_url') is-invalid @enderror"
                                   placeholder="https://advertiser-website.com"
                                   value="{{ old('banner_url') }}">
                            @error('banner_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="alt_text" class="form-label fw-semibold">Alt Text</label>
                            <input type="text" name="alt_text" id="alt_text"
                                   class="form-control" placeholder="Accessible description..."
                                   value="{{ old('alt_text') }}">
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" id="start_date"
                                   class="form-control" value="{{ old('start_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" id="end_date"
                                   class="form-control" value="{{ old('end_date') }}">
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
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="open_in_new_tab" id="open_in_new_tab"
                               value="1" checked {{ old('open_in_new_tab', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="open_in_new_tab">Open in New Tab</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Create Advertisement
                    </button>
                    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-outline-secondary w-100 btn-sm">
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
    function adForm() {
        return {
            adType: '{{ old("type", "") }}',
            bannerPreview: null,

            previewBanner(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => this.bannerPreview = e.target.result;
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
