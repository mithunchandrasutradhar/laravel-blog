@extends('layouts.app')

@section('content')

    {{-- Hero Section --}}
    @if($featuredPost)
    <section class="hero-section" aria-label="Featured post">
        <div class="hero-inner position-relative overflow-hidden" style="min-height:520px;">
            @if($featuredPost->thumbnail)
            <img
                src="{{ asset($featuredPost->thumbnail) }}"
                alt="{{ $featuredPost->title }}"
                class="hero-bg-image position-absolute w-100 h-100"
                style="object-fit:cover;top:0;left:0;"
                loading="eager"
                fetchpriority="high"
            >
            @else
            <div class="hero-bg-image position-absolute w-100 h-100 bg-dark"></div>
            @endif
            <div class="hero-overlay position-absolute w-100 h-100" style="top:0;left:0;background:linear-gradient(to right,rgba(0,0,0,.75) 0%,rgba(0,0,0,.3) 60%,transparent 100%);"></div>

            <div class="container position-relative h-100 py-5">
                <div class="row h-100 align-items-center">
                    <div class="col-lg-7">
                        <div class="hero-content text-white py-4 py-md-5">
                            @if($featuredPost->category)
                            <a href="{{ route('categories.show', $featuredPost->category->slug) }}" class="badge text-decoration-none mb-3 d-inline-block hero-category-badge" style="background-color:{{ $featuredPost->category->color ?? 'var(--brand-primary)' }};">
                                {{ $featuredPost->category->name }}
                            </a>
                            @endif

                            <h1 class="hero-title display-6 fw-bold mb-3 text-white">
                                <a href="{{ route('blog.show', $featuredPost->slug) }}" class="text-white text-decoration-none stretched-link">
                                    {{ $featuredPost->title }}
                                </a>
                            </h1>

                            @if($featuredPost->excerpt)
                            <p class="hero-excerpt text-white opacity-75 mb-4 fs-6 d-none d-md-block" style="max-width:520px;">
                                {{ Str::limit($featuredPost->excerpt, 160) }}
                            </p>
                            @endif

                            <div class="hero-meta d-flex align-items-center gap-3 flex-wrap mb-4">
                                @if($featuredPost->author)
                                <div class="d-flex align-items-center gap-2">
                                    @if($featuredPost->author->avatar)
                                    <img src="{{ asset($featuredPost->author->avatar) }}" alt="{{ $featuredPost->author->name }}" class="rounded-circle" width="32" height="32" style="object-fit:cover;border:2px solid rgba(255,255,255,.4);">
                                    @endif
                                    <span class="text-white opacity-90 small">{{ $featuredPost->author->name }}</span>
                                </div>
                                @endif
                                @if($featuredPost->published_at)
                                <span class="text-white opacity-75 small">
                                    <i class="far fa-calendar-alt me-1"></i>{{ $featuredPost->published_at->format('M d, Y') }}
                                </span>
                                @endif
                                @if($featuredPost->reading_time)
                                <span class="text-white opacity-75 small">
                                    <i class="far fa-clock me-1"></i>{{ $featuredPost->reading_time }} min read
                                </span>
                                @endif
                            </div>

                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="btn btn-primary btn-lg px-4 hero-cta position-relative">
                                Read Article <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Latest Posts Section --}}
    <section class="latest-posts-section py-5" aria-labelledby="latestPostsHeading">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="section-header d-flex align-items-center justify-content-between mb-4">
                        <h2 id="latestPostsHeading" class="h4 fw-bold mb-0">
                            <span class="section-title-accent"></span>Latest Posts
                        </h2>
                        <a href="{{ route('blog.index') }}" class="btn btn-outline-primary btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="row g-4">
                        @forelse($latestPosts as $post)
                        <div class="col-sm-6 col-lg-4">
                            @include('partials.post-card', ['post' => $post])
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-newspaper fa-3x mb-3 opacity-25"></i>
                                <p>No posts published yet.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Trending Posts Sidebar --}}
                <div class="col-lg-4 mt-5 mt-lg-0">
                    <div class="trending-posts-widget card border-0 shadow-sm h-auto sticky-lg-top" style="top:80px;">
                        <div class="card-body p-4">
                            <h3 class="h5 fw-bold mb-4">
                                <i class="fas fa-chart-line me-2 text-danger"></i>Trending Now
                            </h3>
                            <ol class="list-unstyled trending-list mb-0">
                                @forelse($trendingPosts ?? [] as $trending)
                                <li class="trending-item d-flex gap-3 align-items-start {{ !$loop->last ? 'mb-4' : '' }}">
                                    <span class="trending-number display-6 fw-black text-primary opacity-25 flex-shrink-0 lh-1">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div>
                                        @if($trending->category)
                                        <a href="{{ route('categories.show', $trending->category->slug) }}" class="badge text-decoration-none mb-1 d-inline-block" style="background-color:{{ $trending->category->color ?? 'var(--brand-primary)' }};font-size:.65rem;">
                                            {{ $trending->category->name }}
                                        </a>
                                        @endif
                                        <a href="{{ route('blog.show', $trending->slug) }}" class="text-dark text-decoration-none fw-semibold line-clamp-2 d-block small">
                                            {{ $trending->title }}
                                        </a>
                                        <small class="text-muted">
                                            <i class="far fa-eye me-1"></i>{{ number_format($trending->views_count ?? 0) }} views
                                        </small>
                                    </div>
                                </li>
                                @empty
                                <li class="text-muted text-center py-2">
                                    <small>No trending posts yet.</small>
                                </li>
                                @endforelse
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories Section --}}
    @if(isset($homeCategories) && $homeCategories->isNotEmpty())
    <section class="categories-section py-5 bg-light" aria-labelledby="categoriesHeading">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 id="categoriesHeading" class="h3 fw-bold mb-2">Browse by Category</h2>
                <p class="text-muted mb-0">Explore topics that interest you most</p>
            </div>

            <div class="row g-4 justify-content-center">
                @foreach($homeCategories as $category)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <a href="{{ route('categories.show', $category->slug) }}" class="category-card card border-0 shadow-sm text-decoration-none text-center h-100 overflow-hidden">
                        <div class="category-card-image position-relative overflow-hidden" style="height:100px;">
                            @if($category->image)
                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 lazy-img" style="object-fit:cover;" loading="lazy" data-src="{{ asset($category->image) }}">
                            @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:{{ $category->color ?? '#6c757d' }}1a;">
                                <i class="fas fa-folder fa-2x" style="color:{{ $category->color ?? '#6c757d' }};"></i>
                            </div>
                            @endif
                            <div class="category-overlay position-absolute w-100 h-100" style="top:0;left:0;background:{{ $category->color ?? '#0d6efd' }}33;"></div>
                        </div>
                        <div class="card-body py-3 px-2">
                            <h3 class="h6 fw-semibold mb-1 text-dark">{{ $category->name }}</h3>
                            <small class="text-muted">{{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}</small>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('categories.index') }}" class="btn btn-outline-primary px-5">
                    <i class="fas fa-th-large me-2"></i>View All Categories
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- Newsletter Section --}}
    <section class="newsletter-section py-5 bg-primary text-white" aria-labelledby="newsletterHeading">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-6">
                    <i class="fas fa-envelope-open-text fa-3x mb-4 opacity-75"></i>
                    <h2 id="newsletterHeading" class="h3 fw-bold mb-2">Never Miss a Post</h2>
                    <p class="opacity-75 mb-4">Join {{ number_format(settings('subscriber_count', 0)) }}+ readers and get new articles delivered to your inbox every week.</p>
                    @include('partials.newsletter-form', ['variant' => 'inline'])
                    <p class="opacity-50 mt-3 mb-0" style="font-size:.8rem;">No spam, unsubscribe anytime.</p>
                </div>
            </div>
        </div>
    </section>

@endsection
