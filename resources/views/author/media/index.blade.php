@extends('author.layouts.author')

@section('title', 'My Media')

@section('page-title', 'My Media')
@section('page-subtitle', 'Upload and manage your media files')

@push('styles')
<style>
    /* ── Upload zone ── */
    .upload-zone {
        border: 3px dashed #dee2e6;
        border-radius: 1rem;
        padding: 2.5rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: #fafafa;
        position: relative;
    }
    .upload-zone:hover,
    .upload-zone.drag-over {
        border-color: #0d6efd;
        background: rgba(13,110,253,.04);
    }
    .upload-zone.drag-over .upload-icon { transform: scale(1.1); }
    .upload-icon { transition: transform .2s; }

    /* ── Media grid ── */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        gap: 1rem;
    }

    .media-item {
        position: relative;
        border-radius: .5rem;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
        transition: box-shadow .15s, border-color .15s;
    }
    .media-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
        border-color: #0d6efd;
    }

    .media-thumb {
        width: 100%;
        height: 115px;
        object-fit: cover;
        display: block;
    }
    .media-thumb-icon {
        width: 100%;
        height: 115px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        font-size: 2.5rem;
    }

    .media-info {
        padding: .45rem .5rem;
        border-top: 1px solid #f0f0f0;
    }
    .media-name {
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .media-meta {
        font-size: .65rem;
        color: #6c757d;
    }

    .media-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,.58);
        display: none;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        flex-direction: column;
        padding: .5rem;
    }
    .media-item:hover .media-overlay { display: flex; }

    #mediaFileInput { display: none; }

    /* ── Filter tabs ── */
    .filter-tab {
        cursor: pointer;
        padding: .35rem .85rem;
        border-radius: 20px;
        font-size: .82rem;
        font-weight: 500;
        color: #6c757d;
        text-decoration: none;
        transition: background .15s, color .15s;
    }
    .filter-tab:hover { background: #f0f0f0; color: #212529; }
    .filter-tab.active { background: #0d6efd; color: #fff; }
</style>
@endpush

@section('content')

{{-- ── Upload Area ── --}}
<div class="card border-0 shadow-sm mb-4" x-data="authorMediaUploader()">
    <div class="card-header bg-transparent border-0 py-3">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-cloud-upload-alt text-primary me-2"></i>Upload Files
        </h6>
    </div>
    <div class="card-body pt-0">

        {{-- Drag-drop zone --}}
        <div class="upload-zone"
             @click="$refs.fileInput.click()"
             @dragover.prevent="isDragging = true"
             @dragleave.prevent="isDragging = false"
             @drop.prevent="handleDrop($event)"
             :class="{ 'drag-over': isDragging }">
            <div class="upload-icon mb-3">
                <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
            </div>
            <h6 class="fw-bold mb-1">Drag &amp; drop files here</h6>
            <p class="text-muted small mb-3">or click to browse from your computer</p>
            <span class="badge bg-light text-secondary border" style="font-size:.75rem;">
                JPG &bull; PNG &bull; GIF &bull; WebP &bull; PDF &mdash; Max 10 MB each
            </span>
        </div>

        <input type="file"
               id="mediaFileInput"
               x-ref="fileInput"
               multiple
               accept="image/*,application/pdf"
               @change="handleFiles($event.target.files)">

        {{-- Upload queue / progress --}}
        <div x-show="uploads.length" x-transition class="mt-3">
            <h6 class="small fw-semibold mb-2 text-muted">Uploading...</h6>
            <template x-for="(file, idx) in uploads" :key="idx">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-file text-muted flex-shrink-0" style="font-size:.85rem;"></i>
                    <span class="small text-truncate" x-text="file.name" style="max-width:200px; flex-shrink:0;"></span>
                    <div class="progress flex-grow-1" style="height:7px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             :style="'width:' + file.progress + '%'"></div>
                    </div>
                    <span class="small text-muted" style="width:36px;text-align:right;" x-text="file.progress + '%'"></span>
                    <i class="fas fa-check-circle text-success" x-show="file.progress >= 100" style="font-size:.9rem;"></i>
                </div>
            </template>
        </div>

    </div>
</div>

{{-- ── Filter Tabs & Grid ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">

        {{-- Filter tabs --}}
        <div class="d-flex align-items-center gap-1">
            <a href="{{ route('author.media.index') }}"
               class="filter-tab {{ !request('type') ? 'active' : '' }}">
                <i class="fas fa-th me-1"></i>All
            </a>
            <a href="{{ route('author.media.index', ['type' => 'image']) }}"
               class="filter-tab {{ request('type') === 'image' ? 'active' : '' }}">
                <i class="fas fa-image me-1"></i>Images
            </a>
            <a href="{{ route('author.media.index', ['type' => 'document']) }}"
               class="filter-tab {{ request('type') === 'document' ? 'active' : '' }}">
                <i class="fas fa-file-pdf me-1"></i>Documents
            </a>
        </div>

        {{-- File count --}}
        <span class="text-muted small">
            {{ $media->total() ?? 0 }} file{{ ($media->total() ?? 0) !== 1 ? 's' : '' }}
        </span>

    </div>

    <div class="card-body">
        @if(isset($media) && $media->count())
        <div class="media-grid">
            @foreach($media as $file)
            <div class="media-item" id="media-{{ $file->id }}">

                {{-- Thumbnail or icon --}}
                @if(Str::startsWith($file->mime_type ?? '', 'image/'))
                    <img src="{{ asset('storage/' . $file->path) }}"
                         alt="{{ $file->original_name }}"
                         class="media-thumb"
                         loading="lazy">
                @elseif($file->mime_type === 'application/pdf')
                    <div class="media-thumb-icon text-danger">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                @else
                    <div class="media-thumb-icon text-muted">
                        <i class="fas fa-file"></i>
                    </div>
                @endif

                {{-- Hover overlay --}}
                <div class="media-overlay">
                    <button type="button"
                            class="btn btn-sm btn-light w-100"
                            style="font-size:.75rem;"
                            onclick="copyMediaUrl('{{ asset('storage/' . $file->path) }}')">
                        <i class="fas fa-link me-1"></i>Copy URL
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-danger w-100"
                            style="font-size:.75rem;"
                            onclick="deleteMedia({{ $file->id }}, '{{ addslashes($file->original_name) }}')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>

                {{-- Info --}}
                <div class="media-info">
                    <div class="media-name" title="{{ $file->original_name }}">
                        {{ $file->original_name }}
                    </div>
                    <div class="media-meta d-flex justify-content-between mt-1">
                        <span>{{ $file->human_size ?? number_format(($file->size ?? 0) / 1024, 1) . ' KB' }}</span>
                        <span>{{ $file->created_at->format('M d') }}</span>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-images fa-3x mb-3 d-block opacity-50"></i>
            <p class="mb-1">No media files yet.</p>
            <p class="small">Use the upload area above to add images or documents.</p>
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if(isset($media) && $media->hasPages())
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $media->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Hidden delete form --}}
<form id="deleteMediaForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // ── Alpine uploader component ──
    function authorMediaUploader() {
        return {
            isDragging: false,
            uploads: [],

            handleDrop(event) {
                this.isDragging = false;
                this.handleFiles(event.dataTransfer.files);
            },

            async handleFiles(files) {
                for (const file of Array.from(files)) {
                    const entry = { name: file.name, progress: 0 };
                    this.uploads.push(entry);
                    await this.uploadFile(file, entry);
                }
                // Reload page after all uploads finish
                setTimeout(() => location.reload(), 900);
            },

            uploadFile(file, entry) {
                return new Promise(resolve => {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("author.media.store") }}');

                    xhr.upload.onprogress = e => {
                        if (e.lengthComputable)
                            entry.progress = Math.round((e.loaded / e.total) * 100);
                    };

                    xhr.onload = () => { entry.progress = 100; resolve(); };
                    xhr.onerror = resolve;

                    xhr.send(formData);
                });
            }
        }
    }

    // ── Copy URL to clipboard ──
    function copyMediaUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'URL Copied!',
                text: url,
                timer: 2500,
                showConfirmButton: false,
                toast: true,
                position: 'bottom-end',
            });
        }).catch(() => {
            prompt('Copy this URL:', url);
        });
    }

    // ── Delete with SweetAlert2 confirm ──
    function deleteMedia(id, name) {
        Swal.fire({
            title: 'Delete this file?',
            html: '<span class="small text-muted">' + name + '</span><br>This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i>Delete',
            cancelButtonText: 'Cancel',
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteMediaForm');
                form.action = '{{ url("author/media") }}/' + id;
                form.submit();
            }
        });
    }
</script>
@endpush
