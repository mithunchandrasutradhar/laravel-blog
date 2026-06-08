@extends('layouts.auth')

@php $title = 'Reset Password'; @endphp

@section('content')

    <div class="text-center mb-4">
        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
            <i class="fas fa-lock text-success fs-4"></i>
        </div>
        <h1 class="h4 fw-bold mb-1">Set new password</h1>
        <p class="text-muted small">Choose a strong password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email (hidden, pre-filled) --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-medium">Email Address</label>
            <input type="email" name="email" id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $request->email) }}"
                   required autocomplete="email" readonly>
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- New Password --}}
        <div class="mb-3">
            <label for="password" class="form-label fw-medium">New Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" id="password"
                       class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                       placeholder="Min. 8 characters"
                       required autocomplete="new-password"
                       minlength="8" autofocus>
                <button type="button" class="input-group-text bg-white border-start-0 password-toggle-btn" data-target="password" aria-label="Toggle password">
                    <i class="fas fa-eye text-muted"></i>
                </button>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-medium">Confirm New Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control border-start-0 border-end-0"
                       placeholder="Repeat your new password"
                       required autocomplete="new-password">
                <button type="button" class="input-group-text bg-white border-start-0 password-toggle-btn" data-target="password_confirmation" aria-label="Toggle confirm password">
                    <i class="fas fa-eye text-muted"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100 fw-semibold py-2">
            <i class="fas fa-check me-2"></i>Reset Password
        </button>
    </form>

@endsection

@section('auth-footer')
    <a href="{{ route('login') }}" class="text-muted text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i>Back to Sign In
    </a>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.password-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        const icon   = this.querySelector('i');
        if (target.type === 'password') {
            target.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            target.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});
</script>
@endpush
