@extends('layouts.auth')

@php $title = 'Sign In'; @endphp

@section('content')

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">Welcome back</h1>
        <p class="text-muted small">Sign in to your account to continue</p>
    </div>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-medium">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" id="email"
                       class="form-control border-start-0 @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autocomplete="email" autofocus>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label fw-medium mb-0">Password</label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary">Forgot password?</a>
                @endif
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" id="password"
                       class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                       placeholder="••••••••"
                       required autocomplete="current-password">
                <button type="button" class="input-group-text bg-white border-start-0 password-toggle-btn" data-target="password" aria-label="Toggle password visibility">
                    <i class="fas fa-eye text-muted" id="passwordToggleIcon"></i>
                </button>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Remember Me --}}
        <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
            <i class="fas fa-sign-in-alt me-2"></i>Sign In
        </button>

        {{-- Divider --}}
        @if(settings('social_login_enabled'))
        <div class="divider text-center my-4 position-relative">
            <hr>
            <span class="divider-text bg-white px-3 text-muted small position-absolute top-50 start-50 translate-middle">or continue with</span>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('auth.social', 'google') }}" class="btn btn-outline-secondary flex-grow-1">
                <i class="fab fa-google me-1"></i> Google
            </a>
            <a href="{{ route('auth.social', 'github') }}" class="btn btn-outline-secondary flex-grow-1">
                <i class="fab fa-github me-1"></i> GitHub
            </a>
        </div>
        @endif
    </form>

@endsection

@section('auth-footer')
    <p class="text-muted small">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Create one free</a>
    </p>
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
