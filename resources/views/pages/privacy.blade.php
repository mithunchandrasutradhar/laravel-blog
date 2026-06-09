@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            @include('partials.breadcrumb', ['items' => [['label' => 'Privacy Policy']]])

            <h1 class="h2 fw-bold mb-1">Privacy Policy</h1>
            @if($lastUpdated)
                <p class="text-muted small mb-4">Last updated: {{ \Carbon\Carbon::parse($lastUpdated)->format('F j, Y') }}</p>
            @endif

            <div class="post-content">
                @if($content)
                    {!! $content !!}
                @else
                    <p>Our privacy policy will be published here. Please check back soon.</p>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
