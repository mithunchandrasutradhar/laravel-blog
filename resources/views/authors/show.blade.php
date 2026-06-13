@extends('layouts.app')

@php
    $seo = [
        'title'       => $author->name . ' — Author at ' . settings('site_name', config('app.name')),
        'description' => $author->bio ?? 'Read articles by ' . $author->name . ' on ' . settings('site_name', config('app.name')),
        'og_image'    => $author->avatar ? asset($author->avatar) : null,
        'canonical'   => route('authors.show', $author->username),
    ];
@endphp

@section('content')

    {{-- Author Hero --}}
    <div class="author-hero py-5" style="background: linear-gradient(135deg,var(--brand-primary-light,#e8f0fe) 0%,#fff 100%); border-bottom:1px solid rgba(0,0,0,.08);">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Blog',    'url' => route('blog.index')],
                    ['label' => $author->name, 'url' => route('authors.show', $author->username)],
                ]
            ])
            <div class="row align-items-center mt-4 g-4">
                {{-- Avatar --}}
                <div class="col-auto">
                    @if($author->avatar)
                    <img src="{{ asset($author->avatar) }}" alt="{{ $author->name }}" class="rounded-circle shadow" width="120" height="120" style="object-fit:cover;">
                    @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow fw-bold" style="width:120px;height:120px;font-size:48px;">
                        {{ strtoupper(substr($author->name, 0, 1)) }}
                    </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="col">
                    <h1 class="h2 fw-bold mb-1">{{ $author->name }}</h1>
                    @if($author->title)
                    <p class="text-muted fw-medium mb-2">{{ $author->title }}</p>
                    @endif
                    @if($author->bio)
                    <p class="text-muted mb-3" style="max-width:600px;">{{ $author->bio }}</p>
                    @endif

                    {{-- Stats --}}
                    <div class="d-flex gap-4 mb-3 flex-wrap">
                        <div class="text-center text-sm-start">
                            <div class="h5 fw-bold mb-0">{{ number_format($posts->total()) }}</div>
                            <small class="text-muted">{{ Str::plural('Post', $posts->total()) }}</small>
                        </div>
                        @if($author->total_views)
                        <div class="text-center text-sm-start">
                            <div class="h5 fw-bold mb-0">{{ number_format($author->total_views) }}</div>
                            <small class="text-muted">Total Views</small>
                        </div>
                        @endif
                        @if($author->created_at)
                        <div class="text-center text-sm-start">
                            <div class="h5 fw-bold mb-0">{{ $author->created_at->format('Y') }}</div>
                            <small class="text-muted">Joined</small>
                        </div>
                        @endif
                    </div>

                    {{-- Social Links --}}
                    <div class="author-social d-flex gap-3 flex-wrap">
                        @if($author->website)
                        <a href="{{ $author->website }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-globe me-1"></i>Website
                        </a>
                        @endif
                        @if($author->social_twitter)
                        <a href="{{ $author->social_twitter }}" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-x-twitter me-1"></i>Twitter
                        </a>
                        @endif
                        @if($author->social_linkedin)
                        <a href="{{ $author->social_linkedin }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-linkedin-in me-1"></i>LinkedIn
                        </a>
                        @endif
                        @if($author->social_github)
                        <a href="{{ $author->social_github }}" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-github me-1"></i>GitHub
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Posts Grid --}}
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-8">
                <h2 class="h5 fw-bold mb-4">
                    Articles by {{ $author->name }}
                </h2>

                @if($posts->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-alt fa-3x mb-3 opacity-25"></i>
                    <h3 class="h5">No articles yet</h3>
                </div>
                @else
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-sm-6">
                        @include('partials.post-card', ['post' => $post])
                    </div>
                    @endforeach
                </div>

                @include('partials.pagination', ['paginator' => $posts])
                @endif
            </div>

            <div class="col-lg-4">
                @include('partials.sidebar')
            </div>
        </div>
    </div>

@endsection
