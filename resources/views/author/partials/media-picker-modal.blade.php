{{--
    Author Media Picker Modal (no folder sidebar — simpler than admin version)
    Trigger : window.dispatchEvent(new CustomEvent('open-media-picker', { detail: { context: 'featured' } }))
    Result  : window listens for 'media-picked' → detail: { url, file_name, name, context }
--}}

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .amp-backdrop {
        position: fixed; inset: 0; z-index: 1055;
        background: rgba(0,0,0,.65);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .amp-dialog {
        background: #fff;
        border-radius: .75rem;
        width: 100%; max-width: 900px;
        max-height: 88vh;
        display: flex; flex-direction: column;
        box-shadow: 0 1rem 3rem rgba(0,0,0,.3);
    }
    .amp-header {
        display: flex; align-items: center; gap: .75rem;
        padding: .875rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
        flex-shrink: 0;
    }
    .amp-grid-area {
        flex: 1; overflow-y: auto; padding: 1rem;
        min-height: 0;
    }
    .amp-thumb {
        position: relative;
        aspect-ratio: 1;
        border-radius: .4rem;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: border-color .12s, box-shadow .12s;
    }
    .amp-thumb:hover { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
    .amp-thumb.is-selected { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.3); }
    .amp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .amp-thumb .amp-check {
        position: absolute; inset: 0;
        background: rgba(13,110,253,.35);
        display: flex; align-items: center; justify-content: center;
    }
    .amp-thumb .amp-label {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(0,0,0,.55); color: #fff;
        font-size: .67rem; padding: .15rem .3rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .amp-drop-zone {
        border: 2px dashed #dee2e6; border-radius: .5rem;
        padding: 2.5rem; text-align: center;
        transition: border-color .2s, background .2s; cursor: pointer;
    }
    .amp-drop-zone.dragging { border-color: #0d6efd; background: #f0f5ff; }
    .amp-footer {
        display: flex; align-items: center; justify-content: space-between;
        padding: .75rem 1.25rem;
        border-top: 1px solid #e9ecef;
        flex-shrink: 0;
    }
</style>
@endpush

<div x-data="authorMediaPicker()"
     x-cloak
     x-show="isOpen"
     x-transition.opacity
     @open-media-picker.window="open($event.detail?.context)"
     @keydown.escape.window="close()"
     class="amp-backdrop"
     @click.self="close()">

    <div class="amp-dialog" @click.stop>

        {{-- Header --}}
        <div class="amp-header">
            <h5 class="mb-0 fw-bold me-1" style="white-space:nowrap;">
                <i class="fas fa-images text-primary me-2"></i>My Media
            </h5>

            {{-- Search --}}
            <div class="position-relative flex-grow-1" style="max-width:240px;">
                <input type="text" class="form-control form-control-sm ps-4"
                       placeholder="Search images..."
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
                <input type="file" class="position-absolute opacity-0"
                       style="inset:0;width:100%;height:100%;cursor:pointer;"
                       accept="image/*" multiple :disabled="uploading"
                       @change="uploadFiles($event)">
            </label>

            <button type="button" class="btn-close ms-2" @click="close()"></button>
        </div>

        {{-- Grid --}}
        <div class="amp-grid-area"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="dragging = false; uploadFiles($event)">

            {{-- Drop zone when empty --}}
            <div x-show="!loading && items.length === 0"
                 class="amp-drop-zone"
                 :class="dragging ? 'dragging' : ''"
                 @click="$el.querySelector('label')?.click()">
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                <p class="fw-medium text-muted mb-1"
                   x-text="search ? 'No images match your search.' : 'No images yet.'"></p>
                <p class="small text-muted mb-0" x-show="!search">
                    Drag &amp; drop or click <strong>Upload</strong> above.
                </p>
            </div>

            {{-- Loading skeleton --}}
            <div x-show="loading && items.length === 0" class="row g-2">
                <template x-for="n in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="n">
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="amp-thumb bg-light" style="border:none;">
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
                        <div class="amp-thumb"
                             :class="selected && selected.id === item.id ? 'is-selected' : ''"
                             @click="selectItem(item)"
                             :title="item.name">
                            <img :src="item.url" :alt="item.name" loading="lazy">
                            <div class="amp-check" x-show="selected && selected.id === item.id">
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                            <div class="amp-label" x-text="item.name"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load more --}}
            <div class="text-center mt-3" x-show="hasMore">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        :disabled="loading" @click="loadMore()">
                    <template x-if="!loading">
                        <span><i class="fas fa-chevron-down me-1"></i>Load More</span>
                    </template>
                    <template x-if="loading">
                        <span><span class="spinner-border spinner-border-sm me-1"></span>Loading…</span>
                    </template>
                </button>
            </div>

        </div>{{-- end amp-grid-area --}}

        {{-- Footer --}}
        <div class="amp-footer">
            <span class="text-muted small">
                <span x-text="total"></span> image<span x-show="total !== 1">s</span>
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

    </div>{{-- end amp-dialog --}}
</div>

@push('scripts')
<script>
function authorMediaPicker() {
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

        async open(ctx) {
            this.context  = ctx || 'featured';
            this.isOpen   = true;
            this.selected = null;
            this.page     = 1;
            this.items    = [];
            this.search   = '';
            this.hasMore  = false;
            this.fetchMedia();
        },

        close() { this.isOpen = false; },

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

                const res  = await fetch('{{ route("author.media.list") }}?' + params, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                this.items   = this.page === 1 ? data.data : [...this.items, ...data.data];
                this.hasMore = data.has_more;
                this.total   = data.total;
            } catch (e) {
                console.error('Author media picker: load error', e);
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
            formData.append('folder_id', '{{ \App\Models\MediaFolder::where("slug","posts")->value("id") }}');

            try {
                const res  = await fetch('{{ route("author.media.store") }}', {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    formData,
                });
                const data = await res.json();
                if (data.files && data.files.length) {
                    const newItems = data.files.map(f => ({
                        id: f.id, name: f.name, url: f.url, file_name: f.file_name ?? '',
                    }));
                    this.items  = [...newItems, ...this.items];
                    this.total += newItems.length;
                    this.selected = newItems[0];
                }
            } catch (e) {
                console.error('Author media picker: upload error', e);
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
