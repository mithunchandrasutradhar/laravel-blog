{{--
    Post Card Partial
    Variables:
      $post      - Post model (required)
      $showExcerpt - bool (default true)
      $cardClass   - extra CSS class string
      $imageHeight - thumbnail height override (default 200)
--}}
@php
    $showExcerpt = $showExcerpt ?? true;
    $cardClass   = $cardClass ?? '';
    $imgHeight   = $imageHeight ?? 200;
@endphp

<article class="card post-card h-100 border-0 shadow-sm {{ $cardClass }}" itemscope itemtype="https://schema.org/BlogPosting">
    {{-- Thumbnail --}}
    <a href="{{ route('blog.show', $post->slug) }}" class="post-card-img-link text-decoration-none" tabindex="-1">
        <div class="post-card-thumbnail overflow-hidden" style="height:{{ $imgHeight }}px;">
            @if($post->thumbnail)
                <img
                    data-src="{{ asset($post->thumbnail) }}"
                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3C/svg%3E"
                    alt="{{ $post->thumbnail_alt ?? $post->title }}"
                    class="img-fluid w-100 h-100 object-fit-cover lazy-img"
                    width="400" height="{{ $imgHeight }}"
                    itemprop="image"
                >
            @else
                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">
                    <i class="fas fa-image fa-2x opacity-25"></i>
                </div>
            @endif
        </div>
    </a>

    <div class="card-body d-flex flex-column p-3">
        {{-- Category Badge --}}
        @if($post->category)
        <div class="mb-2">
            <a href="{{ route('categories.show', $post->category->slug) }}" class="badge text-decoration-none post-category-badge" style="background-color:{{ $post->category->color ?? 'var(--bs-primary)' }};">
                {{ $post->category->name }}
            </a>
        </div>
        @endif

        {{-- Title --}}
        <h2 class="card-title h6 fw-bold mb-2 line-clamp-2" itemprop="headline">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-dark text-decoration-none stretched-link post-title-link">
                {{ $post->title }}
            </a>
        </h2>

        {{-- Excerpt --}}
        @if($showExcerpt && $post->excerpt)
        <p class="card-text text-muted small line-clamp-3 flex-grow-1 mb-3" itemprop="description">
            {{ $post->excerpt }}
        </p>
        @else
        <div class="flex-grow-1"></div>
        @endif

        {{-- Meta --}}
        <div class="post-card-meta d-flex align-items-center gap-2 mt-auto pt-2 border-top">
            {{-- Author Avatar --}}
            @if($post->author)
            <a href="{{ route('authors.show', $post->author->username ?? $post->author->id) }}" class="text-decoration-none flex-shrink-0 position-relative" style="z-index:1;">
                @if($post->author->avatar)
                    <img src="{{ asset($post->author->avatar) }}" alt="{{ $post->author->name }}" class="rounded-circle" width="28" height="28" style="object-fit:cover;" loading="lazy">
                @else
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px;">
                        {{ strtoupper(substr($post->author->name, 0, 1)) }}
                    </div>
                @endif
            </a>
            <span class="text-muted small flex-grow-1 text-truncate" itemprop="author" itemscope itemtype="https://schema.org/Person">
                <span itemprop="name">{{ $post->author->name }}</span>
            </span>
            @endif

            {{-- Date + Reading Time --}}
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <time class="text-muted" style="font-size:.75rem;" datetime="{{ $post->published_at?->toIso8601String() }}" itemprop="datePublished">
                    {{ $post->published_at?->format('M d') }}
                </time>
                @if($post->reading_time)
                <span class="text-muted d-none d-sm-inline" style="font-size:.75rem;">
                    <i class="far fa-clock me-1"></i>{{ $post->reading_time }} min
                </span>
                @endif
            </div>
        </div>
    </div>
</article>
