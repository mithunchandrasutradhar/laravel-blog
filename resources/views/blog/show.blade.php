@extends('layouts.app')

@php
    $seo = [
        'title'           => $post->seo_title ?? $post->title . ' — ' . settings('site_name', config('app.name')),
        'description'     => $post->seo_description ?? $post->excerpt,
        'keywords'        => $post->seo_keywords ?? ($post->tags->pluck('name')->join(', ')),
        'og_type'         => 'article',
        'og_title'        => $post->seo_title ?? $post->title,
        'og_description'  => $post->seo_description ?? $post->excerpt,
        'og_image'        => $post->thumbnail ? asset($post->thumbnail) : null,
        'og_url'          => route('blog.show', $post->slug),
        'twitter_card'    => 'summary_large_image',
        'canonical'       => route('blog.show', $post->slug),
    ];
    $bodyClass = 'single-post-page';
@endphp

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BlogPosting",
    "headline": "{{ addslashes($post->title) }}",
    "description": "{{ addslashes($post->excerpt ?? '') }}",
    "image": "{{ $post->thumbnail ? asset($post->thumbnail) : '' }}",
    "datePublished": "{{ $post->published_at?->toIso8601String() }}",
    "dateModified": "{{ $post->updated_at->toIso8601String() }}",
    "author": {
        "@@type": "Person",
        "name": "{{ addslashes($post->author?->name ?? '') }}",
        "url": "{{ $post->author ? route('authors.show', $post->author->username ?? $post->author->id) : '' }}"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "{{ settings('site_name', config('app.name')) }}",
        "logo": {
            "@@type": "ImageObject",
            "url": "{{ settings('logo') ? asset(settings('logo')) : '' }}"
        }
    },
    "mainEntityOfPage": {
        "@@type": "WebPage",
        "@@id": "{{ route('blog.show', $post->slug) }}"
    }
}
</script>
@endpush

@push('styles')
<style>
    .reading-progress-bar { display: block !important; }
