{{--
    Media Library Picker Modal
    Usage: @include('admin.partials.media-picker-modal')
    Trigger: window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'featured' } }))
    Result: window listens for 'media-picked' event → detail: { url, file_name, name, context }
--}}

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .mp-backdrop {
        position: fixed; inset: 0; z-index: 1055;
        background: rgba(0,0,0,.65);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .mp-dialog {
        background: #fff;
        border-radius: .75rem;
        width: 100%; max-width: 1060px;
        max-height: 90vh;
        display: flex; flex-direction: column;
        box-shadow: 0 1rem 3rem rgba(0,0,0,.3);
    }

    /* Header */
    .mp-header {
        display: flex; align-items: center; gap: .75rem;
        padding: .875rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
        flex-shrink: 0;
    }

    /* Body row */
    .mp-body {
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    /* Folder sidebar */
    .mp-sidebar {
        width: 185px;
        flex-shrink: 0;
        border-right: 1px solid #e9ecef;
        overflow-y: auto;
        padding: .625rem .5rem;
    }
    .mp-folder-btn {
        display: flex; align-items: center; gap: .5rem;
        width: 100%;
        padding: .4rem .65rem;
        border: none; background: none;
        border-radius: .4rem;
        font-size: .825rem;
        color: #343a40;
        text-align: left;
        cursor: pointer;
        transition: background .12s;
    }
    .mp-folder-btn:hover { background: #f1f3f5; }
    .mp-folder-btn.active { background: #e7f1ff; color: #0d6efd; font-weight: 600; }
    .mp-folder-btn .mp-folder-count {
        margin-left: auto;
        font-size: .67rem;
        background: #e9ecef; color: #6c757d;
        border-radius: 100px; padding: .1rem .4rem;
        flex-shrink: 0;
    }
    .mp-folder-btn.active .mp-folder-count { background: #cfe2ff; color: #0d6efd; }

    /* Grid area */
    .mp-grid-area {
        flex: 1; overflow-y: auto; padding: .875rem;
        min-width: 0;
    }

    /* Image tiles */
    .mp-thumb {
        position: relative;
        aspect-ratio: 1;
        border-radius: .4rem;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: border-color .12s, box-shadow .12s;
    }
    .mp-thumb:hover { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
    .mp-thumb.is-selected { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.3); }
    .mp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .mp-thumb .mp-check {
        position: absolute; inset: 0;
        background: rgba(13,110,253,.35);
        display: flex; align-items: center; justify-content: center;
    }
    .mp-thumb .mp-label {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(0,0,0,.55); color: #fff;
        font-size: .67rem; padding: .15rem .3rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Drop zone */
    .mp-drop-zone {
        border: 2px dashed #dee2e6; border-radius: .5rem;
        padding: 2rem; text-align: center;
        transition: border-color .2s, background .2s; cursor: pointer;
    }
    .mp-drop-zone.dragging { border-color: #0d6efd; background: #f0f5ff; }

    /* Footer */
    .mp-footer {
        display: flex; align-items: center; justify-content: space-between;
        padding: .75rem 1.25rem;
        border-top: 1px solid #e9ecef;
        flex-shrink: 0;
    }
</style>
@endpush

<div x-data="mediaPickerModal()"
     x-cloak
     x-show="isOpen"
     x-transition.opacity
     @open-media-picker.window="open($event.detail?.context)"
     @keydown.escape.window="close()"
     class="mp-backdrop"
     @click.self="close()">

    <div class="mp-dialog" @click.stop>

        {{-- ── Header ── --}}
        <div class="mp-header">
            <h5 class="mb-0 fw-bold me-1" style="white-space:nowrap;">
                <i class="fas fa-images text-primary me-2"></i>Media Library
            </h5>

            {{-- Search --}}
            <div class="position-relative flex-grow-1" style="max-width:260px;">
                <input type="text" class="form-control form-control-sm ps-4"
                       placeholder="Search files..."
                       x-model="search"
                       @input.debounce.350ms="reload()">
                <i class="fas fa-search position-absolute text-muted" style="left:.65rem;top:.52rem;font-size:.78rem;"></i>
            </div>

            {{-- Upload button --}}
            <label class="btn btn-success btn-sm mb-0 position-relative ms-auto" :class="uploading ? 'disabled' : ''">
                <template x-if="!uploading">
                    <span><i class="fas fa-upload me-1"></i>Upload</span>
                </template>
                <template x-if="uploading">
                    <span><span class="spinner-border spinner-border-sm me-1"></span>Uploading…</span>
                </template>
                <input type="file" class="position-absolute opacity-0" style="inset:0;width:100%;height:100%;cursor:pointer;"
                       accept="image/*" multiple :disabled="uploading"
                       @change="uploadFiles($event)">
            </label>

            <button type="button" class="btn-close ms-1" @click="close()"></button>
        </div>

        {{-- ── Body (sidebar + grid) ── --}}
        <div class="mp-body">

            {{-- Folder sidebar --}}
            <nav class="mp-sidebar">
                <p class="text-muted mb-1 px-1" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Folders</p>

                {{-- All Files --}}
                <button type="button"
                        class="mp-folder-btn"
                        :class="activeFolder === null ? 'active' : ''"
                        @click="selectFolder(null)">
                    <i class="fas fa-th-large" style="width:14px;text-align:center;flex-shrink:0;"></i>
                    <span>All Files</span>
                    <span class="mp-folder-count" x-text="totalAll"></span>
                </button>

                <hr class="my-1">

                {{-- Folder list --}}
                <template x-if="foldersLoading">
                    <div class="text-center py-3">
                        <span class="spinner-border spinner-border-sm text-muted"></span>
                    </div>
                </template>

                <template x-for="folder in folders" :key="folder.id">
                    <button type="button"
                            class="mp-folder-btn"
                            :class="activeFolder && activeFolder.id === folder.id ? 'active' : ''"
                            @click="selectFolder(folder)">
                        <i class="fas"
                           :class="activeFolder && activeFolder.id === folder.id ? 'fa-folder-open' : 'fa-folder'"
                           style="width:14px;text-align:center;flex-shrink:0;"></i>
                        <span class="text-truncate" x-text="folder.name"></span>
                        <span class="mp-folder-count" x-text="folder.count"></span>
                    </button>
                </template>

                <template x-if="!foldersLoading && folders.length === 0">
                    <p class="text-muted text-center small py-2 mb-0">No folders yet.</p>
                </template>
            </nav>

            {{-- Grid area --}}
            <div class="mp-grid-area">

                {{-- Drop zone when empty --}}
                <div x-show="!loading && items.length === 0"
                     class="mp-drop-zone"
                     :class="dragging ? 'dragging' : ''"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="dragging = false; uploadFiles($event)">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                    <p class="fw-medium text-muted mb-1" x-text="search ? 'No files match your search.' : 'No files here yet.'"></p>
                    <p class="small text-muted mb-0" x-show="!search">Drag &amp; drop or click <strong>Upload</strong> above.</p>
                </div>

                {{-- Loading skeleton --}}
                <div x-show="loading && items.length === 0" class="row g-2">
                    <template x-for="n in [1,2,3,4,5,6,7,8,9,10]" :key="n">
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="mp-thumb bg-light" style="border:none;">
                                <span class="placeholder-glow d-block w-100 h-100 position-absolute" style="inset:0;">
                                    <span class="placeholder w-100 h-100 d-block"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Image grid --}}
                <div class="row g-2" x-show="items.length > 0">
                    <template x-for="item in items" :key="item.id">
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="mp-thumb"
                                 :class="selected && selected.id === item.id ? 'is-selected' : ''"
                                 @click="selectItem(item)"
                                 :title="item.name">
                                <img :src="item.url" :alt="item.name" loading="lazy">
                                <div class="mp-check" x-show="selected && selected.id === item.id">
                                    <i class="fas fa-check-circle text-white fa-lg"></i>
                                </div>
                                <div class="mp-label" x-text="item.name"></div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Load more --}}
                <div class="text-center mt-3" x-show="hasMore">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            :disabled="loading"
                            @click="loadMore()">
                        <template x-if="!loading">
                            <span><i class="fas fa-chevron-down me-1"></i>Load More</span>
                        </template>
                        <template x-if="loading">
                            <span><span class="spinner-border spinner-border-sm me-1"></span>Loading…</span>
                        </template>
                    </button>
                </div>

            </div>{{-- end .mp-grid-area --}}
        </div>{{-- end .mp-body --}}

        {{-- ── Footer ── --}}
        <div class="mp-footer">
            <span class="text-muted small">
                <template x-if="activeFolder">
                    <span>
                        <i class="fas fa-folder text-primary me-1"></i>
                        <span x-text="activeFolder.name"></span> &mdash;
                    </span>
                </template>
                <span x-text="total"></span> file<span x-show="total !== 1">s</span>
                <template x-if="selected">
                    <span class="ms-2 text-primary fw-medium">
                        <i class="fas fa-check-circle me-1"></i><span x-text="selected.name"></span> selected
                    </span>
                </template>
            </span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" @click="close()">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm"
                        :disabled="!selected"
                        @click="confirmSelection()">
                    <i class="fas fa-check me-1"></i>Use This Image
                </button>
            </div>
        </div>

    </div>{{-- end .mp-dialog --}}
</div>

@push('scripts')
<script>
function mediaPickerModal() {
    return {
        isOpen:        false,
        context:       'featured',
        items:         [],
        loading:       false,
        uploading:     false,
        dragging:      false,
        search:        '',
        page:          1,
        hasMore:       false,
        total:         0,
        totalAll:      0,
        selected:      null,
        folders:       [],
        foldersLoading: false,
        activeFolder:  null,

        async open(ctx) {
            this.context      = ctx || 'featured';
            this.isOpen       = true;
            this.selected     = null;
            this.page         = 1;
            this.items        = [];
            this.search       = '';
            this.hasMore      = false;
            this.activeFolder = null;
            await this.fetchFolders();
            this.fetchMedia();
        },

        close() { this.isOpen = false; },

        reload() {
            this.page  = 1;
            this.items = [];
            this.fetchMedia();
        },

        selectFolder(folder) {
            this.activeFolder = folder;
            this.search       = '';
            this.reload();
        },

        async fetchFolders() {
            this.foldersLoading = true;
            try {
                const res     = await fetch('{{ route("admin.media-folders.index") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                this.folders  = await res.json();
                // Also load total count for "All Files"
                const total   = await fetch('{{ route("admin.media.list") }}?page=1', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const tData   = await total.json();
                this.totalAll = tData.total ?? 0;
            } catch (e) {
                console.error('Media picker: folder fetch error', e);
            }
            this.foldersLoading = false;
        },

        async fetchMedia() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page });
                if (this.search)       params.set('q',      this.search);
                if (this.activeFolder) params.set('folder', this.activeFolder.slug);

                const res  = await fetch('{{ route("admin.media.list") }}?' + params, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                this.items   = this.page === 1 ? data.data : [...this.items, ...data.data];
                this.hasMore = data.has_more;
                this.total   = data.total;
            } catch (e) {
                console.error('Media picker: load error', e);
            }
            this.loading = false;
        },

        loadMore() {
            if (this.loading) return;
            this.page++;
            this.fetchMedia();
        },

        async uploadFiles(event) {
            const files      = event.target?.files ?? event.dataTransfer?.files;
            if (!files || !files.length) return;
            const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
            if (!imageFiles.length) return;

            this.uploading = true;
            const formData = new FormData();
            imageFiles.forEach(f => formData.append('files[]', f));
            formData.append('_token', '{{ csrf_token() }}');
            if (this.activeFolder) formData.append('folder_id', this.activeFolder.id);

            try {
                const res  = await fetch('{{ route("admin.media.store") }}', {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    formData,
                });
                const data = await res.json();
                if (data.files && data.files.length) {
                    const newItems = data.files.map(f => ({
                        id: f.id, name: f.name, url: f.url,
                        file_name: f.file_name, size: f.size,
                    }));
                    this.items    = [...newItems, ...this.items];
                    this.total   += newItems.length;
                    this.totalAll += newItems.length;
                    this.selected = newItems[0];
                    // Update folder count in sidebar
                    if (this.activeFolder) {
                        const f = this.folders.find(x => x.id === this.activeFolder.id);
                        if (f) f.count += newItems.length;
                    }
                }
            } catch (e) {
                console.error('Media picker: upload error', e);
            }

            this.uploading = false;
            if (event.target) event.target.value = '';
        },

        selectItem(item) {
            this.selected = (this.selected && this.selected.id === item.id) ? null : item;
        },

        confirmSelection() {
            if (!this.selected) return;
            window.dispatchEvent(new CustomEvent('media-picked', {
                detail: {
                    url:       this.selected.url,
                    file_name: this.selected.file_name,
                    name:      this.selected.name,
                    context:   this.context,
                },
            }));
            this.close();
        },
    };
}
</script>
@endpush
