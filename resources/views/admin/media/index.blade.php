@extends('admin.layouts.admin')

@section('title', 'Media Library')

@section('breadcrumb')
    <li class="breadcrumb-item active">Media Library</li>
@endsection

@section('page-title', 'Media Library')
@section('page-subtitle', 'Upload and manage media files')

@push('styles')
<style>
    .upload-zone {
        border: 3px dashed #dee2e6;
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: #fafafa;
    }
    .upload-zone:hover,
    .upload-zone.drag-over {
        border-color: #0d6efd;
        background: rgba(13,110,253,.04);
    }
    .upload-zone.drag-over .upload-icon { transform: scale(1.1); }
    .upload-icon { transition: transform .2s; }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1rem;
    }

    .media-item {
        position: relative;
        border-radius: .5rem;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
        transition: box-shadow .15s, border-color .15s;
        cursor: pointer;
    }
    .media-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,.12); border-color: #0d6efd; }
    .media-item.selected { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.25); }

    .media-thumb {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }
    .media-thumb-icon {
        width: 100%;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        font-size: 2.5rem;
    }

    .media-info {
        padding: .5rem;
        border-top: 1px solid #f0f0f0;
    }
    .media-info .media-name {
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .media-info .media-meta {
        font-size: .65rem;
        color: #6c757d;
    }

    .media-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,.55);
        display: none;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        flex-direction: column;
    }
    .media-item:hover .media-overlay { display: flex; }

    .upload-progress {
        display: none;
        margin-top: 1rem;
    }

</style>
@endpush

@section('content')

{{-- ── Upload Area ── --}}
<div class="card border-0 shadow-sm mb-4" x-data="mediaUploader()">
    <div class="card-body">
        <div class="upload-zone"
             @click="$refs.fileInput.click()"
             @dragover.prevent="isDragging=true"
             @dragleave.prevent="isDragging=false"
             @drop.prevent="isDragging=false; handleDrop($event)"
             :class="{ 'drag-over': isDragging }">
            <div class="upload-icon mb-3">
                <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
            </div>
            <h5 class="fw-bold mb-1">Drag &amp; drop files here</h5>
            <p class="text-muted mb-3">or click to browse</p>
            <span class="badge bg-light text-secondary">JPG, PNG, GIF, WebP, PDF, MP4 — Max 10MB each</span>
        </div>
        <div style="display:none;position:absolute;">
            <input type="file" x-ref="fileInput" multiple accept="image/*,application/pdf,video/mp4"
                   @change="handleFiles($event.target.files)">
        </div>

        {{-- Upload Queue --}}
        <div x-show="uploads.length" x-transition class="mt-3">
            <h6 class="small fw-semibold mb-2">Uploading...</h6>
            <template x-for="(file, i) in uploads" :key="i">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="small text-truncate flex-grow-1" x-text="file.name" style="max-width:200px;"></span>
                    <div class="progress flex-grow-1" style="height:8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             :style="'width:' + file.progress + '%'"></div>
                    </div>
                    <span class="small text-muted" style="width:36px;" x-text="file.progress + '%'"></span>
                    <i class="fas fa-check-circle text-success" x-show="file.progress >= 100"></i>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- ── Filter & Grid ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h6 class="fw-bold mb-0">Media Files</h6>
            <span class="text-muted small">{{ $media->total() ?? 0 }} files</span>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="type" class="form-select form-select-sm" style="width:130px;"
                        onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="image"    {{ request('type') === 'image'    ? 'selected' : '' }}>Images</option>
                    <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents</option>
                    <option value="video"    {{ request('type') === 'video'    ? 'selected' : '' }}>Videos</option>
                </select>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control" style="width:160px;"
                           placeholder="Search..." value="{{ request('q') }}">
                    <button type="submit" class="btn btn-primary btn-sm">Go</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body">
        @if(isset($media) && $media->count())
        <div class="media-grid">
            @foreach($media as $file)
            <div class="media-item" id="media-{{ $file->id }}">
                {{-- Thumbnail --}}
                @if(Str::startsWith($file->mime_type ?? '', 'image/'))
                    <img src="{{ $file->url }}"
                         alt="{{ $file->name }}" class="media-thumb">
                @elseif(Str::startsWith($file->mime_type ?? '', 'video/'))
                    <div class="media-thumb-icon text-secondary">
                        <i class="fas fa-film"></i>
                    </div>
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
                    <button type="button" class="btn btn-sm btn-light" title="Copy URL"
                            onclick="copyUrl('{{ $file->url }}')">
                        <i class="fas fa-link"></i> Copy URL
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                            onclick="deleteMedia({{ $file->id }})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>

                {{-- Info --}}
                <div class="media-info">
                    <div class="media-name" title="{{ $file->name }}">
                        {{ $file->name }}
                    </div>
                    <div class="media-meta d-flex justify-content-between">
                        <span>{{ $file->human_size ?? number_format(($file->size ?? 0)/1024, 1) . ' KB' }}</span>
                        <span>{{ $file->created_at->format('M d') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-images fa-3x mb-3 d-block"></i>
            <p>No media files yet. Upload some above!</p>
        </div>
        @endif
    </div>

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
    function mediaUploader() {
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
                // Reload after all done
                setTimeout(() => location.reload(), 800);
            },

            uploadFile(file, entry) {
                return new Promise(resolve => {
                    const formData = new FormData();
                    formData.append('files[]', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("admin.media.upload") }}');

                    xhr.upload.onprogress = e => {
                        if (e.lengthComputable)
                            entry.progress = Math.round((e.loaded / e.total) * 100);
                    };

                    xhr.onload = () => {
                        entry.progress = 100;
                        resolve();
                    };
                    xhr.onerror = resolve;
                    xhr.send(formData);
                });
            }
        }
    }

    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({ icon: 'success', title: 'Copied!', text: url, timer: 2000, showConfirmButton: false });
        });
    }

    function deleteMedia(id) {
        Swal.fire({
            title: 'Delete this file?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete',
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteMediaForm');
                form.action = '{{ url("admin/media") }}/' + id;
                form.submit();
            }
        });
    }
</script>
@endpush