</style>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    <div class="container mt-3">
        @include('partials.breadcrumb', [
            'breadcrumbs' => [
                ['label' => 'Blog',                         'url' => route('blog.index')],
                ['label' => $post->category?->name ?? 'Post', 'url' => $post->category ? route('categories.show', $post->category->slug) : route('blog.index')],
                ['label' => Str::limit($post->title, 60),   'url' => route('blog.show', $post->slug)],
            ]
        ])
    </div>

    <article class="container py-4" id="post-article" data-post-id="{{ $post->id }}" itemscope itemtype="https://schema.org/BlogPosting">

        <div class="row g-5">

            {{-- Post Main --}}
            <div class="col-lg-8">

                {{-- Post Header --}}
                <header class="post-header mb-4">
                    {{-- Category Badges --}}
                    @php
                        $showCats = ($post->relationLoaded('categories') && $post->categories->isNotEmpty())
                            ? $post->categories
                            : collect(array_filter([$post->category ?? null]));
                    @endphp
                    @if($showCats->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($showCats as $cat)
                        <a href="{{ route('categories.show', $cat->slug) }}" class="post-category-badge" style="background-color:{{ $cat->color ?? 'var(--brand-primary)' }};">
                            {{ $cat->name }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    {{-- Title --}}
                    <h1 class="post-title display-6 fw-bold mb-3 lh-sm" itemprop="headline">{{ $post->title }}</h1>

                    {{-- Meta --}}
                    <div class="post-meta d-flex flex-wrap align-items-center gap-3 text-muted mb-3">
                        @if($post->author)
                        <a href="{{ route('authors.show', $post->author->username ?? $post->author->id) }}" class="d-flex align-items-center gap-2 text-muted text-decoration-none" itemprop="author" itemscope itemtype="https://schema.org/Person">
                            @if($post->author->avatar)
                            <img src="{{ asset($post->author->avatar) }}" alt="{{ $post->author->name }}" class="rounded-circle flex-shrink-0" style="width:36px;height:36px;min-width:36px;min-height:36px;object-fit:cover;">
                            @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">
                                {{ strtoupper(substr($post->author->name, 0, 1)) }}
                            </div>
                            @endif
                            <span class="fw-medium" itemprop="name">{{ $post->author->name }}</span>
                        </a>
                        @endif

                        @if($post->published_at)
                        <span class="d-flex align-items-center gap-1">
                            <i class="far fa-calendar-alt"></i>
                            <time datetime="{{ $post->published_at->toIso8601String() }}" itemprop="datePublished">
                                {{ $post->published_at->format('M d, Y') }}
                            </time>
                        </span>
                        @endif

                        @if($post->reading_time)
                        <span class="d-flex align-items-center gap-1">
                            <i class="far fa-clock"></i>{{ $post->reading_time }} min read
                        </span>
                        @endif

                        <span class="d-flex align-items-center gap-1">
                            <i class="far fa-eye"></i>{{ number_format($post->views_count ?? 0) }} views
                        </span>

                        @if($post->updated_at && $post->updated_at->gt($post->published_at?->addHours(1)))
                        <span class="d-flex align-items-center gap-1" title="Updated {{ $post->updated_at->format('M d, Y') }}">
                            <i class="fas fa-sync-alt"></i>Updated <time datetime="{{ $post->updated_at->toIso8601String() }}" itemprop="dateModified">{{ $post->updated_at->format('M d, Y') }}</time>
                        </span>
                        @endif
                    </div>
                </header>

                {{-- Featured Image --}}
                @if($post->thumbnail)
                <figure class="post-featured-image mb-4 rounded overflow-hidden" itemprop="image">
                    <img
                        src="{{ asset($post->thumbnail) }}"
                        alt="{{ $post->thumbnail_alt ?? $post->title }}"
                        class="img-fluid w-100 rounded"
                        style="max-height:480px;object-fit:cover;"
                        loading="eager"
                        fetchpriority="high"
                        width="800"
                        height="450"
                    >
                    @if($post->thumbnail_caption)
                    <figcaption class="text-muted text-center mt-2 small">{{ $post->thumbnail_caption }}</figcaption>
                    @endif
                </figure>
                @endif

                {{-- Post Content with in-article ad injection --}}
                <div class="post-content prose" id="post-content" itemprop="articleBody">
                    @php
                        // Inject ad after 2nd paragraph
                        $contentParts = preg_split('/(<\/p>)/i', $post->content, 3, PREG_SPLIT_DELIM_CAPTURE);
                        $injected = false;
                    @endphp

                    @if(count($contentParts) >= 5)
                        {!! $contentParts[0] . $contentParts[1] . $contentParts[2] . $contentParts[3] !!}

                        {{-- In-Article Advertisement --}}
                        <div class="my-4">
                            @include('partials.advertisement', ['position' => 'in-article'])
                        </div>

                        {!! implode('', array_slice($contentParts, 4)) !!}
                    @else
                        {!! $post->content !!}
                    @endif
                </div>

                {{-- Tags --}}
                @if($post->tags->isNotEmpty())
                <div class="post-tags mt-4 pt-3 border-top d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-medium"><i class="fas fa-tags me-1"></i>Tags:</span>
                    @foreach($post->tags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}" class="badge bg-light text-dark border text-decoration-none tag-badge">
                        {{ $tag->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Social Share --}}
                <div class="post-share mt-4 pt-3 border-top">
                    <h3 class="h6 fw-semibold mb-3">Share this article</h3>
                    @include('partials.social-share', ['url' => route('blog.show', $post->slug), 'title' => $post->title])
                </div>

                {{-- Author Bio Box --}}
                @if($post->author)
                <div class="author-bio-box card border-0 shadow-sm mt-5 p-4">
                    <div class="d-flex flex-column flex-sm-row gap-4 align-items-start">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0 text-center text-sm-start">
                            <a href="{{ route('authors.show', $post->author->username ?? $post->author->id) }}">
                                @if($post->author->avatar)
                                <img src="{{ asset($post->author->avatar) }}" alt="{{ $post->author->name }}" class="rounded-circle flex-shrink-0" style="width:88px;height:88px;min-width:88px;min-height:88px;object-fit:cover;">
                                @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto fw-bold fs-2" style="width:88px;height:88px;">
                                    {{ strtoupper(substr($post->author->name, 0, 1)) }}
                                </div>
                                @endif
                            </a>
                        </div>

                        {{-- Info --}}
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <small class="text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.05em;">Written by</small>
                                    <h3 class="h5 fw-bold mb-0">
                                        <a href="{{ route('authors.show', $post->author->username ?? $post->author->id) }}" class="text-dark text-decoration-none">
                                            {{ $post->author->name }}
                                        </a>
                                    </h3>
                                    @if($post->author->title)
                                    <p class="text-muted small mb-1">{{ $post->author->title }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('authors.show', $post->author->username ?? $post->author->id) }}" class="btn btn-sm btn-outline-primary">
                                    View All Posts
                                </a>
                            </div>

                            @if($post->author->bio)
                            <p class="text-muted mb-3">{{ $post->author->bio }}</p>
                            @endif

                            {{-- Author Social Links --}}
                            <div class="author-social d-flex gap-3">
                                @if($post->author->social_twitter)
                                <a href="{{ $post->author->social_twitter }}" class="text-muted" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                                @endif
                                @if($post->author->social_linkedin)
                                <a href="{{ $post->author->social_linkedin }}" class="text-muted" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                                @endif
                                @if($post->author->social_github)
                                <a href="{{ $post->author->social_github }}" class="text-muted" target="_blank" rel="noopener noreferrer" aria-label="GitHub"><i class="fab fa-github"></i></a>
                                @endif
                                @if($post->author->website)
                                <a href="{{ $post->author->website }}" class="text-muted" target="_blank" rel="noopener noreferrer" aria-label="Website"><i class="fas fa-globe"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Related Posts --}}
                @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
                <section class="related-posts mt-5" aria-labelledby="relatedPostsHeading">
                    <h3 id="relatedPostsHeading" class="h5 fw-bold mb-4">
                        <span class="section-title-accent"></span>Related Articles
                    </h3>
                    <div class="row g-4">
                        @foreach($relatedPosts as $related)
                        <div class="col-sm-4">
                            @include('partials.post-card', ['post' => $related, 'imageHeight' => 160])
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Comments Section --}}
                <section class="comments-section mt-5" id="comments" aria-labelledby="commentsHeading">
                    <h3 id="commentsHeading" class="h5 fw-bold mb-4">
                        <i class="fas fa-comments me-2 text-primary"></i>
                        {{ $post->approvedComments->count() }} {{ Str::plural('Comment', $post->approvedComments->count()) }}
                    </h3>

                    {{-- Comment Form --}}
                    <div class="comment-form-wrapper card border-0 shadow-sm p-4 mb-5">
                        <h4 class="h6 fw-bold mb-3">
                            <i class="fas fa-pen me-2 text-primary"></i>Leave a Comment
                        </h4>

                        @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                        @else
                        <form action="{{ route('comments.store') }}" method="POST" id="commentForm" novalidate>
                            @csrf
                            <input type="hidden" name="post_id" value="{{ $post->id }}">
                            <input type="hidden" name="parent_id" value="" id="replyParentId">

                            @guest
                            {{-- Guest fields --}}
                            <div class="row g-3 mb-3">
                                <div class="col-sm-4">
                                    <label for="comment_name" class="form-label small fw-medium">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="comment_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="comment_email" class="form-label small fw-medium">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="comment_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="comment_website" class="form-label small fw-medium">Website</label>
                                    <input type="url" name="website" id="comment_website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website') }}" placeholder="https://" autocomplete="url">
                                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            @else
                            <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded">
                                @if(auth()->user()->avatar)
                                <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle flex-shrink-0" style="width:36px;height:36px;min-width:36px;min-height:36px;object-fit:cover;">
                                @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="fw-medium">{{ auth()->user()->name }}</div>
                                    <small class="text-muted">Commenting as yourself</small>
                                </div>
                            </div>
                            @endguest

                            {{-- Comment Body --}}
                            <div class="mb-3">
                                <label for="commentBody" class="form-label small fw-medium">Comment <span class="text-danger">*</span></label>
                                <textarea name="body" id="commentBody" rows="5" class="form-control @error('body') is-invalid @enderror" placeholder="Write your comment here..." required>{{ old('body') }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- reCAPTCHA v2 --}}
                            @if(settings('recaptcha_site_key'))
                            <div class="mb-3">
                                <div class="g-recaptcha" data-sitekey="{{ settings('recaptcha_site_key') }}"></div>
                                @error('g-recaptcha-response')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-2"></i>Post Comment
                                </button>
                            </div>
                        </form>

                        @if(settings('recaptcha_site_key'))
                        @push('scripts')
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        @endpush
                        @endif
                        @endif
                    </div>

                    {{-- Comments List — only approved, top-level; replies are nested inside comment-item --}}
                    @if($post->approvedComments->isNotEmpty())
                    <div class="comments-list" id="commentsList">
                        @foreach($post->approvedComments as $comment)
                            @include('partials.comment-item', ['comment' => $comment, 'depth' => 0])
                            @if(!$loop->last)<hr class="my-4">@endif
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comment-slash fa-2x mb-2 opacity-25 d-block"></i>
                        <p>No comments yet. Be the first to comment!</p>
                    </div>
                    @endif
                </section>

            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="sticky-lg-top" style="top:80px;">
                    @include('partials.sidebar')
                </div>
            </div>

        </div>
    </article>

@endsection

@push('scripts')
<script>
// Comment reply toggle
document.querySelectorAll('.comment-reply-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const formEl = document.getElementById('reply-form-' + btn.dataset.commentId);
        if (!formEl) return;
        formEl.classList.toggle('d-none');
        if (!formEl.classList.contains('d-none')) {
            formEl.querySelector('textarea')?.focus();
            document.getElementById('replyParentId').value = btn.dataset.commentId;
        }
    });
});
document.querySelectorAll('.comment-reply-cancel').forEach(btn => {
    btn.addEventListener('click', () => {
        const formEl = document.getElementById('reply-form-' + btn.dataset.commentId);
        formEl?.classList.add('d-none');
    });
});

// Track post view
(function() {
    const postId = document.getElementById('post-article')?.dataset.postId;
    if (!postId) return;
    fetch('/posts/' + postId + '/view', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).catch(() => {});
})();
</script>
@endpush
