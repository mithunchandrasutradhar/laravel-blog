@extends('layouts.app')

@section('title', 'Saved Posts')

@section('content')
<div class="container py-5">
    @include('partials.breadcrumb', ['items' => [['label' => 'Saved Posts']]])

    <h1 class="h2 fw-bold mb-4">
        <i class="fas fa-bookmark text-primary me-2"></i> Saved Posts
    </h1>

    @if($posts->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-bookmark fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No saved posts yet</h4>
            <p class="text-muted">Browse our blog and bookmark posts you'd like to read later.</p>
            <a href="{{ route('blog.index') }}" class="btn btn-primary mt-2">Browse Blog</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($posts as $post)
                <div class="col-md-6 col-lg-4">
                    @include('partials.post-card', ['post' => $post])
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $posts->links('partials.pagination') }}
        </div>
    @endif
</div>
@endsection
