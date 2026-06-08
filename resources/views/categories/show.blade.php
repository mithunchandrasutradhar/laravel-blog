@extends('layouts.app')

@php
    $seo = [
        'title'       => $category->seo_title ?? $category->name . ' — ' . settings('site_name', config('app.name')),
        'description' => $category->seo_description ?? $category->description,
        'og_image'    => $category->image ? asset($category->image) : null,
        'canonical'   => route('categories.show', $category->slug),
    ];
@endphp

@section('content')

    {{-- Category Hero --}}
    <div class="category-hero py-5" style="background: linear-gradient(135deg, {{ $category->color ?? '#0d6efd' }}22 0%, {{ $category->color ?? '#0d6efd' }}08 100%); border-bottom: 3px solid {{ $category->color ?? '#0d6efd' }};">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Blog',          'url' => route('blog.index')],
                    ['label' => 'Categories',    'url' => route('categories.index')],
                    ['label' => $category->name, 'url' => route('categories.show', $category->slug)],
                ]
            ])
            <div class="d-flex align-items-center gap-4 mt-3">
                @if($category->image)
                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="rounded-3 flex-shrink-0 d-none d-sm-block" width="80" height="80" style="object-fit:cover;">
                @else
                <div class="rounded-3 flex-shrink-0 d-flex align-items-center justify-content-center d-none d-sm-flex" style="width:80px;height:80px;background:{{ $category->color ?? '#0d6efd' }}22;">
                    <i class="fas fa-folder fa-2x" style="color:{{ $category->color ?? '#0d6efd' }};"></i>
                </div>
                @endif
                <div>
                    <div class="text-uppercase small fw-medium mb-1" style="color:{{ $category->color ?? '#0d6efd' }};letter-spacing:.06em;">Category</div>
                    <h1 class="h2 fw-bold mb-1">{{ $category->name }}</h1>
                    @if($category->description)
                    <p class="text-muted mb-1">{{ $category->description }}</p>
                    @endif
                    <small class="text-muted">
                        <i class="fas fa-file-alt me-1"></i>{{ number_format($posts->total()) }} {{ Str::plural('article', $posts->total()) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5">

            {{-- Posts Grid --}}
            <div class="col-lg-8">
                @if($posts->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <h3 class="h5">No posts in this category yet</h3>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary mt-2">Browse All Posts</a>
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

            {{-- Sidebar --}}
            <div class="col-lg-4">
                @include('partials.sidebar')
            </div>

        </div>
    </div>

@endsection
