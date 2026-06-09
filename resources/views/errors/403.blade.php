@extends('layouts.app')
@section('title', '403 – Forbidden')
@section('content')
<div class="container py-5 text-center">
    <div class="py-5">
        <h1 class="display-1 fw-bold text-warning">403</h1>
        <h2 class="mb-3">Access Denied</h2>
        <p class="text-muted mb-4">You don't have permission to access this page.</p>
        <a href="{{ url('/') }}" class="btn btn-primary px-4">Go Home</a>
    </div>
</div>
@endsection
