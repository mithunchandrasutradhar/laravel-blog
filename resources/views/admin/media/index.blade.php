@extends('admin.layouts.admin')

@section('title', 'Media Library')

@section('breadcrumb')
    <li class="breadcrumb-item active">Media Library</li>
@endsection

@section('page-title', 'Media Library')
@section('page-subtitle', 'Upload and manage media files')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────── */
    .media-layout { display: flex; gap: 1.25rem; align-items: flex-start; }
    .media-sidebar { width: 220px; flex-shrink: 0; }
    .media-main { flex: 1; min-width: 0; }

    /* ── Folder sidebar ─────────────────────────────── */
    .folder-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .45rem .75rem;
        border-radius: .5rem;
        text-decoration: none;
        color: #343a40;
        font-size: .875rem;
        transition: background .15s;
        cursor: pointer;
    }
    .folder-item:hover { background: #f1f3f5; color: #343a40; }
    .folder-item.active { background: #e7f1ff; color: #0d6efd; font-weight: 600; }
    .folder-item .folder-count {
        margin-left: auto;
        font-size: .7rem;
        background: #e9ecef;
        color: #6c757d;
        border-radius: 100px;
        padding: .1rem .45rem;
        flex-shrink: 0;
    }
    .folder-item.active .folder-count { background: #cfe2ff; color: #0d6efd; }
    .folder-actions { display: none; gap: .2rem; }
    .folder-item:hover .folder-actions { display: flex; }

    /* ── Upload zone ────────────────────────────────── */
    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: .875rem;
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: #fafafa;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: #0d6efd;
        background: rgba(13,110,253,.03);
    }

    /* ── Media grid ─────────────────────────────────── */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
        gap: .875rem;
    }
    .media-item {
        position: relative;
        border-radius: .5rem;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
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
        position: absolute; inset: 0;
        background: rgba(0,0,0,.55);
        display: none; align-items: center; justify-content: center;
        gap: .35rem; flex-direction: column; padding: .35rem;
    }
    .media-item:hover .media-overlay { display: flex; }
    .media-overlay-row { display: flex; gap: .35rem; }
    .media-action-btn { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: .75rem; }
    .cursor-pointer { cursor: pointer; }

    /* ── Rename input ───────────────────────────────── */
    .folder-rename-input {
        font-size: .875rem;
        padding: .2rem .4rem;
        border: 1px solid #0d6efd;
        border-radius: .3rem;
        outline: none;
        flex: 1;
        min-width: 0;
    }

    @media (max-width: 768px) {
        .media-layout { flex-direction: column; }
        .media-sidebar { width: 100%; }
    }
</style>
@endpush

@section('content')

{{-- Create Folder Modal --}}
<div class="modal fade" id="newFolderModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" action="{{ route('admin.media-folders.store') }}" class="modal-content">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">New Folder</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                       placeholder="Folder name..." autofocus required maxlength="100">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-folder-plus me-1"></i>Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Hidden delete folder form --}}
<form id="deleteFolderForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

{{-- Hidden delete media form --}}
<form id="deleteMediaForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

