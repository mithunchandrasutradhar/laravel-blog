@extends('admin.layouts.admin')

@section('title', 'Add User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-decoration-none">Users</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('page-title', 'Add New User')

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
    .avatar-upload-box img { width: 100%; height: 100%; object-fit: cover; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data"
      x-data="userForm()">
    @csrf

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
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       x-ref="pwInput"
                                       class="form-control @error('password') is-invalid @enderror"
                                       minlength="8" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary px-3"
                                        @click="togglePw()" title="Toggle visibility">
                                    <i class="fas fa-eye pw-eye-icon"></i>
                                </button>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                            <input type="password" name="password_confirmation"
                                   id="password_confirmation"
                                   x-ref="pwConfirm"
                                   class="form-control" minlength="8" required autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary px-3"
                                    @click="togglePw()" title="Toggle visibility">
                                <i class="fas fa-eye pw-eye-icon"></i>
                            </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="role"
                                    class="form-select @error('role') is-invalid @enderror" required>
                                <option value="user"   {{ old('role') === 'user'   ? 'selected' : '' }}>User</option>
                                <option value="author" {{ old('role') === 'author' ? 'selected' : '' }}>Author</option>
                                <option value="editor" {{ old('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="admin"  {{ old('role') === 'admin'  ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active"   {{ old('status','active') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="banned"   {{ old('status') === 'banned'   ? 'selected' : '' }}>Banned</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="bio" class="form-label fw-semibold">Bio</label>
                            <textarea name="bio" id="bio"
                                      class="form-control @error('bio') is-invalid @enderror"
                                      rows="3" placeholder="Short biography...">{{ old('bio') }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <div class="avatar-upload-box mb-3" @click="$refs.avatarInput.click()">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="Avatar">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="text-muted">
                                <i class="fas fa-user fa-2x d-block mb-1"></i>
                                <span style="font-size:.7rem;">Upload</span>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="profile_image" class="d-none" accept="image/*"
                           x-ref="avatarInput" @change="previewAvatar($event)">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                @click="$refs.avatarInput.click()">
                            <i class="fas fa-upload me-1"></i>Upload
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" x-show="avatarPreview"
                                @click="avatarPreview=null; $refs.avatarInput.value=''">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="form-text mt-2">Recommended: 200×200px, JPG/PNG.</div>
                </div>
            </div>

            {{-- Save --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-user-plus me-2"></i>Create User
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
    function userForm() {
        return {
            showPassword: false,
            avatarPreview: null,

            togglePw() {
                this.showPassword = !this.showPassword;
                const type = this.showPassword ? 'text' : 'password';
                const icon = this.showPassword ? 'fas fa-eye-slash pw-eye-icon' : 'fas fa-eye pw-eye-icon';
                if (this.$refs.pwInput)   this.$refs.pwInput.type   = type;
                if (this.$refs.pwConfirm) this.$refs.pwConfirm.type = type;
                document.querySelectorAll('.pw-eye-icon').forEach(el => el.className = icon);
            },

            previewAvatar(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => this.avatarPreview = e.target.result;
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
