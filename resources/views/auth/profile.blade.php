@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<div class="py-5" style="background:#f8f9fc;min-height:80vh;">
<div class="container" style="max-width:780px;">

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">My Profile</h1>
        <p class="text-muted mb-0">Manage your account information and password</p>
    </div>

    {{-- Status alerts --}}
    @if(session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>Profile updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Profile Info ── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:1rem;">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-4" style="font-size:1rem;">
                <i class="fas fa-user me-2" style="color:#4f46e5;"></i>Profile Information
            </h5>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PATCH')

                {{-- Avatar --}}
                <div class="d-flex align-items-center gap-4 mb-4">
                    <div class="position-relative" style="flex-shrink:0;">
                        @if($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}"
                                 id="avatarPreview"
                                 class="rounded-circle"
                                 style="width:80px;height:80px;object-fit:cover;border:3px solid #e5e7eb;">
                        @else
                            <div id="avatarPreview"
                                 class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                 style="width:80px;height:80px;font-size:1.75rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);border:3px solid #e5e7eb;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <label for="profile_image" class="btn btn-outline-secondary btn-sm mb-1" style="cursor:pointer;">
                            <i class="fas fa-camera me-1"></i>Change Photo
                        </label>
                        <input type="file" id="profile_image" name="profile_image"
                               class="d-none @error('profile_image') is-invalid @enderror"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="previewAvatar(this)">
                        <p class="text-muted mb-0" style="font-size:.75rem;">JPG, PNG or WebP · max 2 MB</p>
                        @error('profile_image')
                        <div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Full Name</label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="text-warning mt-1 mb-0" style="font-size:.8rem;">
                        <i class="fas fa-exclamation-triangle me-1"></i>Email not verified.
                        <button type="button" class="btn btn-link btn-sm p-0 text-warning text-decoration-underline"
                                onclick="resendVerificationEmail(this)">Resend verification email</button>
                    </p>
                    <div id="verificationSentAlert" class="d-none mt-2 d-flex align-items-center gap-2 px-3 py-2 rounded-2"
                         style="background:#d1fae5;color:#065f46;font-size:.82rem;">
                        <i class="fas fa-check-circle"></i>
                        Verification email sent! Please check your inbox (and spam folder).
                    </div>
                    @endif
                </div>

                {{-- Bio --}}
                <div class="mb-4">
                    <label for="bio" class="form-label fw-semibold">Bio <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea id="bio" name="bio" rows="3"
                              class="form-control @error('bio') is-invalid @enderror"
                              placeholder="Tell readers a little about yourself…">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>

            </form>


        </div>
    </div>

    {{-- ── Change Password ── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:1rem;">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-4" style="font-size:1rem;">
                <i class="fas fa-lock me-2" style="color:#4f46e5;"></i>Change Password
            </h5>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PATCH')
                {{-- Keep name/email so validation passes --}}
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="mb-3">
                    <label for="current_password" class="form-label fw-semibold">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           class="form-control @error('current_password') is-invalid @enderror"
                           autocomplete="current-password">
                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label fw-semibold">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           class="form-control @error('new_password') is-invalid @enderror"
                           autocomplete="new-password">
                    @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="new_password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                           class="form-control" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fas fa-key me-2"></i>Update Password
                </button>

            </form>

        </div>
    </div>

    {{-- ── Delete Account ── --}}
    <div class="card border-0 shadow-sm" style="border-radius:1rem;border:1px solid #fee2e2 !important;">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-1" style="font-size:1rem;color:#dc2626;">
                <i class="fas fa-trash me-2"></i>Delete Account
            </h5>
            <p class="text-muted small mb-4">Once deleted, all your data is permanently removed. This action cannot be undone.</p>

            <button type="button" class="btn btn-outline-danger btn-sm px-4 fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                Delete My Account
            </button>

        </div>
    </div>

</div>
</div>

{{-- Delete Account Modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete Account?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">This will permanently delete your account, posts, and all associated data. Enter your password to confirm.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm">
                    @csrf @method('DELETE')
                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-semibold">Your Password</label>
                        <input type="password" id="delete_password" name="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        @error('password', 'userDeletion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteAccountForm" class="btn btn-danger px-4 fw-semibold">
                    <i class="fas fa-trash me-1"></i>Delete My Account
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            // Replace div with img if it's the fallback initial
            if (preview.tagName === 'DIV') {
                const img = document.createElement('img');
                img.id = 'avatarPreview';
                img.className = 'rounded-circle';
                img.style.cssText = 'width:80px;height:80px;object-fit:cover;border:3px solid #e5e7eb;';
                preview.replaceWith(img);
            }
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function resendVerificationEmail(btn) {
    btn.disabled = true;
    btn.textContent = 'Sending…';

    fetch('{{ route('verification.send') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        redirect: 'follow',
    })
    .then(() => {
        document.getElementById('verificationSentAlert').classList.remove('d-none');
        btn.textContent = 'Email sent';
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Resend verification email';
    });
}

// Open delete modal if there were validation errors for it
@if($errors->userDeletion->isNotEmpty())
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
    });
@endif
</script>
@endpush

@endsection
