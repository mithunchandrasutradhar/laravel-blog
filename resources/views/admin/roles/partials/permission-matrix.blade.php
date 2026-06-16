{{--
    Shared permission matrix partial.
    Expects: $allPermissions, $moduleLabels, $permissionLabels, $assigned, $roleColor, $totalPermissions
--}}
<div class="row g-4">

    {{-- ── Left: modules ── --}}
    <div class="col-lg-8">
        <style>:root { --role-color: {{ $roleColor }}; --role-bg: {{ $roleColor }}18; }</style>

        {{-- ═══════════════════════════════════════════════════════
             Panel Access — single radio choice
        ════════════════════════════════════════════════════════════ --}}
        @if($allPermissions->has('panel'))
        @php
            $hasAdmin   = in_array('panel.admin',  $assigned);
            $hasAuthor  = in_array('panel.author', $assigned);
            $panelValue = ($hasAdmin || $hasAuthor) ? 'panel' : 'none';
            $panelSel   = ($hasAdmin || $hasAuthor) ? 1 : 0;
        @endphp
        <div class="perm-module-card mb-1" id="module-panel">
            <div class="perm-module-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-door-open" style="color:{{ $roleColor }};width:18px;text-align:center;"></i>
                    <span class="fw-semibold">Panel Access</span>
                    <span class="badge rounded-pill ms-1"
                          style="background:{{ $roleColor }}20;color:{{ $roleColor }};font-size:.68rem;">
                        <span class="mod-count-panel">{{ $panelSel }}</span>/1
                    </span>
                </div>
                <span class="small text-muted fw-medium" id="panelAccessLabel">
                    {{ $panelValue === 'panel' ? 'Panel' : 'No Access' }}
                </span>
            </div>
            <div class="p-3 d-flex gap-3 flex-wrap">
                @foreach([
                    ['value' => 'none',  'label' => 'No Access', 'icon' => 'fas fa-ban',        'ibg' => '#f3f4f6', 'ifg' => '#6b7280'],
                    ['value' => 'panel', 'label' => 'Panel',     'icon' => 'fas fa-tachometer-alt', 'ibg' => '#e0f2fe', 'ifg' => '#0284c7'],
                ] as $opt)
                <label class="panel-option {{ $panelValue === $opt['value'] ? 'active' : '' }}"
                       data-value="{{ $opt['value'] }}"
                       onclick="selectPanel('{{ $opt['value'] }}')">
                    <span class="panel-opt-icon" style="background:{{ $opt['ibg'] }};color:{{ $opt['ifg'] }};">
                        <i class="{{ $opt['icon'] }}"></i>
                    </span>
                    {{ $opt['label'] }}
                </label>
                @endforeach

                {{-- Hidden checkboxes — submitted with the form --}}
                {{-- panel.admin is never set via UI (admin role gets it via seeder) --}}
                <input type="checkbox" name="permissions[]" value="panel.admin"
                       id="hiddenPanelAdmin"  class="d-none">
                <input type="checkbox" name="permissions[]" value="panel.author"
                       id="hiddenPanelAuthor" class="d-none" {{ ($hasAdmin || $hasAuthor) ? 'checked' : '' }}>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             Regular permission modules (panel excluded)
        ════════════════════════════════════════════════════════════ --}}
        @foreach($allPermissions->except(['panel']) as $module => $permissions)
        @php
            $moduleLabel      = $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
            $assignedInModule = $permissions->filter(fn($p) => in_array($p->name, $assigned))->count();
            $totalInModule    = $permissions->count();
            $moduleIcons      = [
                'posts'            => 'fas fa-newspaper',
                'categories'       => 'fas fa-folder',
                'tags'             => 'fas fa-tags',
                'comments'         => 'fas fa-comments',
                'users'            => 'fas fa-users',
                'media'            => 'fas fa-photo-video',
                'settings'         => 'fas fa-cog',
                'advertisements'   => 'fas fa-ad',
                'subscribers'      => 'fas fa-envelope',
                'contact_messages' => 'fas fa-envelope-open-text',
                'videos'           => 'fab fa-youtube',
            ];
            $icon = $moduleIcons[$module] ?? 'fas fa-shield-alt';
        @endphp
        <div class="perm-module-card" id="module-{{ $module }}">
            <div class="perm-module-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="{{ $icon }}" style="color:{{ $roleColor }};width:18px;text-align:center;"></i>
                    <span class="fw-semibold">{{ $moduleLabel }}</span>
                    <span class="badge rounded-pill ms-1"
                          style="background:{{ $roleColor }}20;color:{{ $roleColor }};font-size:.68rem;">
                        <span class="mod-count-{{ $module }}">{{ $assignedInModule }}</span>/{{ $totalInModule }}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="module-select-all small text-muted text-decoration-none"
                       onclick="event.preventDefault(); selectAllInModule('{{ $module }}', true)">All</a>
                    <span class="text-muted" style="font-size:.7rem;">|</span>
                    <a href="#" class="module-select-all small text-muted text-decoration-none"
                       onclick="event.preventDefault(); selectAllInModule('{{ $module }}', false)">None</a>
                </div>
            </div>
            <div class="perm-grid" id="perm-body-{{ $module }}">
                @foreach($permissions as $permission)
                @php
                    $action = explode('.', $permission->name, 2)[1] ?? $permission->name;
                    $label  = $permissionLabels[$action] ?? ucfirst($action);
                    $isOn   = in_array($permission->name, $assigned);
                @endphp
                <label class="perm-chip {{ $isOn ? 'active' : '' }}" data-module="{{ $module }}">
                    <input type="checkbox"
                           name="permissions[]"
                           value="{{ $permission->name }}"
                           {{ $isOn ? 'checked' : '' }}
                           onchange="onPermChange(this)">
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>

    {{-- ── Right: live summary sidebar ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="position:sticky;top:80px;">
            <div class="card-body">

                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Permissions selected</span>
                        <strong id="totalCount">{{ count($assigned) }}</strong>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar" id="totalBar" role="progressbar"
                             style="background:{{ $roleColor }};
                                    width:{{ $totalPermissions > 0 ? round((count($assigned)/$totalPermissions)*100) : 0 }}%">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-1 mb-4">
                    {{-- Panel row --}}
                    @if($allPermissions->has('panel'))
                    <div class="d-flex justify-content-between align-items-center" style="font-size:.78rem;">
                        <span class="text-muted">Panel Access</span>
                        <span class="badge rounded-pill bg-light text-dark mod-badge-panel">{{ $panelSel }}/1</span>
                    </div>
                    @endif
                    {{-- Content module rows --}}
                    @foreach($allPermissions->except(['panel']) as $module => $permissions)
                    @php
                        $ml  = $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
                        $cnt = $permissions->filter(fn($p) => in_array($p->name, $assigned))->count();
                        $tot = $permissions->count();
                    @endphp
                    <div class="d-flex justify-content-between align-items-center" style="font-size:.78rem;">
                        <span class="text-muted">{{ $ml }}</span>
                        <span class="badge rounded-pill bg-light text-dark mod-badge-{{ $module }}">
                            {{ $cnt }}/{{ $tot }}
                        </span>
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="btn w-100 fw-semibold" id="saveBtn"
                        style="background:{{ $roleColor }};color:#fff;">
                    <i class="fas fa-save me-2"></i>{{ $submitLabel ?? 'Save Permissions' }}
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                    Cancel
                </a>

            </div>
        </div>
    </div>

</div>
