@extends('layouts.app')

@php
    $query = request('q', '');
    $seo   = [
        'title'   => $query ? 'Search: "' . $query . '" — ' . settings('site_name', config('app.name')) : 'Search — ' . settings('site_name', config('app.name')),
        'robots'  => 'noindex, follow',
    ];
@endphp

@section('content')

    {{-- Page Header --}}
    <div class="page-header bg-light border-bottom py-4">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Search', 'url' => route('search')],
                ]
            ])
            <div class="row align-items-center mt-2 g-3">
                <div class="col-lg-6">
                    <h1 class="h3 fw-bold mb-2">
                        @if($query)
                            Search results for: <em class="text-primary">"{{ $query }}"</em>
                        @else
                            Search
                        @endif
                    </h1>
                    @if($query && isset($posts))
                    <p class="text-muted mb-0">
                        Found <strong>{{ number_format($posts->total()) }}</strong> {{ Str::plural('result', $posts->total()) }}
                    </p>
                    @endif
                </div>
                <div class="col-lg-6">
                    <form action="{{ route('search') }}" method="GET" role="search">
                        <div class="input-group input-group-lg">
                            <input type="search" name="q" class="form-control" placeholder="Search posts, topics..." value="{{ $query }}" autofocus aria-label="Search">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-8">

                @if(!$query)
                {{-- No query state --}}
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search fa-4x mb-4 opacity-15"></i>
                    <h2 class="h4 fw-bold mb-2">Start your search</h2>
                    <p>Enter keywords above to find articles, topics, and more.</p>

                    {{-- Suggested Topics --}}
                    @php
                        $popularTags = \App\Models\Tag::withCount(['posts' => fn($q) => $q->published()])->having('posts_count', '>', 0)->orderByDesc('posts_count')->take(12)->get();
                    @endphp
                    @if($popularTags->isNotEmpty())
                    <div class="mt-4">
                        <p class="text-muted fw-medium mb-3">Popular Topics:</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            @foreach($popularTags as $tag)
                            <a href="{{ route('search', ['q' => $tag->name]) }}" class="badge bg-light text-dark border text-decoration-none py-2 px-3" style="font-size:.85rem;">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                @elseif(isset($posts) && $posts->isEmpty())
                {{-- No results state --}}
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search-minus fa-4x mb-4 opacity-15"></i>
                    <h2 class="h4 fw-bold mb-2">No results found</h2>
                    <p class="mb-4">No articles match <strong>"{{ $query }}"</strong>. Try different keywords.</p>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('blog.index') }}" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>Browse All Posts
                        </a>
                        <a href="{{ route('search') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear Search
                        </a>
                    </div>

                    {{-- Suggestions --}}
                    @if(isset($suggestions) && $suggestions->isNotEmpty())
                    <div class="mt-5">
                        <h3 class="h6 fw-semibold mb-3">You might like these:</h3>
                        <div class="row g-3 justify-content-center">
                            @foreach($suggestions as $suggestion)
                            <div class="col-sm-6 col-md-4">
                                @include('partials.post-card', ['post' => $suggestion])
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                @else
                {{-- Results list --}}
                <div class="search-results">
                    @foreach($posts as $post)
                    <article class="search-result-item d-flex gap-4 py-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                        {{-- Thumbnail --}}
                        @if($post->thumbnail)
                        <a href="{{ route('blog.show', $post->slug) }}" class="flex-shrink-0 d-none d-sm-block text-decoration-none">
                            <img src="{{ asset($post->thumbnail) }}" alt="{{ $post->title }}" class="rounded lazy-img" width="140" height="100" style="object-fit:cover;" loading="lazy" data-src="{{ asset($post->thumbnail) }}">
                        </a>
                        @endif

                        {{-- Content --}}
                        <div class="flex-grow-1">
                            @if($post->category)
                            <a href="{{ route('categories.show', $post->category->slug) }}" class="badge text-decoration-none mb-2 d-inline-block" style="background-color:{{ $post->category->color ?? 'var(--brand-primary)' }};font-size:.7rem;">
                                {{ $post->category->name }}
                            </a>
                            @endif

                            <h2 class="h5 fw-bold mb-2">
                                <a href="{{ route('blog.show', $post->slug) }}" class="text-dark text-decoration-none">
                                    @if($query)
                                        {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark>$1</mark>', e($post->title)) !!}
                                    @else
                                        {{ $post->title }}
                                    @endif
                                </a>
                            </h2>

                            @if($post->excerpt)
                            <p class="text-muted mb-2 line-clamp-2">
                                @if($query)
                                    {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark>$1</mark>', e(Str::limit($post->excerpt, 200))) !!}
                                @else
                                    {{ Str::limit($post->excerpt, 200) }}
                                @endif
                            </p>
                            @endif

                            <div class="d-flex align-items-center gap-3 text-muted small flex-wrap">
                                @if($post->author)
                                <span><i class="fas fa-user me-1"></i>{{ $post->author->name }}</span>
                                @endif
                                @if($post->published_at)
                                <time datetime="{{ $post->published_at->toIso8601String() }}">
                                    <i class="far fa-calendar me-1"></i>{{ $post->published_at->format('M d, Y') }}
                                </time>
                                @endif
                                @if($post->reading_time)
                                <span><i class="far fa-clock me-1"></i>{{ $post->reading_time }} min</span>
                                @endif
                                <a href="{{ route('blog.show', $post->slug) }}" class="ms-auto text-primary text-decoration-none fw-medium">
                                    Read more <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </article>
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
