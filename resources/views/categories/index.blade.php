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
    .categories-page {
        background: #f7f6f2;
    }

    .cat-section-label {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1.25rem;
    }

    /* ── Core topic card ──────────────────────────────────────── */
    .core-cat-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border-radius: 1rem;
        border-left: 5px solid var(--cat-color, #6366f1);
        padding: 1.5rem;
        text-decoration: none;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: box-shadow .2s, transform .2s;
    }
    .core-cat-card:hover {
        box-shadow: 0 .75rem 2rem rgba(0,0,0,.09);
        transform: translateY(-3px);
    }
    .cat-icon-badge {
        width: 48px;
        height: 48px;
        border-radius: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: color-mix(in srgb, var(--cat-color, #6366f1) 16%, white);
    }
    .cat-icon-badge i {
        color: var(--cat-color, #6366f1);
        font-size: 1.15rem;
    }
    .core-cat-card .cat-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1a1a;
    }
    .core-cat-card .cat-desc {
        font-size: .875rem;
        color: #6c757d;
        margin: .75rem 0 0;
    }
    .core-cat-card .cat-footer {
        margin-top: auto;
        padding-top: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cat-meta {
        font-size: .8rem;
        color: #8a8f98;
    }
    .cat-arrow-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: color-mix(in srgb, var(--cat-color, #6366f1) 14%, white);
        color: var(--cat-color, #6366f1);
    }

    /* ── Leaf top-level "row" card ────────────────────────────── */
    .row-cat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #fff;
        border-radius: .75rem;
        border-left: 5px solid var(--cat-color, #adb5bd);
        padding: 1rem 1.25rem;
        text-decoration: none;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: box-shadow .2s, transform .2s;
    }
    .row-cat-card:hover {
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08);
        transform: translateY(-2px);
    }
    .row-cat-card .cat-icon-badge {
        width: 42px;
        height: 42px;
    }
    .row-cat-card .cat-title-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }
    .row-cat-card .cat-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1a1a1a;
    }
    .row-cat-card .cat-desc {
        font-size: .8rem;
        color: #8a8f98;
        margin: .2rem 0 0;
    }
    .count-badge {
        font-size: .7rem;
        font-weight: 700;
        line-height: 1;
        padding: .3rem .55rem;
        border-radius: 100px;
        background: color-mix(in srgb, var(--cat-color, #6366f1) 16%, white);
        color: var(--cat-color, #6366f1);
        white-space: nowrap;
    }

    /* ── Empty (0-post) category state ────────────────────────── */
    .row-cat-card.is-empty {
        border-left-color: #dee2e6;
        box-shadow: none;
    }
    .row-cat-card.is-empty:hover {
        box-shadow: none;
        transform: none;
    }
    .row-cat-card.is-empty .cat-icon-badge {
        background: #f1f3f5;
    }
    .row-cat-card.is-empty .cat-icon-badge i {
        color: #adb5bd;
    }
    .row-cat-card.is-empty .cat-title,
    .row-cat-card.is-empty .cat-desc {
        color: #adb5bd;
    }
    .row-cat-card.is-empty .cat-arrow-btn {
        background: #f1f3f5;
        color: #adb5bd;
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header border-bottom py-4" style="background: linear-gradient(90deg, #eef2ff 0%, #ffffff 100%);">
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

<div class="categories-page py-5">
    <div class="container">

        @php
            $subCategories = $categories->flatMap(fn ($c) => $c->descendants)->values();
        @endphp

        @if($categories->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open fa-3x mb-3 opacity-25 d-block"></i>
                <p>No categories yet.</p>
            </div>
        @else

            {{-- ── Core Topic Areas (all parent categories) ─────── --}}
            <div class="mb-5">
                <div class="cat-section-label">Core Topic Areas</div>
                <div class="row g-3">
                    @foreach($categories as $category)
                    @php
                        $color     = $category->color ?? '#6366f1';
                        $icon      = $category->icon  ?? 'fas fa-folder';
                        $postCount = $category->posts_count ?? 0;
                        $descCount = $category->descendants->count();
                    @endphp
                    <div class="col-12 col-md-4">
                        <a href="{{ route('categories.show', $category->slug) }}"
                           class="core-cat-card"
                           style="--cat-color: {{ $color }};">
                            <div class="d-flex align-items-center gap-3 mb-1">
                                <div class="cat-icon-badge">
                                    <i class="{{ $icon }}"></i>
                                </div>
                                <span class="cat-title">{{ $category->name }}</span>
                            </div>
                            @if($category->description)
                                <p class="cat-desc">{{ $category->description }}</p>
                            @endif
                            <div class="cat-footer">
                                <span class="cat-meta">
                                    {{ $postCount }} {{ Str::plural('post', $postCount) }} &bull; {{ $descCount }} {{ Str::plural('sub-category', $descCount) }}
                                </span>
                                <span class="cat-arrow-btn">
                                    <i class="fas fa-arrow-right fa-sm"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Specific Topics & Content Types (all sub-categories) ─── --}}
            @if($subCategories->isNotEmpty())
            <div>
                <div class="cat-section-label">Specific Topics &amp; Content Types</div>
                <div class="row g-3">
                    @foreach($subCategories as $child)
                    @php
                        $color     = $child->color ?? '#adb5bd';
                        $icon      = $child->icon  ?? 'fas fa-folder';
                        $postCount = $child->posts_count ?? 0;
                        $isEmpty   = $postCount === 0;
                    @endphp
                    <div class="col-12 col-md-6">
                        <a href="{{ route('categories.show', $child->slug) }}"
                           class="row-cat-card {{ $isEmpty ? 'is-empty' : '' }}"
                           style="--cat-color: {{ $color }};">
                            <div class="cat-icon-badge">
                                <i class="{{ $icon }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="cat-title-line">
                                    <span class="cat-title">{{ $child->name }}</span>
                                    @if(! $isEmpty)
                                        <span class="count-badge">{{ $postCount }} {{ Str::plural('post', $postCount) }}</span>
                                    @endif
                                </div>
                                @if($child->description)
                                    <p class="cat-desc mb-0">{{ $child->description }}</p>
                                @endif
                            </div>
                            <span class="cat-arrow-btn">
                                <i class="fas fa-arrow-right fa-sm"></i>
                            </span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        @endif

    </div>
</div>

@endsection
