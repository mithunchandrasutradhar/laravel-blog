@extends('layouts.app')
@section('title', '419 – Page Expired')
@section('content')
<div class="container py-5 text-center">
    <div class="py-5">
        <h1 class="display-1 fw-bold text-secondary">419</h1>
        <h2 class="mb-3">Page Expired</h2>
        <p class="text-muted mb-4">Your session has expired. Please refresh and try again.</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary px-4">Go Back</a>
    </div>
</div>
@endsection
