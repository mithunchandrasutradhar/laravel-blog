@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="page-header bg-light border-bottom py-4">
    <div class="container">
        @include('partials.breadcrumb', [
            'breadcrumbs' => [
                ['label' => $page->title, 'url' => route('pages.show', $page->slug)],
            ]
        ])
        <h1 class="h3 fw-bold mt-2 mb-0">{{ $page->title }}</h1>
    </div>
</div>

{{-- Content --}}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="post-content">
                @if($page->content)
                    {!! $page->content !!}
                @else
                    <p class="text-muted fst-italic">No content has been added to this page yet.</p>
                    @auth
                        @if(is_admin())
                        <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-edit me-1"></i>Edit in Admin
                        </a>
                        @endif
                    @endauth
                @endif
            </div>

            <div class="mt-5 pt-4 border-top text-muted small d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span>Last updated: {{ $page->updated_at->format('F j, Y') }}</span>
                @auth
                    @if(is_admin())
                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i>Edit Page
                    </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>

@endsection
