@extends('layouts.app')

@php
    $seo = [
        'title'     => 'About Us — ' . settings('site_name', config('app.name')),
        'canonical' => route('about'),
    ];
@endphp

@section('content')

    {{-- Page Header --}}
    <div class="page-header bg-light border-bottom py-4">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'About', 'url' => route('about')],
                ]
            ])
            <h1 class="h3 fw-bold mt-2 mb-0">About Us</h1>
        </div>
    </div>

    {{-- About Hero --}}
    <section class="about-hero py-5 bg-white">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    @if(settings('about_title'))
                    <h2 class="display-6 fw-bold mb-3">{{ settings('about_title') }}</h2>
                    @else
                    <h2 class="display-6 fw-bold mb-3">About {{ settings('site_name', config('app.name')) }}</h2>
                    @endif

                    @if(settings('about_subtitle'))
                    <p class="lead text-muted mb-4">{{ settings('about_subtitle') }}</p>
                    @endif

                    @if(settings('about_content'))
                    <div class="about-body prose">
                        {!! settings('about_content') !!}
                    </div>
                    @endif

                    <div class="mt-4 d-flex gap-3 flex-wrap">
                        <a href="{{ route('blog.index') }}" class="btn btn-primary px-4">
                            <i class="fas fa-newspaper me-2"></i>Read Our Blog
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>

                @if(settings('about_image'))
                <div class="col-lg-6">
                    <img src="{{ asset(settings('about_image')) }}" alt="About {{ settings('site_name', config('app.name')) }}" class="img-fluid rounded-3 shadow">
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    @if(settings('about_show_stats', true))
    <section class="about-stats py-5 bg-primary text-white">
        <div class="container">
            <div class="row g-4 text-center justify-content-center">
                @php
                    $totalPosts  = \App\Models\Post::published()->count();
                    $totalViews  = \App\Models\Post::published()->sum('views_count');
                    $totalAuthors = \App\Models\User::whereHas('posts')->count();
                    $totalSubs   = \App\Models\Subscriber::count();
                @endphp
                <div class="col-6 col-md-3">
                    <div class="h2 fw-black mb-1">{{ number_format($totalPosts) }}+</div>
                    <div class="opacity-75">Articles Published</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="h2 fw-black mb-1">{{ number_format($totalViews) }}+</div>
                    <div class="opacity-75">Total Reads</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="h2 fw-black mb-1">{{ number_format($totalAuthors) }}+</div>
                    <div class="opacity-75">Authors</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="h2 fw-black mb-1">{{ number_format($totalSubs) }}+</div>
                    <div class="opacity-75">Subscribers</div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Team Section --}}
    @if(isset($team) && $team->isNotEmpty())
    <section class="about-team py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold mb-2">Meet the Team</h2>
                <p class="text-muted">The people behind {{ settings('site_name', config('app.name')) }}</p>
            </div>
            <div class="row g-4 justify-content-center">
                @foreach($team as $member)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="team-card card border-0 shadow-sm text-center h-100 p-4">
                        @if($member->avatar)
                        <img src="{{ asset($member->avatar) }}" alt="{{ $member->name }}" class="rounded-circle mx-auto mb-3" width="80" height="80" style="object-fit:cover;">
                        @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3 fw-bold fs-4" style="width:80px;height:80px;">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        @endif
                        <h3 class="h6 fw-bold mb-1">{{ $member->name }}</h3>
                        @if($member->title)
                        <p class="text-muted small mb-2">{{ $member->title }}</p>
                        @endif
                        <a href="{{ route('authors.show', $member->username ?? $member->id) }}" class="btn btn-sm btn-outline-primary mt-auto">
                            View Profile
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Mission / Values --}}
    @if(settings('about_mission') || settings('about_values'))
    <section class="about-mission py-5">
        <div class="container">
            <div class="row g-5 justify-content-center">
                @if(settings('about_mission'))
                <div class="col-md-6">
                    <div class="p-4 rounded-3 border h-100">
                        <i class="fas fa-bullseye fa-2x text-primary mb-3 d-block"></i>
                        <h3 class="h5 fw-bold mb-2">Our Mission</h3>
                        <p class="text-muted mb-0">{!! settings('about_mission') !!}</p>
                    </div>
                </div>
                @endif
                @if(settings('about_values'))
                <div class="col-md-6">
                    <div class="p-4 rounded-3 border h-100">
                        <i class="fas fa-heart fa-2x text-danger mb-3 d-block"></i>
                        <h3 class="h5 fw-bold mb-2">Our Values</h3>
                        <p class="text-muted mb-0">{!! settings('about_values') !!}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="about-cta py-5 bg-light border-top">
        <div class="container text-center">
            <h2 class="h4 fw-bold mb-2">Stay in the Loop</h2>
            <p class="text-muted mb-4">Subscribe to our newsletter and never miss a new article.</p>
            <div class="row justify-content-center">
                <div class="col-md-5">
                    @include('partials.newsletter-form', ['variant' => 'inline'])
                </div>
            </div>
        </div>
    </section>

@endsection
