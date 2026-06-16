@extends('author.layouts.author')

@section('title', 'My Media')
@section('page-title', 'Media Library')
@section('page-subtitle', 'Upload and manage images for your posts')

@push('styles')
<style>
    /* ── Upload zone ─────────────────────────────────── */
    .upload-zone {
        border: 2px dashed #dee2e6; border-radius: .875rem;
        padding: 2rem 1.5rem; text-align: center;
        cursor: pointer; transition: all .2s; background: #fafafa;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: #0d6efd; background: rgba(13,110,253,.03);
    }

    /* ── Media grid ─────────────────────────────────── */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
        gap: .875rem;
    }
    .media-item {
        position: relative; border-radius: .5rem; overflow: hidden;
        border: 1px solid #dee2e6; background: #fff;
        transition: box-shadow .15s, border-color .15s;
    }
    .media-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); border-color: #0d6efd; }
    .media-thumb { width: 100%; height: 110px; object-fit: cover; display: block; }
    .media-thumb-icon {
        width: 100%; height: 110px;
        display: flex; align-items: center; justify-content: center;
        background: #f8f9fa; font-size: 2rem;
    }
    .media-info { padding: .45rem .5rem; border-top: 1px solid #f0f0f0; }
    .media-name { font-size: .7rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .media-meta { font-size: .62rem; color: #6c757d; }
    .media-overlay {
        position: absolute; inset: 0; background: rgba(0,0,0,.55);
        display: none; align-items: center; justify-content: center;
        gap: .35rem; padding: .35rem;
    }
    .media-item:hover .media-overlay { display: flex; }
    .media-action-btn {
        width: 32px; height: 32px; padding: 0;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }

    /* ── Bulk selection ──────────────────────────────── */
    .media-checkbox-wrap {
        position: absolute; top: 6px; left: 6px; z-index: 10;
        opacity: 0; transition: opacity .15s;
    }
    .media-item:hover .media-checkbox-wrap,
    .media-item.selected .media-checkbox-wrap { opacity: 1; }
    .media-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #0d6efd; }
    .media-item.selected { border-color: #0d6efd !important; box-shadow: 0 0 0 2px rgba(13,110,253,.3) !important; }

    /* ── Bulk action bar ─────────────────────────────── */
    .bulk-bar {
        display: none; align-items: center; gap: .5rem; flex-wrap: wrap;
        background: #e7f1ff; border: 1px solid #b8d4f9;
        border-radius: .5rem; padding: .5rem .75rem; margin-bottom: .75rem;
    }
    .bulk-bar.active { display: flex; }
</style>
@endpush

@section('content')

{{-- Hidden delete form --}}
<form id="deleteMediaForm" method="POST" action="" class="d-none">@csrf @method('DELETE')</form>

{{-- ── Upload Card ── --}}
<div class="card border-0 shadow-sm mb-3" x-data="authorMediaUploader()">
    <div class="card-body">

        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fas fa-folder-open text-warning"></i>
            <span class="fw-semibold small">
                Posts Folder
                <span class="text-muted fw-normal">— all uploads go here automatically</span>
            </span>
        </div>

        <div class="upload-zone"
             @click="$refs.fileInput.click()"
             @dragover.prevent="isDragging=true"
             @dragleave.prevent="isDragging=false"
             @drop.prevent="isDragging=false; handleDrop($event)"
             :class="{ 'drag-over': isDragging }">
            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2 d-block"></i>
            <p class="fw-semibold mb-1">Drag &amp; drop images here</p>
            <p class="text-muted small mb-0">or click to browse — JPG, PNG, GIF, WebP · Max 5 MB</p>
        </div>

        <div style="display:none;position:absolute;">
            <input type="file" x-ref="fileInput" multiple accept="image/*"
                   @change="handleFiles($event.target.files)">
        </div>

        <div x-show="uploads.length" x-transition class="mt-3">
            <h6 class="small fw-semibold mb-2">Uploading...</h6>
            <template x-for="(file, i) in uploads" :key="i">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="small text-truncate flex-grow-1" x-text="file.name" style="max-width:200px;"></span>
                    <div class="progress flex-grow-1" style="height:7px;">
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

{{-- ── Filter bar ── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small">
                <strong>{{ $media->total() }}</strong> file(s) in Posts folder
            </span>
            <div class="ms-auto d-flex gap-2">
                <select name="type" class="form-select form-select-sm" style="width:120px;"
                        onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="image"    {{ request('type') === 'image'    ? 'selected' : '' }}>Images</option>
                    <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents</option>
                </select>
                <div class="input-group input-group-sm" style="width:210px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control"
                           placeholder="Search..." value="{{ request('q') }}">
                    <button type="submit" class="btn btn-primary btn-sm">Go</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Media Grid ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- Bulk bar --}}
        <div class="bulk-bar" id="bulkBar">
            <span class="small fw-semibold text-primary" id="bulkCount">0 selected</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllVisible()">Select All</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect</button>
            <div class="vr mx-1"></div>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                <i class="fas fa-trash me-1"></i>Delete Selected
            </button>
        </div>

        @if($media->count())
        <div class="media-grid">
            @foreach($media as $file)
            <div class="media-item" id="media-{{ $file->id }}">

                <div class="media-checkbox-wrap">
                    <input type="checkbox" class="media-checkbox" data-id="{{ $file->id }}"
                           onchange="toggleSelect({{ $file->id }}, this)"
                           onclick="event.stopPropagation()">
                </div>

                @if($file->isImage())
                    <img src="{{ $file->url }}" alt="{{ $file->name }}" class="media-thumb">
                @elseif($file->mime_type === 'application/pdf')
                    <div class="media-thumb-icon text-danger"><i class="fas fa-file-pdf"></i></div>
                @else
                    <div class="media-thumb-icon text-muted"><i class="fas fa-file"></i></div>
                @endif

                <div class="media-overlay">
                    <button type="button" class="btn btn-sm btn-light media-action-btn"
                            title="Copy URL" onclick="copyUrl('{{ $file->url }}')">
                        <i class="fas fa-link"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-info media-action-btn"
                            title="Rename"
                            onclick="openRename({{ $file->id }}, '{{ addslashes($file->name) }}')">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger media-action-btn"
                            title="Delete" onclick="deleteMedia({{ $file->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <div class="media-info">
                    <div class="media-name" title="{{ $file->name }}">{{ $file->name }}</div>
                    <div class="media-meta d-flex justify-content-between">
                        <span>{{ $file->human_size }}</span>
                        <span>{{ $file->created_at->format('M d') }}</span>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-images fa-3x mb-3 d-block opacity-25"></i>
            <p class="mb-0">No files yet. Upload some above!</p>
        </div>
        @endif

    </div>

    @if($media->hasPages())
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $media->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ── Rename Modal ── --}}
<div class="modal fade" id="renameModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold"><i class="fas fa-pencil-alt me-2"></i>Rename File</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pb-2">
                <input type="text" id="renameInput" class="form-control form-control-sm"
                       placeholder="File display name">
            </div>
            <div class="modal-footer pt-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="executeRename()">Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── Upload ──────────────────────────────────────────────────────────────────
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
                setTimeout(() => location.reload(), 600);
            },

            uploadFile(file, entry) {
                return new Promise(resolve => {
                    const formData = new FormData();
                    formData.append('files[]', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("author.media.upload") }}');
                    xhr.upload.onprogress = e => {
                        if (e.lengthComputable)
                            entry.progress = Math.round((e.loaded / e.total) * 100);
                    };
                    xhr.onload = () => { entry.progress = 100; resolve(); };
                    xhr.onerror = resolve;
                    xhr.send(formData);
                });
            }
        };
    }
    window.authorMediaUploader = authorMediaUploader;

    // ── Copy URL ────────────────────────────────────────────────────────────────
    window.copyUrl = function (url) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({ icon: 'success', title: 'Copied!', text: url, timer: 1800, showConfirmButton: false });
        });
    };

    // ── Delete ──────────────────────────────────────────────────────────────────
    window.deleteMedia = function (id) {
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
                form.action = '{{ url("author/media") }}/' + id;
                form.submit();
            }
        });
    };

    // ── Rename ──────────────────────────────────────────────────────────────────
    var _renameId = null;

    window.openRename = function (id, name) {
        _renameId = id;
        document.getElementById('renameInput').value = name;
        new bootstrap.Modal(document.getElementById('renameModal')).show();
        setTimeout(() => document.getElementById('renameInput').select(), 300);
    };

    window.executeRename = function () {
        const name = document.getElementById('renameInput').value.trim();
        if (!name) return;

        fetch('{{ url("author/media") }}/' + _renameId + '/rename', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const el = document.querySelector('#media-' + _renameId + ' .media-name');
                if (el) { el.textContent = data.name; el.title = data.name; }
                bootstrap.Modal.getInstance(document.getElementById('renameModal')).hide();
                Swal.fire({ icon: 'success', title: 'Renamed!', timer: 1200, showConfirmButton: false });
            } else {
                alert(data.message || 'Could not rename file.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    };

    document.addEventListener('DOMContentLoaded', function () {
        const inp = document.getElementById('renameInput');
        if (inp) inp.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); executeRename(); }
        });
    });

    // ── Bulk Selection ──────────────────────────────────────────────────────────
    var _selectedIds = new Set();

    window.toggleSelect = function (id, checkbox) {
        if (checkbox.checked) {
            _selectedIds.add(id);
            document.getElementById('media-' + id).classList.add('selected');
        } else {
            _selectedIds.delete(id);
            document.getElementById('media-' + id).classList.remove('selected');
        }
        updateBulkBar();
    };

    window.selectAllVisible = function () {
        document.querySelectorAll('.media-checkbox').forEach(cb => {
            cb.checked = true;
            _selectedIds.add(Number(cb.dataset.id));
            cb.closest('.media-item').classList.add('selected');
        });
        updateBulkBar();
    };

    window.deselectAll = function () {
        document.querySelectorAll('.media-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('.media-item').classList.remove('selected');
        });
        _selectedIds.clear();
        updateBulkBar();
    };

    function updateBulkBar() {
        const bar = document.getElementById('bulkBar');
        document.getElementById('bulkCount').textContent = _selectedIds.size + ' selected';
        bar.classList.toggle('active', _selectedIds.size > 0);
    }

    // ── Bulk Delete ─────────────────────────────────────────────────────────────
    window.bulkDelete = function () {
        if (_selectedIds.size === 0) return;
        const n = _selectedIds.size;
        Swal.fire({
            title: 'Delete ' + n + ' file(s)?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete All',
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch('{{ url("author/media/bulk-delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: Array.from(_selectedIds) }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Deleted ' + data.count + ' file(s)!', timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    alert(data.message || 'Delete failed.');
                }
            })
            .catch(() => alert('Network error. Please try again.'));
        });
    };

}());
</script>
@endpush
