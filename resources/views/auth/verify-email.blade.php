@extends('layouts.auth')

@php $title = 'Verify Your Email'; @endphp

@section('content')

<div class="text-center">

    {{-- Animated icon --}}
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
             style="width:88px;height:88px;background:linear-gradient(135deg,#fff8e7 0%,#ffeeba 100%);box-shadow:0 8px 24px rgba(255,193,7,.25);">
            <i class="fas fa-envelope-open-text fa-2x" style="color:#f59e0b;"></i>
        </div>
    </div>

    <h1 class="h4 fw-bold mb-2">Check Your Email</h1>
    <p class="text-muted mb-1" style="font-size:.9rem;line-height:1.6;">
        Thanks for signing up! We've sent a verification link to your email address.
        Click the link to activate your account.
    </p>
    <p class="text-muted mb-4" style="font-size:.82rem;">
        Didn't receive it? Check your <strong>spam folder</strong> or resend below.
    </p>

    {{-- Success alert --}}
    @if(session('status') === 'verification-link-sent')
    <div class="alert border-0 py-2 px-3 mb-4 text-start d-flex align-items-center gap-2"
         style="background:#d1fae5;color:#065f46;border-radius:.6rem;font-size:.85rem;">
        <i class="fas fa-check-circle text-success"></i>
        A new verification link has been sent to your email address.
    </div>
    @endif

    {{-- Actions --}}
    <div class="d-grid gap-2 mb-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fas fa-paper-plane me-2"></i>Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Log Out
            </button>
        </form>
    </div>

    {{-- Divider + steps hint --}}
    <div class="border-top pt-4">
        <p class="text-muted mb-3" style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">How it works</p>
        <div class="d-flex justify-content-center gap-4 text-muted" style="font-size:.8rem;">
            <div class="text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-1"
                     style="width:32px;height:32px;"><i class="fas fa-envelope text-primary" style="font-size:.7rem;"></i></div>
                <div>Check inbox</div>
            </div>
            <div class="text-center" style="padding-top:4px;">
                <i class="fas fa-chevron-right text-muted opacity-50"></i>
            </div>
            <div class="text-center">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-1"
                     style="width:32px;height:32px;"><i class="fas fa-mouse-pointer text-success" style="font-size:.7rem;"></i></div>
                <div>Click the link</div>
            </div>
            <div class="text-center" style="padding-top:4px;">
                <i class="fas fa-chevron-right text-muted opacity-50"></i>
            </div>
            <div class="text-center">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-1"
                     style="width:32px;height:32px;"><i class="fas fa-check text-warning" style="font-size:.7rem;"></i></div>
                <div>You're in!</div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('auth-footer')
<a href="{{ route('home') }}" class="text-muted small text-decoration-none">
    <i class="fas fa-arrow-left me-1"></i>Back to Home
</a>
@endsection
