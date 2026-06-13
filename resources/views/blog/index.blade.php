@extends('layouts.app')

@php
    $seo = [
        'title'       => 'Blog — ' . settings('site_name', config('app.name')),
        'description' => 'Browse all articles, guides, and insights on ' . settings('site_name', config('app.name')),
    ];
@endphp

@section('content')

    {{-- Page Header --}}
    <div class="page-header bg-light border-bottom py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3 fw-bold mb-1">Blog</h1>
                    @include('partials.breadcrumb', ['breadcrumbs' => [['label' => 'Blog', 'url' => route('blog.index')]]])
                </div>
                <div class="col-auto d-none d-md-block text-muted small">
                    {{ number_format($posts->total()) }} {{ Str::plural('post', $posts->total()) }} found
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5">

            {{-- Main Content --}}
            <div class="col-lg-8">

                {{-- Filters Bar --}}
                <form method="GET" action="{{ route('blog.index') }}" id="filtersForm" class="mb-4">
                    <div class="filters-bar card border-0 shadow-sm p-3">
                        <div class="row g-2 align-items-end">

                            {{-- Search --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-medium mb-1" for="filterSearch">Search</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="search" name="q" id="filterSearch" class="form-control border-start-0" placeholder="Search posts..." value="{{ request('q') }}">
                                </div>
                            </div>

                            {{-- Category --}}
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-medium mb-1" for="filterCategory">Category</label>
                                <select name="category" id="filterCategory" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($filterCategories ?? [] as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->posts_count }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Author --}}
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-medium mb-1" for="filterAuthor">Author</label>
                                <select name="author" id="filterAuthor" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Authors</option>
                                    @foreach($filterAuthors ?? [] as $author)
                                    <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                        {{ $author->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Sort --}}
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-medium mb-1" for="filterSort">Sort By</label>
                                <select name="sort" id="filterSort" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="latest"  {{ request('sort', 'latest') === 'latest'  ? 'selected' : '' }}>Latest</option>
                                    <option value="oldest"  {{ request('sort') === 'oldest'            ? 'selected' : '' }}>Oldest</option>
                                    <option value="popular" {{ request('sort') === 'popular'           ? 'selected' : '' }}>Most Popular</option>
                                    <option value="title"   {{ request('sort') === 'title'             ? 'selected' : '' }}>Title A–Z</option>
                                </select>
                            </div>

                            {{-- Submit / Clear --}}
                            <div class="col-6 col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fas fa-filter"></i><span class="d-none d-lg-inline ms-1">Filter</span>
                                </button>
                                @if(request()->hasAny(['q','category','author','sort','tag']))
                                <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                                    <i class="fas fa-times"></i>
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Active Tag Filters (pills) --}}
                        @if(request('tag') || isset($activeTags))
                        <div class="mt-3 pt-2 border-top d-flex flex-wrap gap-2 align-items-center">
                            <small class="text-muted fw-medium">Tags:</small>
                            @foreach($allTags ?? [] as $tag)
                            <a href="{{ route('blog.index', array_merge(request()->query(), ['tag' => $tag->slug])) }}"
                               class="badge text-decoration-none tag-filter-pill {{ request('tag') === $tag->slug ? 'bg-primary' : 'bg-light text-dark border' }}">
                                {{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </form>

                {{-- Posts Grid --}}
                @if($posts->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                    <h3 class="h5">No posts found</h3>
                    <p class="mb-3">Try adjusting your search or filter criteria.</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">Clear Filters</a>
                </div>
                @else
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-sm-6">
                        @include('partials.post-card', ['post' => $post])
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
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
