{{--
    Icon Picker Partial
    Props:
      $currentIcon  - currently saved icon class (e.g. "fas fa-code"), or null
      $inputName    - name attribute for the hidden input (default "icon")
--}}
@php
    $inputName   = $inputName   ?? 'icon';
    $currentIcon = $currentIcon ?? old($inputName, 'fas fa-folder');

    $iconGroups = [
        'Technology' => [
            'fas fa-microchip','fas fa-code','fas fa-laptop-code','fas fa-terminal',
            'fas fa-database','fas fa-server','fas fa-network-wired','fas fa-cloud',
            'fas fa-robot','fas fa-brain','fas fa-satellite','fas fa-shield-alt',
            'fas fa-lock','fas fa-bug','fas fa-memory','fas fa-hard-drive',
        ],
        'Mobile & Apps' => [
            'fas fa-mobile-alt','fas fa-tablet-alt','fas fa-desktop','fas fa-keyboard',
            'fas fa-wifi','fas fa-bluetooth','fas fa-qrcode','fas fa-gamepad',
            'fab fa-android','fab fa-apple','fab fa-windows','fab fa-linux',
        ],
        'Design & Art' => [
            'fas fa-palette','fas fa-paint-brush','fas fa-pencil-alt','fas fa-pencil-ruler',
            'fas fa-vector-square','fas fa-bezier-curve','fas fa-crop-alt','fas fa-fill-drip',
            'fas fa-font','fas fa-text-height','fas fa-italic','fas fa-heading',
            'fas fa-image','fas fa-photo-video','fas fa-film','fas fa-eye-dropper',
        ],
        'Business' => [
            'fas fa-briefcase','fas fa-chart-line','fas fa-chart-bar','fas fa-chart-pie',
            'fas fa-rocket','fas fa-bullhorn','fas fa-handshake','fas fa-dollar-sign',
            'fas fa-coins','fas fa-credit-card','fas fa-building','fas fa-store',
            'fas fa-tag','fas fa-tags','fas fa-receipt','fas fa-file-invoice',
        ],
        'Lifestyle' => [
            'fas fa-heartbeat','fas fa-dumbbell','fas fa-running','fas fa-bicycle',
            'fas fa-leaf','fas fa-seedling','fas fa-sun','fas fa-moon',
            'fas fa-utensils','fas fa-coffee','fas fa-cocktail','fas fa-wine-glass',
            'fas fa-plane','fas fa-car','fas fa-train','fas fa-ship',
        ],
        'Education' => [
            'fas fa-graduation-cap','fas fa-book','fas fa-book-open','fas fa-pen-nib',
            'fas fa-chalkboard-teacher','fas fa-school','fas fa-university','fas fa-microscope',
            'fas fa-flask','fas fa-atom','fas fa-globe','fas fa-map',
            'fas fa-lightbulb','fas fa-puzzle-piece','fas fa-chess','fas fa-trophy',
        ],
        'Media & News' => [
            'fas fa-newspaper','fas fa-rss','fas fa-podcast','fas fa-broadcast-tower',
            'fas fa-video','fas fa-camera','fas fa-microphone','fas fa-headphones',
            'fas fa-music','fas fa-play-circle','fas fa-tv','fas fa-satellite-dish',
            'fab fa-youtube','fab fa-twitter','fab fa-instagram','fab fa-linkedin',
        ],
        'General' => [
            'fas fa-folder','fas fa-star','fas fa-fire','fas fa-bolt',
            'fas fa-gem','fas fa-crown','fas fa-award','fas fa-medal',
            'fas fa-flag','fas fa-bookmark','fas fa-bell','fas fa-comments',
            'fas fa-users','fas fa-user','fas fa-heart','fas fa-thumbs-up',
        ],
    ];
@endphp

<div x-data="iconPicker('{{ $currentIcon }}')" class="icon-picker-widget">

    {{-- Hidden field that stores the selected icon class --}}
    <input type="hidden" name="{{ $inputName }}" x-model="selected">

    {{-- Preview + trigger --}}
    <div class="d-flex align-items-center gap-3 mb-2">
        <div class="icon-picker-preview rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:52px;height:52px;background:var(--bs-light,#f8f9fa);border:2px solid #dee2e6;">
            <i :class="selected" style="font-size:1.4rem;color:#4f46e5;"></i>
        </div>
        <div>
            <div class="small fw-semibold mb-1" x-text="selected"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" @click="open = !open">
                <i class="fas fa-icons me-1"></i>
                <span x-text="open ? 'Close Picker' : 'Choose Icon'"></span>
            </button>
        </div>
    </div>

    {{-- Picker panel --}}
    <div x-show="open" x-cloak
         class="icon-picker-panel border rounded-3 bg-white shadow-sm mt-2" style="display:none;">

        {{-- Search --}}
        <div class="p-3 border-bottom">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 bg-light"
                       placeholder="Search icons…" x-model="search"
                       @input="filterIcons()">
                <button class="btn btn-outline-secondary btn-sm" type="button" @click="search='';filterIcons()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Icon grid --}}
        <div class="icon-picker-grid p-3" style="max-height:320px;overflow-y:auto;">
            @foreach($iconGroups as $groupName => $icons)
            <div class="icon-picker-group mb-3" data-group="{{ $groupName }}">
                <div class="icon-group-label small fw-semibold text-muted mb-2 text-uppercase"
                     style="letter-spacing:.06em;font-size:.65rem;">
                    {{ $groupName }}
                </div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($icons as $icon)
                    <button type="button"
                            class="icon-picker-btn"
                            data-icon="{{ $icon }}"
                            :class="selected === '{{ $icon }}' ? 'active' : ''"
                            @click="pick('{{ $icon }}')"
                            title="{{ $icon }}">
                        <i class="{{ $icon }}"></i>
                    </button>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- No results --}}
            <div x-show="noResults" style="display:none;" class="text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2 d-block opacity-25"></i>
                <small>No icons match "<span x-text="search"></span>"</small>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.icon-picker-btn {
    width: 36px; height: 36px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #f9fafb;
    color: #6b7280;
    font-size: .875rem;
    cursor: pointer;
    transition: all .15s ease;
    padding: 0;
}
.icon-picker-btn:hover { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }
.icon-picker-btn.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
.icon-picker-panel { border-color: #dee2e6 !important; }
</style>
@endpush
@endonce

@once
@push('scripts')
<script>
function iconPicker(initial) {
    return {
        selected: initial || 'fas fa-folder',
        open: false,
        search: '',
        noResults: false,

        pick(icon) {
            this.selected = icon;
            this.open = false;
            this.search = '';
            this.filterIcons();
        },

        filterIcons() {
            const q = this.search.toLowerCase().trim();
            let visibleGroups = 0;

            document.querySelectorAll('.icon-picker-group').forEach(group => {
                let visibleInGroup = 0;
                group.querySelectorAll('.icon-picker-btn').forEach(btn => {
                    const icon = btn.dataset.icon;
                    const match = !q || icon.includes(q);
                    btn.style.display = match ? '' : 'none';
                    if (match) visibleInGroup++;
                });
                group.style.display = visibleInGroup ? '' : 'none';
                if (visibleInGroup) visibleGroups++;
            });

            this.noResults = visibleGroups === 0;
        }
    };
}
</script>
@endpush
@endonce