<div class="media-layout">

    {{-- ══ Sidebar ══ --}}
    <aside class="media-sidebar">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2">

                {{-- All Files --}}
                <a href="{{ route('admin.media.index') }}"
                   class="folder-item {{ !$currentFolder ? 'active' : '' }}">
                    <i class="fas fa-th-large" style="width:16px;text-align:center;"></i>
                    <span>All Files</span>
                    <span class="folder-count">{{ \App\Models\Media::count() }}</span>
                </a>

                <hr class="my-2">

                <div class="d-flex align-items-center justify-content-between px-1 mb-1">
                    <span class="text-muted" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Folders</span>
                    <button class="btn btn-link btn-sm p-0 text-primary" data-bs-toggle="modal" data-bs-target="#newFolderModal" title="New Folder">
                        <i class="fas fa-plus fa-sm"></i>
                    </button>
                </div>

                {{-- Folder list --}}
                @forelse($folders as $folder)
                <div class="folder-item {{ $currentFolder?->id === $folder->id ? 'active' : '' }}"
                     id="folder-row-{{ $folder->id }}">

                    {{-- Normal view --}}
                    <div id="folder-label-{{ $folder->id }}" class="d-flex align-items-center gap-2 flex-grow-1 min-w-0" style="min-width:0;">
                        <i class="fas fa-folder{{ $currentFolder?->id === $folder->id ? '-open' : '' }}"
                           style="width:16px;text-align:center;flex-shrink:0;"></i>
                        <a href="{{ route('admin.media.index', ['folder' => $folder->slug]) }}"
                           class="text-truncate text-decoration-none {{ $currentFolder?->id === $folder->id ? 'text-primary' : 'text-dark' }}"
                           style="flex:1;min-width:0;font-size:.875rem;">
                            {{ $folder->name }}
                        </a>
                        <span class="folder-count">{{ $folder->media_count }}</span>
                        <div class="folder-actions">
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted"
                                    title="Rename" onclick="startRename({{ $folder->id }}, '{{ addslashes($folder->name) }}')">
                                <i class="fas fa-pencil-alt fa-xs"></i>
                            </button>
                            <button type="button" class="btn btn-link btn-sm p-0 text-danger"
                                    title="Delete" onclick="deleteFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}', {{ $folder->media_count }})">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Rename input (hidden by default) --}}
                    <div id="folder-rename-{{ $folder->id }}" class="d-none d-flex align-items-center gap-1 flex-grow-1" style="min-width:0;">
                        <input type="text" class="folder-rename-input"
                               id="folder-rename-input-{{ $folder->id }}"
                               value="{{ $folder->name }}"
                               onkeydown="handleRenameKey(event, {{ $folder->id }})"
                               onblur="cancelRename({{ $folder->id }})">
                        <button type="button" class="btn btn-success btn-sm py-0 px-1"
                                onmousedown="saveRename(event, {{ $folder->id }})">
                            <i class="fas fa-check fa-xs"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1"
                                onmousedown="cancelRename({{ $folder->id }})">
                            <i class="fas fa-times fa-xs"></i>
                        </button>
                    </div>
                </div>
                @empty
                <p class="text-muted small text-center py-2 mb-0">No folders yet.</p>
                @endforelse

            </div>
        </div>
    </aside>

    {{-- ══ Main Content ══ --}}
    <div class="media-main">

        {{-- Upload Zone --}}
        <div class="card border-0 shadow-sm mb-3" x-data="mediaUploader()">
            <div class="card-body pb-3">

                {{-- Folder target indicator --}}
                @if($currentFolder)
                <div class="d-flex align-items-center gap-2 mb-3 px-1">
                    <i class="fas fa-folder-open text-primary"></i>
                    <span class="small fw-semibold">Uploading to: <span class="text-primary">{{ $currentFolder->name }}</span></span>
                </div>
                @else
                <div class="mb-3">
                    <label class="form-label small fw-semibold mb-1">Upload to folder</label>
                    <select id="upload-folder-id" class="form-select form-select-sm" style="max-width:220px;">
                        <option value="">— Uncategorized —</option>
                        @foreach($folders as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="upload-zone"
                     @click="$refs.fileInput.click()"
                     @dragover.prevent="isDragging=true"
                     @dragleave.prevent="isDragging=false"
                     @drop.prevent="isDragging=false; handleDrop($event)"
                     :class="{ 'drag-over': isDragging }">
                    <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2 d-block"></i>
                    <p class="fw-semibold mb-1">Drag &amp; drop files here</p>
                    <p class="text-muted small mb-0">or click to browse — JPG, PNG, GIF, WebP, PDF · Max 5 MB</p>
                </div>

                <div style="display:none;position:absolute;">
                    <input type="file" x-ref="fileInput" multiple accept="image/*,application/pdf"
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

        {{-- Filter bar --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                    @if($currentFolder)
                    <input type="hidden" name="folder" value="{{ $currentFolder->slug }}">
                    @endif
                    <span class="text-muted small me-1">
                        {{ $currentFolder ? $currentFolder->name : 'All Files' }} — <strong>{{ $media->total() }}</strong> file(s)
                    </span>
                    <div class="ms-auto d-flex gap-2">
                        <select name="type" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="image"    {{ request('type') === 'image'    ? 'selected' : '' }}>Images</option>
                            <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents</option>
                        </select>
                        <div class="input-group input-group-sm" style="width:210px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Search..." value="{{ request('q') }}">
                            <button type="submit" class="btn btn-primary btn-sm">Go</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Media grid --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($media->count())
                <div class="media-grid">
                    @foreach($media as $file)
                    <div class="media-item" id="media-{{ $file->id }}">
                        @if(Str::startsWith($file->mime_type ?? '', 'image/'))
                            <img src="{{ $file->url }}" alt="{{ $file->name }}" class="media-thumb">
                        @elseif($file->mime_type === 'application/pdf')
                            <div class="media-thumb-icon text-danger"><i class="fas fa-file-pdf"></i></div>
                        @else
                            <div class="media-thumb-icon text-muted"><i class="fas fa-file"></i></div>
                        @endif

                        <div class="media-overlay">
                            <div class="media-overlay-row">
                                <button type="button" class="btn btn-sm btn-light media-action-btn" title="Copy URL" onclick="copyUrl('{{ $file->url }}')">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info media-action-btn" title="Rename" onclick="openFileRename({{ $file->id }}, '{{ addslashes($file->name) }}')">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary media-action-btn" title="Move to folder" onclick="openFileMoveCopy({{ $file->id }}, 'move')">
                                    <i class="fas fa-folder-open"></i>
                                </button>
                            </div>
                            <div class="media-overlay-row">
                                <button type="button" class="btn btn-sm btn-success media-action-btn" title="Copy to folder" onclick="openFileMoveCopy({{ $file->id }}, 'copy')">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger media-action-btn" title="Delete" onclick="deleteMedia({{ $file->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
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
                    <p>No files here yet. Upload some above!</p>
                </div>
                @endif
            </div>

            @if($media->hasPages())
            <div class="card-footer bg-transparent border-0 py-3">
                {{ $media->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>

    </div>{{-- end .media-main --}}
</div>{{-- end .media-layout --}}

{{-- ── File Rename Modal ──────────────────────────────────────────────────────── --}}
<div class="modal fade" id="fileRenameModal" tabindex="-1" aria-labelledby="fileRenameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold" id="fileRenameModalLabel"><i class="fas fa-pencil-alt me-2"></i>Rename File</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pb-2">
                <input type="text" id="fileRenameInput" class="form-control form-control-sm" placeholder="File display name">
            </div>
            <div class="modal-footer pt-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="executeFileRename()">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- ── File Move / Copy Modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="fileMoveCopyModal" tabindex="-1" aria-labelledby="fileMoveCopyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold" id="fileMoveCopyModalLabel"></h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3" id="fileMoveCopyDesc"></p>
                <div class="list-group list-group-flush" style="max-height:300px; overflow-y:auto;">
                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 cursor-pointer">
                        <input type="radio" name="moveCopyFolder" value="" class="form-check-input mt-0">
                        <i class="fas fa-inbox text-muted" style="width:16px;"></i>
                        <span class="small">Uncategorized</span>
                    </label>
                    @foreach($folders as $f)
                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 cursor-pointer">
                        <input type="radio" name="moveCopyFolder" value="{{ $f->id }}" class="form-check-input mt-0">
                        <i class="fas fa-folder text-warning" style="width:16px;"></i>
                        <span class="small">{{ $f->name }}</span>
                        <span class="badge bg-light text-muted ms-auto">{{ $f->media_count }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="fileMoveCopyBtn" onclick="executeFileMoveCopy()">Apply</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── Upload ──────────────────────────────────────────────────────────────────
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
                setTimeout(() => location.reload(), 600);
            },

            uploadFile(file, entry) {
                return new Promise(resolve => {
                    const formData = new FormData();
                    formData.append('files[]', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Send folder_id — either from current folder or the dropdown
                    @if($currentFolder)
                    formData.append('folder_id', '{{ $currentFolder->id }}');
                    @else
                    const sel = document.getElementById('upload-folder-id');
                    if (sel && sel.value) formData.append('folder_id', sel.value);
                    @endif

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("admin.media.upload") }}');
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
    window.mediaUploader = mediaUploader;

    // ── Copy URL ────────────────────────────────────────────────────────────────
    window.copyUrl = function (url) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({ icon: 'success', title: 'Copied!', text: url, timer: 1800, showConfirmButton: false });
        });
    };

    // ── Delete media file ───────────────────────────────────────────────────────
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
                form.action = '{{ url("admin/media") }}/' + id;
                form.submit();
            }
        });
    };

    // ── Folder rename ───────────────────────────────────────────────────────────
    window.startRename = function (id, currentName) {
        document.getElementById('folder-label-' + id).classList.add('d-none');
        var renameDiv = document.getElementById('folder-rename-' + id);
        renameDiv.classList.remove('d-none');
        var input = document.getElementById('folder-rename-input-' + id);
        input.value = currentName;
        input.focus();
        input.select();
    };

    window.cancelRename = function (id) {
        document.getElementById('folder-rename-' + id).classList.add('d-none');
        document.getElementById('folder-label-' + id).classList.remove('d-none');
    };

    window.handleRenameKey = function (e, id) {
        if (e.key === 'Enter') { e.preventDefault(); saveRename(e, id); }
        if (e.key === 'Escape') cancelRename(id);
    };

    window.saveRename = function (e, id) {
        e.preventDefault();
        var input = document.getElementById('folder-rename-input-' + id);
        var newName = input.value.trim();
        if (!newName) return;

        fetch('{{ url("admin/media-folders") }}/' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name: newName }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Update the visible link text
                var row = document.getElementById('folder-row-' + id);
                var link = row.querySelector('#folder-label-' + id + ' a');
                if (link) link.textContent = data.name;
                cancelRename(id);
            } else {
                alert(data.message || 'Could not rename folder.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    };

    // ── File Rename ─────────────────────────────────────────────────────────────
    var _renameFileId = null;

    window.openFileRename = function (id, currentName) {
        _renameFileId = id;
        document.getElementById('fileRenameInput').value = currentName;
        new bootstrap.Modal(document.getElementById('fileRenameModal')).show();
    };

    window.executeFileRename = function () {
        var name = document.getElementById('fileRenameInput').value.trim();
        if (!name) return;

        fetch('{{ url("admin/media") }}/' + _renameFileId + '/rename', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ name: name }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                var el = document.querySelector('#media-' + _renameFileId + ' .media-name');
                if (el) { el.textContent = data.name; el.title = data.name; }
                bootstrap.Modal.getInstance(document.getElementById('fileRenameModal')).hide();
                Swal.fire({ icon: 'success', title: 'Renamed!', timer: 1200, showConfirmButton: false });
            } else {
                alert(data.message || 'Could not rename file.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    };

    // ── File Move / Copy ────────────────────────────────────────────────────────
    var _moveCopyFileId = null;
    var _moveCopyAction = null;

    window.openFileMoveCopy = function (id, action) {
        _moveCopyFileId = id;
        _moveCopyAction = action;

        var modal = document.getElementById('fileMoveCopyModal');
        modal.querySelector('#fileMoveCopyModalLabel').innerHTML =
            action === 'move'
                ? '<i class="fas fa-folder-open me-2"></i>Move File'
                : '<i class="fas fa-copy me-2"></i>Copy File to Folder';
        modal.querySelector('#fileMoveCopyDesc').textContent =
            action === 'move'
                ? 'Select the destination folder. The file URL will stay the same.'
                : 'A copy of the file will be placed in the selected folder.';
        modal.querySelector('#fileMoveCopyBtn').textContent =
            action === 'move' ? 'Move' : 'Copy';

        // Clear radio selection
        modal.querySelectorAll('input[name="moveCopyFolder"]').forEach(r => r.checked = false);

        new bootstrap.Modal(modal).show();
    };

    window.executeFileMoveCopy = function () {
        var selected = document.querySelector('input[name="moveCopyFolder"]:checked');
        var folderId = selected ? selected.value : '';

        var url    = '{{ url("admin/media") }}/' + _moveCopyFileId + '/' + _moveCopyAction;
        var method = _moveCopyAction === 'move' ? 'PATCH' : 'POST';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ folder_id: folderId || null }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('fileMoveCopyModal')).hide();
                if (_moveCopyAction === 'move') {
                    // Remove from grid only when browsing a specific folder and item moved away
                    @if($currentFolder)
                    var targetFolderId = selected ? selected.value : '';
                    if (String(targetFolderId) !== '{{ $currentFolder->id }}') {
                        var el = document.getElementById('media-' + _moveCopyFileId);
                        if (el) el.remove();
                    }
                    @endif
                    Swal.fire({ icon: 'success', title: 'Moved!', timer: 1200, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'success', title: 'Copied!', text: 'A copy "' + data.name + '" was created.', timer: 2000, showConfirmButton: false });
                }
            } else {
                alert(data.message || 'Operation failed.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    };

    // Enter key on rename input triggers save
    document.addEventListener('DOMContentLoaded', function () {
        var inp = document.getElementById('fileRenameInput');
        if (inp) inp.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); executeFileRename(); }
        });
    });

    // ── Delete folder ───────────────────────────────────────────────────────────
    window.deleteFolder = function (id, name, fileCount) {
        var msg = fileCount > 0
            ? 'The ' + fileCount + ' file(s) inside will be moved to uncategorized.'
            : 'This folder is empty.';

        Swal.fire({
            title: 'Delete "' + name + '"?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete Folder',
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteFolderForm');
                form.action = '{{ url("admin/media-folders") }}/' + id;
                form.submit();
            }
        });
    };

}());
</script>
@endpush
