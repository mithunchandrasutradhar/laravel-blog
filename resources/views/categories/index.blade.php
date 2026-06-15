@extends('layouts.app')

@php
    $seo = [
        'title'       => 'Categories — ' . settings('site_name', config('app.name')),
        'description' => 'Browse all categories on ' . settings('site_name', config('app.name')),
        'canonical'   => route('categories.index'),
    ];
@endphp

@push('styles')
<style>
    /* ── Parent category card ─────────────────────────────────── */
    .cat-parent-card {
        border-radius: .875rem;
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
        text-decoration: none;
        display: block;
    }
    .cat-parent-card:hover {
        box-shadow: 0 .5rem 1.75rem rgba(0,0,0,.1) !important;
        transform: translateY(-2px);
    }
    .cat-icon-col {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 88px;
        min-height: 88px;
    }
    .cat-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: .6rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ── Sub-category cards ───────────────────────────────────── */
    .subcat-card {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .75rem 1rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: .625rem;
        text-decoration: none;
        transition: box-shadow .15s, transform .15s, border-color .15s;
    }
    .subcat-card:hover {
        box-shadow: 0 .25rem .75rem rgba(0,0,0,.08);
        transform: translateY(-2px);
        border-color: #dee2e6;
    }
    .subcat-icon {
        width: 36px;
        height: 36px;
        border-radius: .4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .count-pill {
        font-size: .68rem;
        font-weight: 700;
        line-height: 1;
        padding: .25rem .5rem;
        border-radius: 100px;
        white-space: nowrap;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header bg-light border-bottom py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 fw-bold mb-1">Browse Categories</h1>
                @include('partials.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Blog',       'url' => route('blog.index')],
                    ['label' => 'Categories', 'url' => route('categories.index')],
                ]])
            </div>
            <div class="col-auto d-none d-md-flex align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                    {{ $categories->count() }} {{ Str::plural('Category', $categories->count()) }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    @forelse($categories as $category)
    @php
        $color      = $category->color ?? '#0d6efd';
        $icon       = $category->icon  ?? 'fas fa-folder';
        $postCount  = $category->posts_count ?? 0;
        $descCount  = $category->descendants->count();
    @endphp

    <div class="mb-5">

        {{-- ── Parent Category Card ───────────────────────────── --}}
        <a href="{{ route('categories.show', $category->slug) }}" class="cat-parent-card card border-0 shadow-sm mb-3">
            <div class="card-body p-0">
                <div class="d-flex align-items-stretch">

                    {{-- Icon column with accent colour --}}
                    <div class="cat-icon-col" style="background:{{ $color }}18; border-left:5px solid {{ $color }};">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}"
                                 alt="{{ $category->name }}"
                                 class="rounded-2"
                                 style="width:52px;height:52px;object-fit:cover;">
                        @else
                            <div class="cat-icon-wrap" style="background:{{ $color }}28;">
                                <i class="{{ $icon }} fa-lg" style="color:{{ $color }};"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Name + meta --}}
                    <div class="flex-grow-1 px-4 py-3 d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <span class="h5 fw-bold mb-0 text-dark">{{ $category->name }}</span>
                            <span class="count-pill" style="background:{{ $color }}18; color:{{ $color }};">
                                {{ $postCount }} {{ Str::plural('post', $postCount) }}
                            </span>
                            @if($descCount)
                                <span class="text-muted" style="font-size:.8rem;">
                                    · {{ $descCount }} {{ Str::plural('sub-category', $descCount) }}
                                </span>
                            @endif
                        </div>
                        @if($category->description)
                            <p class="text-muted mb-0" style="font-size:.875rem;">{{ $category->description }}</p>
                        @endif
                    </div>

                    {{-- Arrow --}}
                    <div class="d-flex align-items-center pe-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:38px;height:38px;background:{{ $color }}15;color:{{ $color }};">
                            <i class="fas fa-arrow-right fa-sm"></i>
                        </div>
                    </div>

                </div>
            </div>
        </a>

        {{-- ── Sub-categories grid ─────────────────────────────── --}}
        @if($category->descendants->isNotEmpty())
        <div class="row g-2 ps-md-3">
            @foreach($category->descendants as $child)
            @php
                $childColor = $child->color ?? $color;
                $childIcon  = $child->icon  ?? 'fas fa-folder-open';
                $childCount = $child->posts_count ?? 0;
            @endphp
            <div class="col-12 col-sm-6 col-xl-4">
                <a href="{{ route('categories.show', $child->slug) }}" class="subcat-card h-100">
                    <div class="subcat-icon" style="background:{{ $childColor }}18;">
                        <i class="{{ $childIcon }} fa-sm" style="color:{{ $childColor }};"></i>
                    </div>
                    <span class="fw-medium text-dark flex-grow-1" style="font-size:.875rem;line-height:1.3;">
                        {{ $child->name }}
                    </span>
                    <span class="count-pill" style="background:#f1f3f5;color:#6c757d;">
                        {{ $childCount }}
                    </span>
                </a>
            </div>
            @endforeach
        </div>
        @endif

    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="fas fa-folder-open fa-3x mb-3 opacity-25 d-block"></i>
        <p>No categories yet.</p>
    </div>
    @endforelse
</div>

@endsection
