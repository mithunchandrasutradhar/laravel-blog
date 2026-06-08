@extends('layouts.auth')

@php $title = 'Forgot Password'; @endphp

@section('content')

    <div class="text-center mb-4">
        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
            <i class="fas fa-key text-warning fs-4"></i>
        </div>
        <h1 class="h4 fw-bold mb-1">Forgot your password?</h1>
        <p class="text-muted small">No worries! Enter your email and we'll send you a reset link.</p>
    </div>

    @if(session('status'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-check-circle"></i>
        <div>{{ session('status') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
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

        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
        </button>
    </form>

@endsection

@section('auth-footer')
    <a href="{{ route('login') }}" class="text-muted text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i>Back to Sign In
    </a>
@endsection
