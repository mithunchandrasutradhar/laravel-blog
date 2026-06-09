@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center py-5">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5 text-center">

                    <div class="mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:72px;height:72px">
                            <i class="fas fa-envelope-open-text fa-2x text-warning"></i>
                        </div>
                    </div>

                    <h2 class="h4 fw-bold mb-2">Check Your Email</h2>
                    <p class="text-muted mb-4">
                        Thanks for signing up! Before you get started, please verify your email address
                        by clicking the link we just sent you.
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success py-2 small">
                            A new verification link has been sent to your email address.
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i> Resend Verification Email
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-sign-out-alt me-2"></i> Log Out
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
