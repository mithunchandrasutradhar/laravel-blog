{{--
    Reusable Sidebar Partial
    Sections: search, popular posts, categories, tags cloud, newsletter, ads
--}}
<aside class="blog-sidebar" id="blogSidebar">

    {{-- Search Widget --}}
    <div class="sidebar-widget card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h3 class="sidebar-widget-title h6 fw-bold mb-3">
                <i class="fas fa-search me-2 text-primary"></i>Search
            </h3>
            <form action="{{ route('search') }}" method="GET" role="search">
                <div class="input-group">
                    <input type="search" name="q" class="form-control" placeholder="Search posts..." value="{{ request('q') }}" aria-label="Search posts">
                    <button class="btn btn-primary" type="submit" aria-label="Submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Advertisement - Sidebar Top --}}
    @include('partials.advertisement', ['position' => 'sidebar_top'])

    {{-- Popular Posts Widget --}}
    @php
        $sidebarPopularPosts = $sidebarPopularPosts ?? \App\Models\Post::published()->orderByDesc('views_count')->take(5)->get();
    @endphp
    @if($sidebarPopularPosts->isNotEmpty())
    <div class="sidebar-widget card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h3 class="sidebar-widget-title h6 fw-bold mb-3">
                <i class="fas fa-fire me-2 text-danger"></i>Popular Posts
            </h3>
            <ol class="list-unstyled mb-0">
                @foreach($sidebarPopularPosts as $popularPost)
                <li class="d-flex gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                    <span class="popular-post-number fw-bold text-primary flex-shrink-0">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="flex-grow-1">
                        <a href="{{ route('blog.show', $popularPost->slug) }}" class="text-dark text-decoration-none fw-medium small line-clamp-2 d-block">
                            {{ $popularPost->title }}
                        </a>
                        <small class="text-muted">
                            <i class="far fa-eye me-1"></i>{{ number_format($popularPost->views_count ?? 0) }} views
                        </small>
                    </div>
                    @if($popularPost->thumbnail)
                    <img src="{{ asset($popularPost->thumbnail) }}" alt="{{ $popularPost->title }}" class="rounded flex-shrink-0 lazy-img" width="60" height="60" style="object-fit:cover;" loading="lazy" data-src="{{ asset($popularPost->thumbnail) }}">
                    @endif
                </li>
                @endforeach
            </ol>
        </div>
    </div>
    @endif

    {{-- Categories Widget --}}
    @php
        $sidebarCategories = $sidebarCategories ?? \App\Models\Category::withCount(['posts' => fn($q) => $q->published()])->having('posts_count', '>', 0)->orderByDesc('posts_count')->take(10)->get();
    @endphp
    @if($sidebarCategories->isNotEmpty())
    <div class="sidebar-widget card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h3 class="sidebar-widget-title h6 fw-bold mb-3">
                <i class="fas fa-folder me-2 text-warning"></i>Categories
            </h3>
            <ul class="list-unstyled mb-0">
                @foreach($sidebarCategories as $sidebarCat)
                <li class="{{ !$loop->last ? 'mb-2' : '' }}">
                    <a href="{{ route('categories.show', $sidebarCat->slug) }}" class="d-flex justify-content-between align-items-center text-decoration-none text-dark py-1">
                        <span class="d-flex align-items-center gap-2">
                            @if($sidebarCat->color)
                            <span class="rounded-circle flex-shrink-0" style="width:8px;height:8px;background:{{ $sidebarCat->color }};display:inline-block;"></span>
                            @endif
                            {{ $sidebarCat->name }}
                        </span>
                        <span class="badge bg-light text-secondary rounded-pill">{{ $sidebarCat->posts_count }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-primary w-100 mt-3">View All Categories</a>
        </div>
    </div>
    @endif

    {{-- Tags Cloud Widget --}}
    @php
        $sidebarTags = $sidebarTags ?? \App\Models\Tag::withCount(['posts' => fn($q) => $q->published()])->having('posts_count', '>', 0)->orderByDesc('posts_count')->take(20)->get();
    @endphp
    @if($sidebarTags->isNotEmpty())
    <div class="sidebar-widget card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h3 class="sidebar-widget-title h6 fw-bold mb-3">
                <i class="fas fa-tags me-2 text-info"></i>Tags
            </h3>
            <div class="tags-cloud d-flex flex-wrap gap-2">
                @foreach($sidebarTags as $sidebarTag)
                <a href="{{ route('tags.show', $sidebarTag->slug) }}" class="badge text-decoration-none tag-badge bg-light text-secondary border">
                    {{ $sidebarTag->name }}
                    <sup>{{ $sidebarTag->posts_count }}</sup>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Newsletter Widget --}}
    <div class="sidebar-widget card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body text-center p-4">
            <i class="fas fa-envelope-open-text fa-2x mb-3 opacity-75"></i>
            <h3 class="h6 fw-bold mb-2">Stay Updated</h3>
            <p class="small opacity-75 mb-3">Get the latest articles delivered to your inbox.</p>
            @include('partials.newsletter-form', ['variant' => 'sidebar'])
        </div>
    </div>

    {{-- Advertisement - Sidebar Bottom --}}
    @include('partials.advertisement', ['position' => 'sidebar_bottom'])

</aside>
