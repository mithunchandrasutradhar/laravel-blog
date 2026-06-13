@extends('layouts.app')

@section('content')

{{-- ================================================================
     HERO — Dark magazine panel: main featured + latest sidebar
     ================================================================ --}}
<section class="hero-section" style="background:linear-gradient(150deg,#0a0f1e 0%,#111827 60%,#1a1f3a 100%);" aria-label="Featured and latest posts">
    <div class="container py-4 py-lg-5">
        <div class="row g-4 align-items-stretch">

            {{-- ── Main Featured Post ── --}}
            @if($featuredPost)
            <div class="col-lg-7">
                <article class="position-relative rounded-4 overflow-hidden shadow-lg" style="min-height:460px;cursor:pointer;" onclick="window.location='{{ route('blog.show', $featuredPost->slug) }}'">

                    @if($featuredPost->thumbnail)
                    <img src="{{ $featuredPost->thumbnail }}" alt="{{ $featuredPost->title }}"
                         class="position-absolute w-100 h-100" loading="eager" fetchpriority="high"
                         style="object-fit:cover;top:0;left:0;transition:transform .6s ease;">
                    @else
                    <div class="position-absolute w-100 h-100" style="background:linear-gradient(135deg,#1e3a5f,#0f172a);top:0;left:0;"></div>
                    @endif

                    {{-- gradient overlay --}}
                    <div class="position-absolute w-100 h-100" style="top:0;left:0;background:linear-gradient(to top,rgba(0,0,0,.9) 0%,rgba(0,0,0,.4) 55%,rgba(0,0,0,.05) 100%);"></div>

                    <div class="position-absolute bottom-0 start-0 end-0 p-4 p-lg-5">
                        @if($featuredPost->category)
                        <a href="{{ route('categories.show', $featuredPost->category->slug) }}"
                           class="post-category-badge d-inline-block mb-3"
                           style="background-color:{{ $featuredPost->category->color ?? 'var(--brand-primary)' }};">
                            {{ $featuredPost->category->name }}
                        </a>
                        @endif

                        <h1 class="fw-bold text-white mb-3 lh-sm" style="font-size:clamp(1.35rem,3.5vw,1.9rem);">
                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="text-white text-decoration-none stretched-link">
                                {{ $featuredPost->title }}
                            </a>
                        </h1>

                        @if($featuredPost->excerpt)
                        <p class="text-white mb-3 d-none d-md-block lh-base"
                           style="opacity:.75;font-size:.9375rem;max-width:520px;">
                            {{ Str::limit($featuredPost->excerpt, 130) }}
                        </p>
                        @endif

                        <div class="d-flex align-items-center flex-wrap gap-3">
                            @if($featuredPost->author)
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white flex-shrink-0"
                                     style="width:28px;height:28px;font-size:11px;font-weight:700;">
                                    {{ strtoupper(substr($featuredPost->author->name, 0, 1)) }}
                                </div>
                                <span class="text-white small" style="opacity:.85;">{{ $featuredPost->author->name }}</span>
                            </div>
                            @endif

                            @if($featuredPost->published_at)
                            <span class="text-white small" style="opacity:.6;">
                                <i class="far fa-calendar-alt me-1"></i>{{ $featuredPost->published_at->format('M d, Y') }}
                            </span>
                            @endif

                            @if($featuredPost->reading_time)
                            <span class="read-time-pill" style="background:rgba(255,255,255,.15);color:#fff;">
                                <i class="far fa-clock"></i>{{ $featuredPost->reading_time }} min
                            </span>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
            @endif

            {{-- ── Latest Articles Panel ── --}}
            <div class="col-lg-5 d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="mb-0 d-flex align-items-center gap-2"
                        style="font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.55);">
                        <span style="display:inline-block;width:16px;height:2px;background:var(--brand-accent);border-radius:2px;"></span>
                        Latest Articles
                    </h2>
                    <a href="{{ route('blog.index') }}"
                       class="btn btn-sm py-1 px-3 fw-semibold"
                       style="font-size:.75rem;background:rgba(255,255,255,.1);color:rgba(255,255,255,.75);border:1px solid rgba(255,255,255,.15);border-radius:var(--radius-pill);">
                        View All <i class="fas fa-arrow-right ms-1" style="font-size:.65rem;"></i>
                    </a>
                </div>

                <div class="d-flex flex-column gap-2 flex-grow-1">
                    @forelse($latestPosts->take(4) as $post)
                    <a href="{{ route('blog.show', $post->slug) }}"
                       class="hero-article-item d-flex gap-3 align-items-start text-decoration-none rounded-3 p-3"
                       style="background:rgba(255,255,255,.06);transition:background .2s,transform .2s;"
                       onmouseover="this.style.background='rgba(255,255,255,.12)';this.style.transform='translateX(4px)';"
                       onmouseout="this.style.background='rgba(255,255,255,.06)';this.style.transform='translateX(0)';">

                        {{-- thumb --}}
                        <div class="flex-shrink-0 rounded-2 overflow-hidden" style="width:76px;height:64px;">
                            @if($post->thumbnail)
                            <img src="{{ $post->thumbnail }}" alt="{{ $post->title }}"
                                 class="w-100 h-100" style="object-fit:cover;" loading="lazy">
                            @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                                 style="background:rgba(255,255,255,.08);">
                                <i class="fas fa-image text-white" style="opacity:.2;"></i>
                            </div>
                            @endif
                        </div>

                        {{-- info --}}
                        <div class="flex-grow-1 overflow-hidden">
                            @if($post->category)
                            <span class="d-inline-block px-2 py-0 rounded mb-1"
                                  style="font-size:.62rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;background-color:{{ $post->category->color ?? 'var(--brand-primary)' }};color:#fff;">
                                {{ $post->category->name }}
                            </span>
                            @endif
                            <div class="text-white fw-semibold line-clamp-2 mb-1" style="font-size:.8375rem;line-height:1.4;">
                                {{ $post->title }}
                            </div>
                            <div class="d-flex align-items-center gap-2" style="font-size:.7rem;color:rgba(255,255,255,.45);">
                                @if($post->author)<span>{{ $post->author->name }}</span><span>·</span>@endif
                                @if($post->published_at)<span>{{ $post->published_at->format('M d') }}</span>@endif
                                @if($post->reading_time)<span>· {{ $post->reading_time }}m read</span>@endif
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-5" style="color:rgba(255,255,255,.3);">
                        <i class="fas fa-newspaper fa-2x mb-2 d-block"></i>
                        <p class="small mb-0">No articles yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── Secondary Featured Row ── --}}
        @if($featuredPosts->count() > 1)
        <div class="row g-3 mt-1">
            @foreach($featuredPosts->skip(1)->take(2) as $post)
            <div class="col-md-6">
                <a href="{{ route('blog.show', $post->slug) }}"
                   class="d-flex gap-3 align-items-center text-decoration-none rounded-3 p-3"
                   style="background:rgba(255,255,255,.06);transition:background .2s;"
                   onmouseover="this.style.background='rgba(255,255,255,.12)'"
                   onmouseout="this.style.background='rgba(255,255,255,.06)'">
                    <div class="flex-shrink-0 rounded-2 overflow-hidden" style="width:72px;height:64px;">
                        @if($post->thumbnail)
                        <img src="{{ $post->thumbnail }}" alt="{{ $post->title }}"
                             class="w-100 h-100" style="object-fit:cover;" loading="lazy">
                        @else
                        <div class="w-100 h-100" style="background:rgba(255,255,255,.08);"></div>
                        @endif
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        @if($post->category)
                        <span class="d-inline-block px-2 rounded mb-1"
                              style="font-size:.6rem;font-weight:700;text-transform:uppercase;background:{{ $post->category->color ?? 'var(--brand-primary)' }};color:#fff;">
                            {{ $post->category->name }}
                        </span>
                        @endif
                        <div class="text-white fw-semibold line-clamp-2" style="font-size:.8375rem;line-height:1.4;">
                            {{ $post->title }}
                        </div>
                        @if($post->published_at)
                        <div style="font-size:.7rem;color:rgba(255,255,255,.4);">
                            {{ $post->published_at->format('M d, Y') }}
                            @if($post->reading_time) · {{ $post->reading_time }} min @endif
                        </div>
                        @endif
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- ================================================================
     LATEST POSTS + TRENDING SIDEBAR
     ================================================================ --}}
