@extends('layouts.app')

@php
    $seo = [
        'title'       => 'Categories — ' . settings('site_name', config('app.name')),
        'description' => 'Browse all categories on ' . settings('site_name', config('app.name')),
        'canonical'   => route('categories.index'),
    ];
@endphp

@section('content')

    {{-- Page Header --}}
    <div class="page-header bg-light border-bottom py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3 fw-bold mb-1">Categories</h1>
                    @include('partials.breadcrumb', ['breadcrumbs' => [
                        ['label' => 'Blog',       'url' => route('blog.index')],
                        ['label' => 'Categories', 'url' => route('categories.index')],
                    ]])
                </div>
                <div class="col-auto d-none d-md-block text-muted small">
                    {{ $categories->count() }} {{ Str::plural('category', $categories->count()) }}
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">

        @forelse($categories as $category)
        <div class="mb-5">
            {{-- Category card --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4" style="border-left: 4px solid {{ $category->color ?? '#0d6efd' }};">
                    <div class="d-flex align-items-center gap-3">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}"
                                 alt="{{ $category->name }}"
                                 class="rounded-3 flex-shrink-0"
                                 width="64" height="64" style="object-fit:cover;">
                        @else
                            <div class="rounded-3 flex-shrink-0 d-flex align-items-center justify-content-center"
                                 style="width:64px;height:64px;background:{{ $category->color ?? '#0d6efd' }}22;">
                                <i class="{{ $category->icon ?? 'fas fa-folder' }} fa-lg" style="color:{{ $category->color ?? '#0d6efd' }};"></i>
                            </div>
                        @endif

                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('categories.show', $category->slug) }}"
                                   class="h5 fw-bold mb-0 text-decoration-none text-dark">
                                    {{ $category->name }}
                                </a>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                                </span>
                            </div>
                            @if($category->description)
                                <p class="text-muted small mb-0 mt-1">{{ $category->description }}</p>
                            @endif
                        </div>

                        <a href="{{ route('categories.show', $category->slug) }}"
                           class="btn btn-outline-primary btn-sm d-none d-sm-inline-flex align-items-center gap-1">
                            Browse <i class="fas fa-arrow-right fa-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sub-categories (all depths) --}}
            @if($category->descendants->isNotEmpty())
            <div class="row g-2 ps-3">
                @foreach($category->descendants as $child)
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('categories.show', $child->slug) }}"
                       class="d-flex align-items-center gap-2 p-2 rounded-2 text-decoration-none text-dark border bg-white hover-shadow">
                        <i class="{{ $child->icon ?? 'fas fa-folder-open' }} fa-sm" style="color:{{ $child->color ?? $category->color ?? '#0d6efd' }};"></i>
                        <span class="small fw-medium">{{ $child->name }}</span>
                        <span class="ms-auto badge bg-secondary bg-opacity-10 text-secondary small">{{ $child->posts_count ?? 0 }}</span>
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
            <p>No categories yet.</p>
        </div>
        @endforelse

    </div>

@endsection
