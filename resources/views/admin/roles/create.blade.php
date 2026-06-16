@extends('admin.layouts.admin')

@section('title', 'Create Role')
@section('page-title', 'Create New Role')
@section('page-subtitle', 'Define a custom role and assign permissions')

@section('page-actions')
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Roles
    </a>
@endsection

@push('styles')
@include('admin.roles.partials.matrix-styles')
@endpush

@section('content')

@if($errors->any())
<div class="alert alert-danger mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.roles.store') }}" id="rolesForm">
    @csrf

    {{-- ── Role Details Card ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-id-badge text-primary me-2"></i>Role Details</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Display Name <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" id="displayName"
                           class="form-control @error('display_name') is-invalid @enderror"
                           placeholder="e.g. Content Manager"
                           value="{{ old('display_name') }}"
                           maxlength="100" required
                           oninput="updateSlug(this.value)">
                    @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Role Slug <span class="text-muted">(auto-generated)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted" style="font-size:.85rem;">
                            <i class="fas fa-key me-1"></i>
                        </span>
                        <input type="text" id="slugPreview" class="form-control bg-light text-muted"
                               placeholder="content_manager" readonly>
                    </div>
                    <div class="form-text">Used internally. Cannot be changed after creation.</div>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Description</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="2" placeholder="Brief description of what this role can do..."
                              maxlength="500">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Role Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="color" id="colorPicker"
                               class="form-control form-control-color"
                               value="{{ old('color', '#0d6efd') }}"
                               style="width:48px;height:38px;padding:2px;"
                               oninput="updateColor(this.value)">
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach(['#0d6efd','#6f42c1','#20c997','#fd7e14','#dc3545','#198754','#0dcaf0','#6c757d'] as $preset)
                            <button type="button" class="border-0 rounded-circle preset-color"
                                    onclick="setColor('{{ $preset }}')"
                                    style="width:22px;height:22px;background:{{ $preset }};cursor:pointer;"
                                    title="{{ $preset }}"></button>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-text">Colour used for role badge and highlights.</div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Permission Matrix ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-shield-alt text-success me-2"></i>Assign Permissions</h6>
        </div>
        <div class="card-body">
            @php $roleColor = old('color', '#0d6efd'); $submitLabel = 'Create Role'; @endphp
            @include('admin.roles.partials.permission-matrix')
        </div>
    </div>

</form>

@endsection

@push('scripts')
@include('admin.roles.partials.matrix-scripts')
<script>
function updateSlug(value) {
    var slug = value.toLowerCase()
        .replace(/[^a-z0-9\s_]/g, '')
        .trim()
        .replace(/\s+/g, '_');
    document.getElementById('slugPreview').value = slug || '';
    // Update role colour preview if colour hasn't been changed manually
}

function updateColor(hex) {
    document.documentElement.style.setProperty('--role-color', hex);
    document.documentElement.style.setProperty('--role-bg', hex + '18');
    document.getElementById('saveBtn').style.background = hex;
}

function setColor(hex) {
    document.getElementById('colorPicker').value = hex;
    updateColor(hex);
}

// Init slug from old() value if present
document.addEventListener('DOMContentLoaded', function () {
    var displayName = document.getElementById('displayName').value;
    if (displayName) updateSlug(displayName);
});
</script>
@endpush
