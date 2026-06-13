@extends('layouts.app')

@section('title', $activeCategory ? $activeCategory->name . ' Videos' : 'Videos')
@section('meta_description', 'Watch our curated video collection — tutorials, talks, and more.')

@push('styles')
<style>
/* ── Page header ── */
.vp-hero {
    background: linear-gradient(150deg, #0a0f1e 0%, #111827 55%, #1a1230 100%);
    padding: 4rem 0 3.5rem;
    text-align: center;
}
.vp-hero-icon {
    width: 72px; height: 72px;
    background: rgba(239,68,68,.15);
    border: 1px solid rgba(239,68,68,.25);
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 1.25rem;
}
.vp-hero h1 { font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 800; color: #fff; margin-bottom: .5rem; }
.vp-hero p  { color: rgba(255,255,255,.55); font-size: 1rem; margin: 0; }

/* ── Filter bar ── */
.vp-filter-bar {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    position: sticky; top: 64px; z-index: 100;
}
.vp-filter-inner {
    display: flex; align-items: center; gap: .5rem;
    overflow-x: auto; padding: .875rem 0;
    scrollbar-width: none;
}
.vp-filter-inner::-webkit-scrollbar { display: none; }
.vp-tab {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .45rem 1.1rem;
    border-radius: 999px;
    font-size: .8125rem; font-weight: 600;
    white-space: nowrap; text-decoration: none;
    border: 1.5px solid transparent;
    transition: all .18s ease;
    color: #4b5563;
    background: #f3f4f6;
}
.vp-tab:hover { background: #e5e7eb; color: #111; }
.vp-tab.active { color: #fff; border-color: transparent; }
.vp-tab .vp-tab-icon { font-size: .75rem; }

/* ── Video cards ── */
.vid-card {
    border-radius: 1rem; overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.06);
    transition: transform .22s ease, box-shadow .22s ease;
    height: 100%;
    display: flex; flex-direction: column;
}
.vid-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(0,0,0,.13);
}

/* Thumbnail / player area */
.vid-thumb-wrap {
    position: relative; aspect-ratio: 16/9;
    overflow: hidden; cursor: pointer;
    background: #0f0e1e;
    flex-shrink: 0;
}
.vid-thumb-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    display: block;
    transition: transform .35s ease, opacity .25s ease;
}
.vid-card:hover .vid-thumb-wrap img { transform: scale(1.04); }

/* Play button */
.vid-play-btn {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,.28);
    transition: background .2s;
}
.vid-card:hover .vid-play-btn { background: rgba(0,0,0,.42); }
.vid-play-circle {
    width: 60px; height: 60px;
    background: rgba(239,68,68,.9);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 24px rgba(239,68,68,.5);
    transition: transform .2s ease, background .2s;
}
.vid-card:hover .vid-play-circle { transform: scale(1.1); background: #ef4444; }
.vid-play-circle i { color: #fff; font-size: 1.25rem; margin-left: 3px; }

/* Category badge on thumb */
.vid-cat-badge {
    position: absolute; top: .75rem; left: .75rem;
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .7rem; font-weight: 700;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    color: #fff;
    text-decoration: none;
}

/* Iframe (replaces thumb on click) */
.vid-thumb-wrap iframe {
    position: absolute; inset: 0;
    width: 100%; height: 100%; border: 0;
}

/* Card body */
.vid-card-body {
    padding: 1rem 1.125rem 1.25rem;
    flex: 1; display: flex; flex-direction: column;
}
.vid-cat-link {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .72rem; font-weight: 700;
    text-decoration: none; margin-bottom: .5rem;
    border-radius: 999px; padding: .2rem .6rem;
}
.vid-title {
    font-size: .9375rem; font-weight: 700;
    color: #111827; line-height: 1.45;
    margin-bottom: .4rem;
}
.vid-desc {
    font-size: .8125rem; color: #6b7280;
    line-height: 1.6; margin: 0;
    flex: 1;
}

/* Empty state */
.vp-empty {
    text-align: center; padding: 5rem 1rem;
    color: #9ca3af;
}
.vp-empty i { font-size: 3.5rem; color: #e5e7eb; margin-bottom: 1rem; display: block; }
</style>
@endpush

@section('content')

{{-- ── Hero ── --}}
<div class="vp-hero">
    <div class="container">
        <div class="vp-hero-icon">
            <i class="fab fa-youtube" style="color:#ef4444;font-size:1.75rem;"></i>
        </div>
        <h1>
            @if($activeCategory)
                <i class="{{ $activeCategory->icon ?? 'fas fa-folder' }}" style="color:{{ $activeCategory->color ?? '#4f46e5' }};margin-right:.4rem;font-size:.8em;"></i>
                {{ $activeCategory->name }} Videos
            @else
                All Videos
            @endif
        </h1>
        <p>Curated videos to help you learn and grow</p>
    </div>
</div>

{{-- ── Filter bar ── --}}
@if($categories->isNotEmpty())
<div class="vp-filter-bar">
    <div class="container">
        <div class="vp-filter-inner">

            {{-- All --}}
            <a href="{{ route('videos.index') }}"
               class="vp-tab {{ !$activeCategory ? 'active' : '' }}"
               style="{{ !$activeCategory ? 'background:#4f46e5;' : '' }}">
                <i class="fas fa-th-large vp-tab-icon"></i> All
            </a>

            @foreach($categories as $cat)
            @php $isCatActive = $activeCategory && $activeCategory->id === $cat->id; @endphp
            <a href="{{ route('videos.index', ['category' => $cat->slug]) }}"
               class="vp-tab {{ $isCatActive ? 'active' : '' }}"
               style="{{ $isCatActive ? 'background:'.($cat->color ?? '#4f46e5').';' : '' }}">
                <i class="{{ $cat->icon ?? 'fas fa-folder' }} vp-tab-icon"
                   style="{{ !$isCatActive ? 'color:'.($cat->color ?? '#4f46e5').';' : '' }}"></i>
                {{ $cat->name }}
            </a>
            @endforeach

        </div>
    </div>
</div>
@endif

{{-- ── Grid ── --}}
<section class="py-5 py-lg-6" style="background:#f8f9fc;">
    <div class="container">

        @if($videos->isEmpty())
        <div class="vp-empty">
            <i class="fab fa-youtube"></i>
            <p class="mb-3 fw-semibold" style="color:#374151;">No videos found{{ $activeCategory ? ' in this category' : '' }}.</p>
            @if($activeCategory)
            <a href="{{ route('videos.index') }}" class="btn btn-primary px-4">View all videos</a>
            @endif
        </div>
        @else

        <div class="row g-4">
            @foreach($videos as $video)
            <div class="col-md-6 col-xl-4">
                <div class="vid-card">

                    {{-- Thumbnail / player --}}
                    <div class="vid-thumb-wrap" data-embed="{{ $video->embed_url }}&autoplay=1">
                        <img src="{{ $video->thumbnail_url }}"
                             alt="{{ e($video->title) }}" loading="lazy">

                        {{-- Category badge on thumbnail --}}
                        @if($video->category)
                        <a href="{{ route('videos.index', ['category' => $video->category->slug]) }}"
                           class="vid-cat-badge"
                           style="background:{{ $video->category->color ?? '#4f46e5' }}cc;"
                           onclick="event.stopPropagation()">
                            <i class="{{ $video->category->icon ?? 'fas fa-folder' }}"></i>
                            {{ $video->category->name }}
                        </a>
                        @endif

                        {{-- Play button --}}
                        <div class="vid-play-btn" aria-label="Play video">
                            <div class="vid-play-circle">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="vid-card-body">
                        <h2 class="vid-title">{{ $video->title }}</h2>
                        @if($video->description)
                        <p class="vid-desc">{{ Str::limit($video->description, 120) }}</p>
                        @endif
                    </div>

                </div>
            </div>
            @endforeach
        </div>

        @if($videos->hasPages())
        <div class="mt-5 d-flex justify-content-center">
            {{ $videos->links() }}
        </div>
        @endif

        @endif
    </div>
</section>

@push('scripts')
<script>
document.querySelectorAll('.vid-thumb-wrap').forEach(function (wrap) {
    wrap.addEventListener('click', function () {
        var embed = this.dataset.embed;
        if (!embed) return;
        var iframe = document.createElement('iframe');
        iframe.src = embed;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        this.innerHTML = '';
        this.appendChild(iframe);
        this.style.cursor = 'default';
    });
});
</script>
@endpush

@endsection
