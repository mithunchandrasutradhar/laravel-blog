@extends('layouts.app')

@php
    $seo = ['title' => 'Edit Profile — ' . settings('site_name', config('app.name')), 'robots' => 'noindex'];
@endphp

@section('content')

    <div class="page-header bg-light border-bottom py-4">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Profile', 'url' => route('profile.edit')],
                    ['label' => 'Edit Profile', 'url' => route('profile.edit')],
                ]
            ])
            <h1 class="h3 fw-bold mt-2 mb-0">Edit Profile</h1>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5 justify-content-center">

            {{-- Left: Avatar --}}
            <div class="col-lg-3 col-md-4">
                <div class="card border-0 shadow-sm p-4 text-center sticky-lg-top" style="top:90px;">
                    {{-- Current Avatar --}}
                    <div class="avatar-wrapper mb-3 position-relative d-inline-block mx-auto">
                        @if(auth()->user()->avatar)
                        <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" id="avatarPreview" width="100" height="100" style="object-fit:cover;">
                        @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto fw-bold" id="avatarPreviewPlaceholder" style="width:100px;height:100px;font-size:40px;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <img src="" alt="" class="rounded-circle d-none" id="avatarPreview" width="100" height="100" style="object-fit:cover;">
                        @endif
                    </div>

                    <div class="fw-bold mb-1">{{ auth()->user()->name }}</div>
                    <div class="text-muted small mb-3">{{ auth()->user()->email }}</div>

                    {{-- Avatar Upload Form --}}
                    <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                        @csrf
                        <label for="avatar" class="btn btn-outline-primary btn-sm w-100 cursor-pointer">
                            <i class="fas fa-camera me-1"></i>Change Photo
                        </label>
                        <input type="file" name="avatar" id="avatar" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp">
                    </form>
                    <small class="text-muted d-block mt-2">JPG, PNG or WEBP. Max 2MB.</small>

                    @if(auth()->user()->avatar)
                    <form action="{{ route('profile.avatar.remove') }}" method="POST" class="mt-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger btn-sm p-0" onclick="return confirm('Remove your avatar?')">
                            <i class="fas fa-trash me-1"></i>Remove Photo
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Right: Forms --}}
            <div class="col-lg-7 col-md-8">

                {{-- Email verification notice --}}
                @if(!auth()->user()->hasVerifiedEmail())
                <div class="alert d-flex align-items-start gap-3 mb-4 border-0 rounded-3"
                     style="background:#fff8e1;color:#7c5300;">
                    <i class="fas fa-exclamation-triangle mt-1" style="color:#f59e0b;flex-shrink:0;"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1">Email not verified</div>
                        <div class="small mb-2">
                            Please verify <strong>{{ auth()->user()->email }}</strong> to unlock all features.
                            Check your inbox or click below to resend the link.
                        </div>
                        <div id="verificationAlert" class="d-none d-flex align-items-center gap-2 p-2 rounded-2 mb-2"
                             style="background:#d1fae5;color:#065f46;font-size:.85rem;">
                            <i class="fas fa-check-circle"></i>
                            Verification email sent! Please check your inbox (and spam folder).
                        </div>
                        <button type="button" id="resendVerificationBtn"
                                class="btn btn-warning btn-sm fw-semibold"
                                onclick="resendVerificationEmail(this)">
                            <i class="fas fa-paper-plane me-1"></i>Resend Verification Email
                        </button>
                    </div>
                </div>
                @endif

                {{-- Profile Info --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="fas fa-user me-2 text-primary"></i>Personal Information
                        </h2>
                        <form action="{{ route('profile.update') }}" method="POST" novalidate>
                            @csrf @method('PATCH')

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="profile_name" class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="profile_name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', auth()->user()->name) }}"
                                           required autocomplete="name">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="profile_username" class="form-label fw-medium">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted">@</span>
                                        <input type="text" name="username" id="profile_username"
                                               class="form-control border-start-0 @error('username') is-invalid @enderror"
                                               value="{{ old('username', auth()->user()->username) }}"
                                               placeholder="yourhandle"
                                               autocomplete="username">
                                    </div>
                                    @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="profile_email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="profile_email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', auth()->user()->email) }}"
                                           required autocomplete="email">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="profile_title" class="form-label fw-medium">Job Title / Role</label>
                                    <input type="text" name="title" id="profile_title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title', auth()->user()->title) }}"
                                           placeholder="e.g. Software Engineer">
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label for="profile_bio" class="form-label fw-medium">Bio</label>
                                    <textarea name="bio" id="profile_bio" rows="4"
                                              class="form-control @error('bio') is-invalid @enderror"
                                              placeholder="Tell the world a little about yourself..."
                                              maxlength="500">{{ old('bio', auth()->user()->bio) }}</textarea>
                                    <div class="form-text d-flex justify-content-between">
                                        <span>A brief description for your public profile.</span>
                                        <span id="bioCharCount">{{ strlen(old('bio', auth()->user()->bio ?? '')) }}/500</span>
                                    </div>
                                    @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="profile_website" class="form-label fw-medium">Website</label>
                                    <input type="url" name="website" id="profile_website"
                                           class="form-control @error('website') is-invalid @enderror"
                                           value="{{ old('website', auth()->user()->website) }}"
                                           placeholder="https://yourwebsite.com">
                                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="profile_location" class="form-label fw-medium">Location</label>
                                    <input type="text" name="location" id="profile_location"
                                           class="form-control @error('location') is-invalid @enderror"
                                           value="{{ old('location', auth()->user()->location) }}"
                                           placeholder="e.g. New York, USA">
                                    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Social Links --}}
                            <hr class="my-4">
                            <h3 class="h6 fw-bold mb-3">
                                <i class="fas fa-share-alt me-2 text-primary"></i>Social Profiles
                            </h3>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="social_twitter" class="form-label fw-medium">
                                        <i class="fab fa-x-twitter me-1 text-dark"></i>Twitter / X
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted small">x.com/</span>
                                        <input type="text" name="social_twitter" id="social_twitter"
                                               class="form-control border-start-0"
                                               value="{{ old('social_twitter', auth()->user()->social_twitter ? ltrim(parse_url(auth()->user()->social_twitter, PHP_URL_PATH), '/') : '') }}"
                                               placeholder="username">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="social_linkedin" class="form-label fw-medium">
                                        <i class="fab fa-linkedin me-1 text-primary"></i>LinkedIn
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted small">linkedin.com/in/</span>
                                        <input type="text" name="social_linkedin" id="social_linkedin"
                                               class="form-control border-start-0"
                                               value="{{ old('social_linkedin', auth()->user()->social_linkedin ? ltrim(parse_url(auth()->user()->social_linkedin, PHP_URL_PATH), '/in/') : '') }}"
                                               placeholder="username">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="social_github" class="form-label fw-medium">
                                        <i class="fab fa-github me-1 text-dark"></i>GitHub
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted small">github.com/</span>
                                        <input type="text" name="social_github" id="social_github"
                                               class="form-control border-start-0"
                                               value="{{ old('social_github', auth()->user()->social_github ? ltrim(parse_url(auth()->user()->social_github, PHP_URL_PATH), '/') : '') }}"
                                               placeholder="username">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="social_instagram" class="form-label fw-medium">
                                        <i class="fab fa-instagram me-1 text-danger"></i>Instagram
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted small">instagram.com/</span>
                                        <input type="text" name="social_instagram" id="social_instagram"
                                               class="form-control border-start-0"
                                               value="{{ old('social_instagram', auth()->user()->social_instagram ? ltrim(parse_url(auth()->user()->social_instagram, PHP_URL_PATH), '/') : '') }}"
                                               placeholder="username">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Change Password --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-4">
                            <i class="fas fa-shield-alt me-2 text-danger"></i>Change Password
                        </h2>

                        <form action="{{ route('profile.password') }}" method="POST" novalidate>
                            @csrf @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-medium">Current Password</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       required autocomplete="current-password">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-medium">New Password</label>
                                <input type="password" name="password" id="new_password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="new-password" minlength="8"
                                       placeholder="Min. 8 characters">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="new_password_confirmation" class="form-label fw-medium">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="new_password_confirmation"
                                       class="form-control"
                                       required autocomplete="new-password"
                                       placeholder="Repeat new password">
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-danger px-4 fw-semibold">
                                    <i class="fas fa-lock me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
// Avatar preview on select
document.getElementById('avatar')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    const preview = document.getElementById('avatarPreview');
    const placeholder = document.getElementById('avatarPreviewPlaceholder');

    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.classList.remove('d-none');
        placeholder?.classList.add('d-none');
    };
    reader.readAsDataURL(file);

    // Auto-submit avatar form
    document.getElementById('avatarForm').submit();
});

// Bio char count
document.getElementById('profile_bio')?.addEventListener('input', function() {
    document.getElementById('bioCharCount').textContent = this.value.length + '/500';
});

// Resend verification email via AJAX
function resendVerificationEmail(btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('{{ route('verification.send') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        redirect: 'follow',
    })
    .then(() => {
        document.getElementById('verificationAlert').classList.remove('d-none');
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Email Sent';
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Resend Verification Email';
    });
}
</script>
@endpush