<section class="py-5" aria-labelledby="latestPostsHeading">
    <div class="container">
        <div class="row g-5">

            {{-- Latest posts grid --}}
            <div class="col-lg-8">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 id="latestPostsHeading" class="h4 fw-bold mb-0 d-flex align-items-center gap-2">
                        <span class="section-title-accent"></span>Latest Posts
                    </h2>
                    <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-primary fw-semibold px-4">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row g-4">
                    @forelse($latestPosts as $post)
                    <div class="col-sm-6">
                        @include('partials.post-card', ['post' => $post])
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-newspaper fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-0">No posts published yet. Check back soon!</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Trending sidebar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-lg-top" style="top:80px;">
                    <div class="card-header py-3 px-4 border-0"
                         style="background:linear-gradient(135deg,var(--brand-primary),var(--brand-primary-dark));">
                        <h3 class="h6 fw-bold mb-0 text-white d-flex align-items-center gap-2">
                            <i class="fas fa-fire"></i> Trending Now
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ol class="list-unstyled mb-0">
                            @forelse($trendingPosts ?? [] as $trending)
                            <li class="{{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--brand-gray-100)!important;">
                                <a href="{{ route('blog.show', $trending->slug) }}"
                                   class="d-flex align-items-start gap-3 p-3 text-decoration-none"
                                   style="transition:background .2s;"
                                   onmouseover="this.style.background='var(--brand-gray-50)'"
                                   onmouseout="this.style.background='transparent'">
                                    <span class="flex-shrink-0 fw-black lh-1 mt-1"
                                          style="font-size:1.75rem;min-width:2rem;color:var(--brand-gray-200);">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div class="flex-grow-1 overflow-hidden">
                                        @if($trending->category)
                                        <span class="d-inline-block px-2 rounded mb-1"
                                              style="font-size:.6rem;font-weight:700;text-transform:uppercase;background:{{ $trending->category->color ?? 'var(--brand-primary)' }};color:#fff;">
                                            {{ $trending->category->name }}
                                        </span>
                                        @endif
                                        <div class="fw-semibold line-clamp-2 mb-1"
                                             style="font-size:.875rem;color:var(--brand-dark);line-height:1.4;">
                                            {{ $trending->title }}
                                        </div>
                                        <div style="font-size:.72rem;color:var(--brand-gray-400);">
                                            <i class="far fa-eye me-1"></i>{{ number_format($trending->views_count ?? 0) }} views
                                            @if(($trending->approved_comments_count ?? 0) > 0)
                                            <span class="ms-2"><i class="far fa-comment me-1"></i>{{ $trending->approved_comments_count }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </li>
                            @empty
                            <li class="text-center py-4 text-muted">
                                <i class="fas fa-chart-line fa-2x mb-2 d-block opacity-25"></i>
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

{{-- ================================================================
     CATEGORIES SECTION
     ================================================================ --}}
@if(isset($homeCategories) && $homeCategories->isNotEmpty())
<section class="py-5 py-lg-6" style="background:var(--brand-gray-50);" aria-labelledby="categoriesHeading">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label mx-auto justify-content-center">Explore Topics</div>
            <h2 id="categoriesHeading" class="h2 fw-bold mb-2">Browse by Category</h2>
            <p class="text-muted mb-0">Find the content that matters most to you</p>
        </div>

        {{-- Marquee: two identical tracks → translate -50% loops seamlessly --}}
        <div class="cat-marquee-wrap">
            <div class="cat-marquee-track">

                @foreach([1,2] as $pass){{-- duplicate for seamless loop --}}
                @foreach($homeCategories as $category)
                @php
                    $catColor = $category->color ?? '#4f46e5';
                    $catIcon  = $category->icon  ?? 'fas fa-folder';
                @endphp
                <a href="{{ route('categories.show', $category->slug) }}"
                   class="cat-marquee-card text-decoration-none"
                   @if($pass === 2) aria-hidden="true" tabindex="-1" @endif>

                    <div class="cat-card-icon" style="background:{{ $catColor }}1a;">
                        <i class="{{ $catIcon }}" style="color:{{ $catColor }};font-size:1.4rem;"></i>
                    </div>

                    <h3 class="cat-card-name">{{ $category->name }}</h3>

                    <p class="cat-card-count">
                        {{ $category->posts_count }} {{ Str::plural('article', $category->posts_count) }}
                    </p>

                    <div class="cat-card-bar" style="background:{{ $catColor }};"></div>
                </a>
                @endforeach
                @endforeach

            </div>

            {{-- fade edges --}}
            <div class="cat-marquee-fade cat-marquee-fade-left"></div>
            <div class="cat-marquee-fade cat-marquee-fade-right"></div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('categories.index') }}" class="btn btn-primary px-5 fw-semibold">
                <i class="fas fa-th-large me-2"></i>All Categories
            </a>
        </div>
    </div>
</section>
@endif

{{-- ================================================================
     NEWSLETTER
     ================================================================ --}}
<section class="newsletter-section py-5 py-lg-6" aria-labelledby="newsletterHeading">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6 col-xl-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
                     style="width:72px;height:72px;background:rgba(255,255,255,.15);">
                    <i class="fas fa-envelope-open-text fa-2x text-white"></i>
                </div>
                <h2 id="newsletterHeading" class="h2 fw-bold text-white mb-2">Never Miss a Story</h2>
                <p class="mb-4" style="color:rgba(255,255,255,.75);font-size:1.0625rem;">
                    Join {{ number_format(settings('subscriber_count', 1200)) }}+ curious readers —
                    fresh articles delivered every week, no spam.
                </p>
                @include('partials.newsletter-form', ['variant' => 'inline'])
                <p class="mt-3 mb-0" style="font-size:.78rem;color:rgba(255,255,255,.45);">
                    <i class="fas fa-lock me-1"></i>Your email is safe. Unsubscribe anytime.
                </p>
            </div>
        </div>
    </div>
</section>

@endsection
