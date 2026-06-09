@extends('layouts.app')
@section('title', '404 – Page Not Found')
@section('content')
<div class="container py-5 text-center">
    <div class="py-5">
        <h1 class="display-1 fw-bold text-primary">404</h1>
        <h2 class="mb-3">Page Not Found</h2>
        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ url('/') }}" class="btn btn-primary px-4">Go Home</a>
        <a href="{{ url('/blog') }}" class="btn btn-outline-primary px-4 ms-2">Browse Blog</a>
    </div>
</div>
@endsection
