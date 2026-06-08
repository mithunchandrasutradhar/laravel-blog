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
                            <select name="type" id="type"
                                    class="form-select @error('type') is-invalid @enderror"
                                    x-model="adType" required>
                                <option value="adsense" {{ old('type', $advertisement->type) === 'adsense' ? 'selected' : '' }}>
                                    Google AdSense
                                </option>
                                <option value="banner" {{ old('type', $advertisement->type) === 'banner' ? 'selected' : '' }}>
                                    Banner Image
                                </option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="position" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                            <select name="position" id="position"
                                    class="form-select @error('position') is-invalid @enderror" required>
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
                            <label for="ad_code" class="form-label fw-semibold">AdSense Code</label>
                            <textarea name="ad_code" id="ad_code"
                                      class="form-control font-monospace @error('ad_code') is-invalid @enderror"
                                      rows="8">{{ old('ad_code', $advertisement->ad_code) }}</textarea>
                            @error('ad_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Banner --}}
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
                                        <span class="small">Click to change banner</span>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="banner_image" class="d-none" accept="image/*"
                                   x-ref="bannerInput" @change="previewBanner($event)">
                            <input type="hidden" name="remove_banner" x-bind:value="removeBanner ? '1' : ''">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        @click="$refs.bannerInput.click()">
                                    <i class="fas fa-upload me-1"></i>Change
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" x-show="bannerPreview"
                                        @click="bannerPreview=null; removeBanner=true; $refs.bannerInput.value=''">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="banner_url" class="form-label fw-semibold">Click URL</label>
                            <input type="url" name="banner_url" id="banner_url"
                                   class="form-control @error('banner_url') is-invalid @enderror"
                                   value="{{ old('banner_url', $advertisement->banner_url) }}">
                            @error('banner_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="alt_text" class="form-label fw-semibold">Alt Text</label>
                            <input type="text" name="alt_text" id="alt_text"
                                   class="form-control"
                                   value="{{ old('alt_text', $advertisement->alt_text) }}">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                   value="{{ old('start_date', $advertisement->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                   value="{{ old('end_date', $advertisement->end_date?->format('Y-m-d')) }}">
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
                               value="1" {{ old('is_active', $advertisement->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="open_in_new_tab" id="open_in_new_tab"
                               value="1" {{ old('open_in_new_tab', $advertisement->open_in_new_tab ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="open_in_new_tab">Open in New Tab</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Impressions</span>
                            <strong>{{ number_format($advertisement->impressions ?? 0) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">Clicks</span>
                            <strong>{{ number_format($advertisement->clicks ?? 0) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between small py-2 px-3">
                            <span class="text-muted">CTR</span>
                            <strong>
                                {{ $advertisement->impressions > 0
                                    ? number_format(($advertisement->clicks / $advertisement->impressions) * 100, 2)
                                    : '0.00' }}%
                            </strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Update Advertisement
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
    function adEditForm() {
        return {
            adType: '{{ old("type", $advertisement->type ?? "") }}',
            bannerPreview: {{ $advertisement->banner_image ? '"' . asset('storage/' . $advertisement->banner_image) . '"' : 'null' }},
            removeBanner: false,

            previewBanner(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => {
                    this.bannerPreview = e.target.result;
                    this.removeBanner = false;
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
