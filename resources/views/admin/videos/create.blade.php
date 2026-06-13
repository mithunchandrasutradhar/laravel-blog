@extends('admin.layouts.admin')

@section('title', 'Add Video')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.videos.index') }}">Videos</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('page-title', 'Add New Video')
@section('page-subtitle', 'Paste a YouTube link to embed it on the site')

@section('content')

<form action="{{ route('admin.videos.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        {{-- Main fields --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="e.g. Getting Started with Laravel" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">YouTube URL <span class="text-danger">*</span></label>
                        <input type="url" name="youtube_url" id="youtubeUrl"
                               class="form-control @error('youtube_url') is-invalid @enderror"
                               value="{{ old('youtube_url') }}"
                               placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/..."
                               required>
                        @error('youtube_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Supports youtube.com/watch?v=, youtu.be/, and youtube.com/embed/ formats.</div>
                    </div>

                    {{-- Live preview --}}
                    <div id="previewWrap" class="mb-4 d-none">
                        <label class="form-label fw-semibold">Preview</label>
                        <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark" style="max-width:480px;">
                            <iframe id="previewFrame" src="" allowfullscreen style="border:0;"></iframe>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Short description (optional)">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">Settings</div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">— No category —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', 0) }}" min="0" max="9999">
                        <div class="form-text">Lower numbers appear first.</div>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active"
                               id="isActive" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active (visible on site)</label>
                    </div>

                </div>
                <div class="card-footer bg-transparent d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-save me-1"></i>Save Video
                    </button>
                    <a href="{{ route('admin.videos.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const urlInput    = document.getElementById('youtubeUrl');
    const previewWrap = document.getElementById('previewWrap');
    const previewFrame = document.getElementById('previewFrame');

    function extractId(url) {
        let m;
        if ((m = url.match(/youtu\.be\/([a-zA-Z0-9_-]{11})/)))   return m[1];
        if ((m = url.match(/[?&]v=([a-zA-Z0-9_-]{11})/)))        return m[1];
        if ((m = url.match(/\/embed\/([a-zA-Z0-9_-]{11})/)))     return m[1];
        return null;
    }

    function updatePreview() {
        const id = extractId(urlInput.value.trim());
        if (id) {
            previewFrame.src = 'https://www.youtube.com/embed/' + id + '?rel=0';
            previewWrap.classList.remove('d-none');
        } else {
            previewWrap.classList.add('d-none');
            previewFrame.src = '';
        }
    }

    urlInput.addEventListener('input', updatePreview);
    updatePreview();
})();
</script>
@endpush

@endsection
