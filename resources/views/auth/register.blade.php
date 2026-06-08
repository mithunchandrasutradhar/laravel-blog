@extends('layouts.auth')

@php $title = 'Create Account'; @endphp

@section('content')

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">Create your account</h1>
        <p class="text-muted small">Join {{ settings('site_name', config('app.name')) }} for free</p>
    </div>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        {{-- Name --}}
        <div class="mb-3">
            <label for="name" class="form-label fw-medium">Full Name</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="name" id="name"
                       class="form-control border-start-0 @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="John Doe"
                       required autocomplete="name" autofocus>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-medium">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" id="email"
                       class="form-control border-start-0 @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autocomplete="email">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label fw-medium">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" id="password"
                       class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                       placeholder="Min. 8 characters"
                       required autocomplete="new-password"
                       minlength="8">
                <button type="button" class="input-group-text bg-white border-start-0 password-toggle-btn" data-target="password" aria-label="Toggle password">
                    <i class="fas fa-eye text-muted"></i>
                </button>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="password-strength-bar mt-1" id="passwordStrengthBar" style="display:none;">
                <div class="progress" style="height:3px;">
                    <div class="progress-bar" id="passwordStrengthIndicator" role="progressbar"></div>
                </div>
                <small class="text-muted" id="passwordStrengthText"></small>
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-medium">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control border-start-0 border-end-0"
                       placeholder="Repeat your password"
                       required autocomplete="new-password">
                <button type="button" class="input-group-text bg-white border-start-0 password-toggle-btn" data-target="password_confirmation" aria-label="Toggle confirm password">
                    <i class="fas fa-eye text-muted"></i>
                </button>
            </div>
        </div>

        {{-- Terms --}}
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" required>
                <label class="form-check-label small" for="terms">
                    I agree to the
                    @if(settings('terms_page'))
                    <a href="{{ route('page', settings('terms_page')) }}" class="text-primary" target="_blank" rel="noopener">Terms of Service</a>
                    @else
                    <span class="text-primary">Terms of Service</span>
                    @endif
                    and
                    @if(settings('privacy_policy_page'))
                    <a href="{{ route('page', settings('privacy_policy_page')) }}" class="text-primary" target="_blank" rel="noopener">Privacy Policy</a>
                    @else
                    <span class="text-primary">Privacy Policy</span>
                    @endif
                </label>
                @error('terms')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
            <i class="fas fa-user-plus me-2"></i>Create Account
        </button>
    </form>

@endsection

@section('auth-footer')
    <p class="text-muted small">
        Already have an account?
        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign in</a>
    </p>
@endsection

@push('scripts')
<script>
// Password toggle
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

// Password strength indicator
const pwInput = document.getElementById('password');
const strengthBar = document.getElementById('passwordStrengthBar');
const strengthIndicator = document.getElementById('passwordStrengthIndicator');
const strengthText = document.getElementById('passwordStrengthText');

pwInput?.addEventListener('input', function() {
    const val = this.value;
    if (!val) { strengthBar.style.display = 'none'; return; }
    strengthBar.style.display = 'block';

    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: 25,  cls: 'bg-danger',  text: 'Weak' },
        { pct: 50,  cls: 'bg-warning', text: 'Fair' },
        { pct: 75,  cls: 'bg-info',    text: 'Good' },
        { pct: 100, cls: 'bg-success', text: 'Strong' },
    ];
    const level = levels[Math.max(0, score - 1)] || levels[0];

    strengthIndicator.style.width = level.pct + '%';
    strengthIndicator.className = 'progress-bar ' + level.cls;
    strengthText.textContent = level.text;
});
</script>
@endpush
