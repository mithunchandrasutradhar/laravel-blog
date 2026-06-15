@extends('admin.layouts.admin')

@section('title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-decoration-none">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('page-title', 'Edit User')
@section('page-subtitle', $user->name ?? '')

@push('styles')
<style>
    .avatar-upload-box {
        border: 2px dashed #dee2e6;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        transition: border-color .2s;
        margin: 0 auto;
    }
    .avatar-upload-box:hover { border-color: #0d6efd; }
    #avatar-img { width: 100%; height: 100%; object-fit: cover; display: block; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data" id="userEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-xl-8">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Change Password toggle --}}
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               id="changePasswordCb" onchange="togglePwSection(this)">
                                        <label class="form-check-label small fw-semibold" for="changePasswordCb">
                                            Change Password
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Password fields (hidden until checkbox checked) --}}
                        <div class="col-12" id="pw-section" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="edit-pw"
                                               class="form-control @error('password') is-invalid @enderror"
                                               disabled minlength="8" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary px-3 pw-toggle-btn"
                                                title="Toggle visibility">
                                            <i class="fas fa-eye pw-eye-icon"></i>
                                        </button>
                                    </div>
                                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="edit-pw-confirm"
                                               class="form-control" disabled minlength="8" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary px-3 pw-toggle-btn"
                                                title="Toggle visibility">
                                            <i class="fas fa-eye pw-eye-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="role"
                                    class="form-select @error('role') is-invalid @enderror" required>
                                <option value="user"   {{ old('role', $currentRole) === 'user'   ? 'selected' : '' }}>User</option>
                                <option value="author" {{ old('role', $currentRole) === 'author' ? 'selected' : '' }}>Author</option>
                                <option value="editor" {{ old('role', $currentRole) === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="admin"  {{ old('role', $currentRole) === 'admin'  ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active"   {{ old('status', $user->status) === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="bio" class="form-label fw-semibold">Bio</label>
                            <textarea name="bio" id="bio"
                                      class="form-control @error('bio') is-invalid @enderror"
                                      rows="3">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- User Stats --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>User Stats</h6>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0 text-center">
                        <div class="col border-end py-3">
                            <div class="h5 fw-bold mb-0">{{ $user->posts_count ?? 0 }}</div>
                            <div class="text-muted small">Posts</div>
                        </div>
                        <div class="col border-end py-3">
                            <div class="h5 fw-bold mb-0">{{ $user->comments_count ?? 0 }}</div>
                            <div class="text-muted small">Comments</div>
                        </div>
                        <div class="col py-3">
                            <div class="h5 fw-bold mb-0">{{ $user->created_at->diffForHumans() }}</div>
                            <div class="text-muted small">Joined</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-xl-4">

            {{-- Profile Image --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user-circle text-primary me-2"></i>Profile Image</h6>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-upload-box mb-3" onclick="document.getElementById('avatar-file').click()">
                        @if($user->profile_image)
                        <img id="avatar-img" src="{{ asset('storage/' . $user->profile_image) }}" alt="Avatar">
                        @else
                        <img id="avatar-img" src="" alt="Avatar" style="display:none;">
                        @endif
                        <div id="avatar-placeholder" class="text-muted text-center" style="{{ $user->profile_image ? 'display:none;' : '' }}">
                            <i class="fas fa-user fa-2x d-block mb-1"></i>
                            <span style="font-size:.7rem;">Upload</span>
                        </div>
                    </div>
                    <input type="file" name="profile_image" id="avatar-file"
                           class="d-none" accept="image/*" onchange="previewAvatar(event)">
                    <input type="hidden" name="remove_avatar" id="remove-avatar-flag" value="">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="document.getElementById('avatar-file').click()">
                            <i class="fas fa-upload me-1"></i>Change
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                id="remove-avatar-btn"
                                onclick="removeAvatar()"
                                style="{{ $user->profile_image ? '' : 'display:none;' }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Save --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>Update User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100 btn-sm">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    var pwVisible = false;

    // Show / hide the password section when the checkbox is toggled
    window.togglePwSection = function (cb) {
        var section = document.getElementById('pw-section');
        var pw      = document.getElementById('edit-pw');
        var conf    = document.getElementById('edit-pw-confirm');

        if (cb.checked) {
            section.style.display = '';
            if (pw)   pw.disabled   = false;
            if (conf) conf.disabled = false;
        } else {
            section.style.display = 'none';
            if (pw)   { pw.disabled = true;   pw.value = '';   }
            if (conf) { conf.disabled = true; conf.value = ''; }
            // reset visibility state
            pwVisible = false;
            if (pw)   pw.type   = 'password';
            if (conf) conf.type = 'password';
            document.querySelectorAll('.pw-eye-icon').forEach(function (el) {
                el.className = 'fas fa-eye pw-eye-icon';
            });
        }
    };

    // Avatar preview
    window.previewAvatar = function (event) {
        var file = event.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            var img         = document.getElementById('avatar-img');
            var placeholder = document.getElementById('avatar-placeholder');
            var removeBtn   = document.getElementById('remove-avatar-btn');
            var removeFlag  = document.getElementById('remove-avatar-flag');
            img.src = e.target.result;
            img.style.display = '';
            if (placeholder) placeholder.style.display = 'none';
            if (removeBtn)   removeBtn.style.display   = '';
            if (removeFlag)  removeFlag.value           = '';
        };
        reader.readAsDataURL(file);
    };

    // Remove avatar
    window.removeAvatar = function () {
        var img         = document.getElementById('avatar-img');
        var placeholder = document.getElementById('avatar-placeholder');
        var removeBtn   = document.getElementById('remove-avatar-btn');
        var removeFlag  = document.getElementById('remove-avatar-flag');
        var fileInput   = document.getElementById('avatar-file');
        img.src = '';
        img.style.display = 'none';
        if (placeholder) placeholder.style.display = '';
        if (removeBtn)   removeBtn.style.display   = 'none';
        if (removeFlag)  removeFlag.value           = '1';
        if (fileInput)   fileInput.value            = '';
    };

    // Wire up eye-toggle buttons once DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.pw-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                pwVisible = !pwVisible;
                var type = pwVisible ? 'text' : 'password';
                var pw   = document.getElementById('edit-pw');
                var conf = document.getElementById('edit-pw-confirm');
                if (pw)   pw.type   = type;
                if (conf) conf.type = type;
                document.querySelectorAll('.pw-eye-icon').forEach(function (el) {
                    el.className = (pwVisible ? 'fas fa-eye-slash' : 'fas fa-eye') + ' pw-eye-icon';
                });
            });
        });
    });
}());
</script>
@endpush
