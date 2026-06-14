@extends('layouts.app')

@php
    $seo = [
        'title'     => 'Terms of Service — ' . settings('site_name', config('app.name')),
        'canonical' => route('terms'),
    ];
@endphp

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Terms of Service', 'url' => route('terms')],
                ]
            ])

            <h1 class="h2 fw-bold mb-1 mt-3">Terms of Service</h1>
            <p class="text-muted small mb-4">
                Last updated:
                @if($lastUpdated)
                    {{ \Carbon\Carbon::parse($lastUpdated)->format('F j, Y') }}
                @else
                    {{ now()->format('F j, Y') }}
                @endif
            </p>

            <div class="post-content">
                @if($content)
                    {!! $content !!}
                @else
                    <p class="text-muted fst-italic">Terms of service content has not been added yet.</p>
                    @auth
                        @if(is_admin())
                        <a href="{{ route('admin.settings.index') }}?tab=pages" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-edit me-1"></i>Add Content in Admin Settings
                        </a>
                        @endif
                    @endauth
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
