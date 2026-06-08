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
    .avatar-upload-box img { width: 100%; height: 100%; object-fit: cover; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data"
      x-data="userEditForm()">
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
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="changePassword"
                                               x-model="changePassword">
                                        <label class="form-check-label small fw-semibold" for="changePassword">
                                            Change Password
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <template x-if="changePassword">
                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <div class="input-group">
                                            <input :type="showPassword ? 'text' : 'password'" name="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   minlength="8">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    @click="showPassword=!showPassword">
                                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm Password</label>
                                        <input :type="showPassword ? 'text' : 'password'" name="password_confirmation"
                                               class="form-control" minlength="8">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="role"
                                    class="form-select @error('role') is-invalid @enderror" required>
                                <option value="user"   {{ old('role', $user->role) === 'user'   ? 'selected' : '' }}>User</option>
                                <option value="author" {{ old('role', $user->role) === 'author' ? 'selected' : '' }}>Author</option>
                                <option value="editor" {{ old('role', $user->role) === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="admin"  {{ old('role', $user->role) === 'admin'  ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active"   {{ old('status', $user->status) === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="banned"   {{ old('status', $user->status) === 'banned'   ? 'selected' : '' }}>Banned</option>
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
                    <input type="file" name="avatar" class="d-none" accept="image/*"
                           x-ref="avatarInput" @change="previewAvatar($event)">
                    <input type="hidden" name="remove_avatar" x-bind:value="removeAvatar ? '1' : ''">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                @click="$refs.avatarInput.click()">
                            <i class="fas fa-upload me-1"></i>Change
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" x-show="avatarPreview"
                                @click="avatarPreview=null; removeAvatar=true; $refs.avatarInput.value=''">
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
    function userEditForm() {
        return {
            showPassword: false,
            changePassword: false,
            avatarPreview: {{ $user->avatar ? '"' . asset('storage/' . $user->avatar) . '"' : 'null' }},
            removeAvatar: false,
            previewAvatar(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => {
                    this.avatarPreview = e.target.result;
                    this.removeAvatar = false;
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
