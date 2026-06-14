{{--
    Media Library Picker Modal
    Usage: @include('admin.partials.media-picker-modal')
    Trigger: window.dispatchEvent(new CustomEvent('open-media-picker'))
    Result: window listens for 'media-picked' event → detail: { url, file_name, name }
--}}

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .media-picker-backdrop {
        position: fixed; inset: 0; z-index: 1055;
        background: rgba(0,0,0,.65);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .media-picker-dialog {
        background: #fff;
        border-radius: .75rem;
        width: 100%; max-width: 960px;
        max-height: 90vh;
        display: flex; flex-direction: column;
        box-shadow: 0 1rem 3rem rgba(0,0,0,.3);
    }
    .media-picker-body {
        overflow-y: auto;
        flex: 1 1 auto;
        padding: 1rem;
    }
    .media-thumb {
        position: relative;
        aspect-ratio: 1;
        border-radius: .4rem;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: border-color .15s, box-shadow .15s;
    }
    .media-thumb:hover { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
    .media-thumb.is-selected { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.25); }
    .media-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .media-thumb .check-overlay {
        position: absolute; inset: 0;
        background: rgba(13,110,253,.35);
        display: flex; align-items: center; justify-content: center;
    }
    .media-thumb .name-label {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(0,0,0,.55);
        color: #fff; font-size: .68rem;
        padding: .15rem .3rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .media-drop-zone {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        padding: 1.5rem;
        text-align: center;
        transition: border-color .2s, background .2s;
        cursor: pointer;
    }
    .media-drop-zone.dragging { border-color: #0d6efd; background: #f0f5ff; }
</style>
@endpush

<div x-data="mediaPickerModal()"
     x-cloak
     x-show="isOpen"
     x-transition.opacity
     @open-media-picker.window="open($event.detail?.context)"
     @keydown.escape.window="close()"
     class="media-picker-backdrop"
     @click.self="close()">

    <div class="media-picker-dialog" @click.stop>

        {{-- Header --}}
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom flex-shrink-0">
            <h5 class="mb-0 fw-bold flex-grow-1">
                <i class="fas fa-images text-primary me-2"></i>Media Library
            </h5>
            <div class="position-relative flex-grow-1" style="max-width:280px;">
                <input type="text" class="form-control form-control-sm ps-4"
                       placeholder="Search images..."
                       x-model="search"
                       @input.debounce.400ms="reload()">
                <i class="fas fa-search position-absolute text-muted" style="left:.65rem;top:.5rem;font-size:.8rem;"></i>
            </div>
            <label class="btn btn-success btn-sm mb-0 position-relative" :class="uploading ? 'disabled' : ''">
                <template x-if="!uploading">
                    <span><i class="fas fa-upload me-1"></i>Upload New</span>
                </template>
                <template x-if="uploading">
                    <span><span class="spinner-border spinner-border-sm me-1" role="status"></span>Uploading…</span>
                </template>
                <input type="file" class="position-absolute opacity-0" style="inset:0;width:100%;height:100%;cursor:pointer;"
                       accept="image/*" multiple :disabled="uploading"
                       @change="uploadFiles($event)">
            </label>
            <button type="button" class="btn-close" @click="close()"></button>
        </div>

        {{-- Body: image grid --}}
        <div class="media-picker-body">

            {{-- Upload drop zone (visible only when library is empty) --}}
            <div x-show="!loading && items.length === 0"
                 class="media-drop-zone mb-3"
                 :class="dragging ? 'dragging' : ''"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="dragging = false; uploadFiles($event)">
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                <p class="mb-1 fw-medium text-muted" x-text="search ? 'No images match your search.' : 'No images in the library yet.'"></p>
                <p class="small text-muted mb-0" x-show="!search">Drag &amp; drop images here or click <strong>Upload New</strong> above.</p>
            </div>

            {{-- Loading skeleton --}}
            <div x-show="loading && items.length === 0" class="row g-2">
                <template x-for="n in [1,2,3,4,5,6,7,8]" :key="n">
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="media-thumb bg-light" style="border:none;">
                            <div class="placeholder-glow w-100 h-100 position-absolute inset-0">
                                <span class="placeholder w-100 h-100 d-block"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Image grid --}}
            <div class="row g-2" x-show="items.length > 0">
                <template x-for="item in items" :key="item.id">
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="media-thumb"
                             :class="selected && selected.id === item.id ? 'is-selected' : ''"
                             @click="selectItem(item)"
                             :title="item.name">
                            <img :src="item.url" :alt="item.name" loading="lazy">
                            <div class="check-overlay" x-show="selected && selected.id === item.id">
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                            <div class="name-label" x-text="item.name"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load more --}}
            <div class="text-center mt-3" x-show="hasMore">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        :class="loading ? 'disabled' : ''"
                        @click="loadMore()">
                    <template x-if="!loading">
                        <span><i class="fas fa-chevron-down me-1"></i>Load More</span>
                    </template>
                    <template x-if="loading">
                        <span><span class="spinner-border spinner-border-sm me-1" role="status"></span>Loading…</span>
                    </template>
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top flex-shrink-0">
            <span class="text-muted small">
                <span x-text="total"></span> image<span x-show="total !== 1">s</span> in library
                <template x-if="selected">
                    <span class="ms-2 text-primary fw-medium">
                        &mdash; <i class="fas fa-check-circle me-1"></i><span x-text="selected.name"></span> selected
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
    </div>
</div>

@push('scripts')
<script>
function mediaPickerModal() {
    return {
        isOpen:    false,
        context:   'featured',
        items:     [],
        loading:   false,
        uploading: false,
        dragging:  false,
        search:    '',
        page:      1,
        hasMore:   false,
        total:     0,
        selected:  null,

        open(ctx) {
            this.context  = ctx || 'featured';
            this.isOpen   = true;
            this.selected = null;
            this.page     = 1;
            this.items    = [];
            this.search   = '';
            this.hasMore  = false;
            this.fetchMedia();
        },

        close() {
            this.isOpen = false;
        },

        reload() {
            this.page  = 1;
            this.items = [];
            this.fetchMedia();
        },

        async fetchMedia() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page });
                if (this.search) params.set('q', this.search);
                const res  = await fetch('{{ route("admin.media.list") }}?' + params, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
            const files = event.target?.files ?? event.dataTransfer?.files;
            if (!files || !files.length) return;

            const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
            if (!imageFiles.length) return;

            this.uploading = true;
            const formData = new FormData();
            imageFiles.forEach(f => formData.append('files[]', f));

            try {
                const res  = await fetch('{{ route("admin.media.store") }}', {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const data = await res.json();
                if (data.files && data.files.length) {
                    const newItems = data.files.map(f => ({
                        id:        f.id,
                        name:      f.name,
                        url:       f.url,
                        file_name: f.file_name,
                        size:      f.size,
                    }));
                    this.items = [...newItems, ...this.items];
                    this.total += newItems.length;
                    this.selected = newItems[0];
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
                }
            }));
            this.close();
        },
    };
}
</script>
@endpush
