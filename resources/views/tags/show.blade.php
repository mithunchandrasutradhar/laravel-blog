@extends('layouts.app')

@php
    $seo = [
        'title'     => '#' . $tag->name . ' — ' . settings('site_name', config('app.name')),
        'canonical' => route('tags.show', $tag->slug),
    ];
@endphp

@section('content')

    {{-- Tag Hero --}}
    <div class="tag-hero py-4 bg-light border-bottom">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Blog', 'url' => route('blog.index')],
                    ['label' => 'Tags', 'url' => route('tags.index')],
                    ['label' => '#' . $tag->name, 'url' => route('tags.show', $tag->slug)],
                ]
            ])
            <div class="d-flex align-items-center gap-3 mt-3">
                <div class="tag-icon rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="fas fa-tag text-info fs-5"></i>
                </div>
                <div>
                    <h1 class="h3 fw-bold mb-0">#{{ $tag->name }}</h1>
                    <small class="text-muted">{{ number_format($posts->total()) }} {{ Str::plural('post', $posts->total()) }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-8">
                @if($posts->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-tag fa-3x mb-3 opacity-25"></i>
                    <h3 class="h5">No posts with this tag yet</h3>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary mt-2">Browse All Posts</a>
                </div>
                @else
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-sm-6">
                        @include('partials.post-card', ['post' => $post])
                    </div>
                    @endforeach
                </div>

                @include('partials.pagination', ['paginator' => $posts])
                @endif
            </div>

            <div class="col-lg-4">
                @include('partials.sidebar')
            </div>
        </div>
    </div>

@endsection
