{{--
    Post Card Partial — Premium Design
    Variables:
      $post        - Post model (required)
      $showExcerpt - bool (default true)
      $cardClass   - extra CSS class string
--}}
@php
    $showExcerpt = $showExcerpt ?? true;
    $cardClass   = $cardClass ?? '';
    $catColor    = $post->category->color ?? 'var(--brand-primary)';
@endphp

<article class="card post-card h-100 {{ $cardClass }}" itemscope itemtype="https://schema.org/BlogPosting">

    {{-- Thumbnail with category badge overlay --}}
    <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none post-card-img-link d-block" tabindex="-1">
        <div class="post-card-thumbnail">
            <img
                data-src="{{ $post->thumbnail }}"
                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3C/svg%3E"
                alt="{{ $post->title }}"
                class="w-100 h-100 lazy-img"
                itemprop="image"
                loading="lazy"
            >

            {{-- Category badge overlaid on image --}}
            @if($post->category)
            <div class="position-absolute top-0 start-0 p-3" style="z-index:2;">
                <a href="{{ route('categories.show', $post->category->slug) }}"
                   class="post-category-badge text-decoration-none"
                   style="background-color:{{ $catColor }};"
                   itemprop="articleSection">
                    {{ $post->category->name }}
                </a>
            </div>
            @endif
        </div>
    </a>

    {{-- Card Body --}}
    <div class="card-body d-flex flex-column" style="padding:1.125rem 1.25rem .875rem;">

        {{-- Title --}}
        <h2 class="post-card-title mb-2" itemprop="headline">
            <a href="{{ route('blog.show', $post->slug) }}"
               class="text-decoration-none stretched-link"
               style="color:inherit;">
                {{ $post->title }}
            </a>
        </h2>

        {{-- Excerpt --}}
        @if($showExcerpt && $post->excerpt)
        <p class="post-card-excerpt flex-grow-1 mb-0" itemprop="description">
            {{ $post->excerpt }}
        </p>
        @else
        <div class="flex-grow-1"></div>
        @endif

        {{-- Meta row --}}
        <div class="post-card-meta mt-auto">
            {{-- Author avatar + name --}}
            @if($post->author)
            <a href="{{ route('authors.show', $post->author->username ?? $post->author->id) }}"
               class="text-decoration-none d-flex align-items-center gap-2 flex-shrink-0"
               style="z-index:1;position:relative;" itemprop="author" itemscope itemtype="https://schema.org/Person">
                @if($post->author->avatar)
                    <img src="{{ asset($post->author->avatar) }}" alt="{{ $post->author->name }}"
                         class="rounded-circle flex-shrink-0" width="26" height="26"
                         style="object-fit:cover;" loading="lazy">
                @else
                    <div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:26px;height:26px;font-size:11px;background:{{ $catColor }};">
                        {{ strtoupper(substr($post->author->name, 0, 1)) }}
                    </div>
                @endif
                <span class="author-name text-truncate" style="max-width:100px;" itemprop="name">
                    {{ $post->author->name }}
                </span>
            </a>
            @endif

            <span class="meta-dot"></span>

            {{-- Date --}}
            @if($post->published_at)
            <time class="flex-shrink-0" style="font-size:.76rem;color:var(--brand-gray-400);"
                  datetime="{{ $post->published_at->toIso8601String() }}" itemprop="datePublished">
                {{ $post->published_at->format('M d, Y') }}
            </time>
            @endif

            {{-- Reading time pill --}}
            @if($post->reading_time)
            <span class="read-time-pill" title="{{ $post->reading_time }} min read">
                <i class="far fa-clock"></i>{{ $post->reading_time }}m
            </span>
            @endif
        </div>
    </div>
</article>
