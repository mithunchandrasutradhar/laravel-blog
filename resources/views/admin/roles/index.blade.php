@extends('admin.layouts.admin')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Control what each role can do across the platform')

@section('page-actions')
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>New Role
    </a>
@endsection

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Info banner --}}
<div class="alert alert-info d-flex gap-3 align-items-start mb-4" role="alert">
    <i class="fas fa-shield-alt fa-lg mt-1 flex-shrink-0"></i>
    <div class="small">
        <strong>Admin role is always protected</strong> — it has full access and cannot be edited or deleted.
        Roles with assigned users cannot be deleted until those users are reassigned.
    </div>
</div>

{{-- Role cards --}}
<div class="row g-4 mb-5">
    @foreach($roles as $role)
    @php
        $color = $role->color ?? '#6c757d';
        $label = $role->display_name ?? ucfirst(str_replace('_', ' ', $role->name));
        $isBuiltIn = in_array($role->name, ['site_editor', 'author', 'user', 'editor']);
        $userCount = $role->users()->count();
    @endphp
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                {{-- Header --}}
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 flex-shrink-0" style="background:{{ $color }}20;">
                            <i class="fas fa-user-tag fa-lg" style="color:{{ $color }};"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $label }}</h5>
                            <code class="small text-muted" style="font-size:.72rem;">{{ $role->name }}</code>
                        </div>
                    </div>
                    @if(!$isBuiltIn)
                    <span class="badge bg-light text-muted border" style="font-size:.65rem;">Custom</span>
                    @endif
                </div>

                {{-- Description --}}
                <p class="text-muted small mb-3" style="min-height:2.5rem;">
                    {{ $role->description ?? 'No description.' }}
                </p>

                {{-- Stats --}}
                <div class="d-flex gap-3 mb-3">
                    <span class="small text-muted">
                        <i class="fas fa-key me-1" style="color:{{ $color }};"></i>
                        {{ $role->permissions_count }} permission{{ $role->permissions_count !== 1 ? 's' : '' }}
                    </span>
                    <span class="small text-muted">
                        <i class="fas fa-users me-1 text-muted"></i>
                        {{ $userCount }} user{{ $userCount !== 1 ? 's' : '' }}
                    </span>
                </div>

                {{-- Permission bar --}}
                @php $pct = $totalPermissions > 0 ? round(($role->permissions_count / $totalPermissions) * 100) : 0; @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between" style="font-size:.72rem;color:#6c757d;">
                        <span>Coverage</span><span>{{ $pct }}%</span>
                    </div>
                    <div class="progress mt-1" style="height:5px;">
                        <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                    </div>
                </div>
            </div>

            {{-- Card footer: actions --}}
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3 d-flex gap-2">
                <a href="{{ route('admin.roles.edit', $role->id) }}"
                   class="btn btn-sm flex-grow-1"
                   style="background:{{ $color }};color:#fff;">
                    <i class="fas fa-sliders-h me-1"></i>Edit
                </a>
                {{-- Delete button — hidden for built-in roles --}}
                @if(!$isBuiltIn)
                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        title="Delete role"
                        onclick="confirmDelete({{ $role->id }}, '{{ addslashes($label) }}', {{ $userCount }})">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Permissions overview matrix --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-table text-primary me-2"></i>Permissions Overview
        </h6>
        <span class="text-muted small">All roles vs. all permissions</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="min-width:180px;">Permission</th>
                        @foreach($roles as $role)
                        @php $color = $role->color ?? '#6c757d'; $label = $role->display_name ?? ucfirst(str_replace('_',' ',$role->name)); @endphp
                        <th class="text-center" style="min-width:110px;">
                            <span class="badge rounded-pill px-2 py-1" style="background:{{ $color }}20;color:{{ $color }};">
                                {{ $label }}
                            </span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        use Spatie\Permission\Models\Permission;
                        $allPerms    = Permission::orderBy('name')->get();
                        $rolePermMap = $roles->mapWithKeys(fn($r) => [$r->name => $r->permissions->pluck('name')->flip()]);
                        $moduleLabels = [
                            'panel'=>'Panel Access',
                            'posts'=>'Posts','categories'=>'Categories','tags'=>'Tags',
                            'comments'=>'Comments','users'=>'Users','media'=>'Media',
                            'videos'=>'Videos','settings'=>'Settings',
                            'advertisements'=>'Advertisements',
                            'subscribers'=>'Subscribers','contact_messages'=>'Contact Messages',
                        ];
                        $actionLabels = [
                            'author'=>'Panel',  // panel.author shown as 'Panel' (panel.admin is skipped)
                            'viewAny'=>'View List','view'=>'View Detail','create'=>'Create',
                            'update'=>'Edit','delete'=>'Delete','publish'=>'Publish',
                            'forceDelete'=>'Force Delete','restore'=>'Restore',
                            'approve'=>'Approve','reject'=>'Reject','ban'=>'Ban','upload'=>'Upload',
                        ];
                        $currentModule = null;
                    @endphp
                    @foreach($allPerms as $perm)
                        @if($perm->name === 'panel.admin')
                            @continue {{-- collapsed into the single 'Panel' row (panel.author) --}}
                        @endif
                        @php
                            [$mod, $act] = explode('.', $perm->name, 2) + [1 => $perm->name];
                            $modLabel = $moduleLabels[$mod] ?? ucfirst($mod);
                            $actLabel = $actionLabels[$act] ?? ucfirst($act);
                        @endphp
                        @if($mod !== $currentModule)
                            @php $currentModule = $mod @endphp
                            <tr class="table-light">
                                <td colspan="{{ $roles->count() + 1 }}" class="ps-3 fw-semibold text-uppercase"
                                    style="font-size:.68rem;letter-spacing:.06em;color:#6c757d;">
                                    {{ $modLabel }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="ps-3 text-muted">{{ $actLabel }}</td>
                            @foreach($roles as $role)
                            <td class="text-center">
                                @php
                                    // For the Panel row: check if role has EITHER panel.admin or panel.author
                                    $hasPerm = $perm->name === 'panel.author'
                                        ? (isset($rolePermMap[$role->name]['panel.admin']) || isset($rolePermMap[$role->name]['panel.author']))
                                        : isset($rolePermMap[$role->name][$perm->name]);
                                @endphp
                                @if($hasPerm)
                                    <i class="fas fa-check-circle" style="color:{{ $role->color ?? '#6c757d' }};"></i>
                                @else
                                    <i class="fas fa-times-circle text-muted opacity-25"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="deleteRoleForm" method="POST" action="" class="d-none">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
window.confirmDelete = function (id, name, userCount) {
    if (userCount > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Cannot delete "' + name + '"',
            text: userCount + ' user(s) are assigned this role. Reassign them before deleting.',
            confirmButtonText: 'OK',
        });
        return;
    }
    Swal.fire({
        title: 'Delete "' + name + '"?',
        text: 'This role and all its permission assignments will be permanently removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete it',
    }).then(function (result) {
        if (result.isConfirmed) {
            var form = document.getElementById('deleteRoleForm');
            form.action = '{{ url("admin/roles") }}/' + id;
            form.submit();
        }
    });
};
</script>
@endpush
