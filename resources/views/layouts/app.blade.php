<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- SEO Meta Tags --}}
    <title>{{ $seo['title'] ?? config('app.name', 'Blog') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? settings('seo_description', 'A modern blog platform') }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? settings('seo_keywords', '') }}">
    @if(isset($seo['robots']))
    <meta name="robots" content="{{ $seo['robots'] }}">
    @endif

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

    {{-- Open Graph Tags --}}
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? config('app.name') }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? settings('seo_description', '') }}">
    <meta property="og:url" content="{{ $seo['og_url'] ?? url()->current() }}">
    <meta property="og:image" content="{{ $seo['og_image'] ?? asset(settings('og_default_image', 'images/og-default.jpg')) }}">
    <meta property="og:site_name" content="{{ settings('site_name', config('app.name')) }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    {{-- Twitter Card Tags --}}
    <meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $seo['title'] ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['description'] ?? settings('seo_description', '') }}">
    <meta name="twitter:image" content="{{ $seo['twitter_image'] ?? $seo['og_image'] ?? asset(settings('og_default_image', 'images/og-default.jpg')) }}">
    @if(settings('twitter_site'))
    <meta name="twitter:site" content="{{ settings('twitter_site') }}">
    @endif

    {{-- Favicon --}}
    @if(settings('favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset(settings('favicon')) }}">
    <link rel="apple-touch-icon" href="{{ asset(settings('favicon')) }}">
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6g==" crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')

    {{-- Google Analytics 4 --}}
    @if(settings('ga4_measurement_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ settings('ga4_measurement_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ settings('ga4_measurement_id') }}');
    </script>
    @endif

    {{-- Additional head content --}}
    @stack('head')
</head>
<body class="{{ $bodyClass ?? '' }}">

    {{-- Reading Progress Bar --}}
    <div id="reading-progress-bar" class="reading-progress-bar d-none"></div>

    {{-- Header / Navbar --}}
    <header class="site-header sticky-top bg-white shadow-sm">
        <nav class="navbar navbar-expand-lg navbar-light" id="mainNavbar">
            <div class="container">
                {{-- Logo --}}
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                    @if(settings('logo'))
                        <img src="{{ asset(settings('logo')) }}" alt="{{ settings('site_name', config('app.name')) }}" height="40" class="site-logo">
                    @else
                        <span class="fw-bold fs-4 text-primary">{{ settings('site_name', config('app.name')) }}</span>
                    @endif
                </a>

                {{-- Mobile: Search Icon + Hamburger --}}
                <div class="d-flex align-items-center gap-2 d-lg-none">
                    <button class="btn btn-link text-dark p-1" id="mobileSearchToggle" aria-label="Toggle search">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                {{-- Nav Links --}}
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">
                                Blog
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu shadow border-0" aria-labelledby="categoriesDropdown">
                                @php
                                    $navCategories = $navCategories ?? \App\Models\Category::withCount('posts')->having('posts_count', '>', 0)->orderBy('name')->take(10)->get();
                                @endphp
                                @foreach($navCategories as $category)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('categories.show', $category->slug) }}">
                                        {{ $category->name }}
                                        <span class="badge bg-primary rounded-pill">{{ $category->posts_count }}</span>
                                    </a>
                                </li>
                                @endforeach
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item fw-semibold text-primary" href="{{ route('categories.index') }}">
                                        <i class="fas fa-th-large me-1"></i> All Categories
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                                About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                                Contact
                            </a>
                        </li>
                    </ul>

                    {{-- Desktop Search Bar --}}
                    <form class="d-none d-lg-flex search-form position-relative" action="{{ route('search') }}" method="GET" role="search">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control form-control-sm rounded-start-pill border-end-0" placeholder="Search posts..." value="{{ request('q') }}" autocomplete="off" id="desktopSearch" aria-label="Search">
                            <button class="btn btn-outline-secondary rounded-end-pill border-start-0" type="submit" aria-label="Submit search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="search-suggestions dropdown-menu w-100 shadow" id="searchSuggestions"></div>
                    </form>

                    {{-- Auth Links --}}
                    <div class="d-flex align-items-center ms-3 gap-2">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                        @else
                            <div class="dropdown">
                                <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:14px;">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="d-none d-xl-inline">{{ auth()->user()->name }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2 text-muted"></i>Profile</a></li>
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('editor'))
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2 text-muted"></i>Dashboard</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endguest
                    </div>

                    {{-- Mobile Search (visible inside collapse) --}}
                    <form class="d-lg-none mt-3 mb-1" action="{{ route('search') }}" method="GET" role="search">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control" placeholder="Search posts..." value="{{ request('q') }}" aria-label="Search">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    {{-- Flash Messages --}}
    @include('partials.flash-messages')

    {{-- Main Content --}}
    <main id="main-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="site-footer bg-dark text-light pt-5 mt-5">
        <div class="container">
            <div class="row g-4">
                {{-- Brand Column --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand mb-3">
                        @if(settings('logo_white') || settings('logo'))
                            <img src="{{ asset(settings('logo_white') ?? settings('logo')) }}" alt="{{ settings('site_name', config('app.name')) }}" height="40" class="mb-3">
                        @else
                            <h5 class="fw-bold text-white">{{ settings('site_name', config('app.name')) }}</h5>
                        @endif
                    </div>
                    <p class="text-muted small">{{ settings('site_description', 'A place to read, write, and connect with stories that matter.') }}</p>
                    {{-- Social Links --}}
                    <div class="social-links d-flex gap-3 mt-3">
                        @if(settings('social_facebook'))
                        <a href="{{ settings('social_facebook') }}" class="text-muted fs-5 social-link" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if(settings('social_twitter'))
                        <a href="{{ settings('social_twitter') }}" class="text-muted fs-5 social-link" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                        @endif
                        @if(settings('social_instagram'))
                        <a href="{{ settings('social_instagram') }}" class="text-muted fs-5 social-link" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if(settings('social_linkedin'))
                        <a href="{{ settings('social_linkedin') }}" class="text-muted fs-5 social-link" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        @endif
                        @if(settings('social_youtube'))
                        <a href="{{ settings('social_youtube') }}" class="text-muted fs-5 social-link" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if(settings('social_rss', true))
                        <a href="{{ route('rss.feed') }}" class="text-muted fs-5 social-link" aria-label="RSS Feed"><i class="fas fa-rss"></i></a>
                        @endif
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-semibold mb-3 footer-heading">Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-muted text-decoration-none">Blog</a></li>
                        <li><a href="{{ route('categories.index') }}" class="text-muted text-decoration-none">Categories</a></li>
                        <li><a href="{{ route('about') }}" class="text-muted text-decoration-none">About</a></li>
                        <li><a href="{{ route('contact') }}" class="text-muted text-decoration-none">Contact</a></li>
                        @if(settings('privacy_policy_page'))
                        <li><a href="{{ route('page', settings('privacy_policy_page')) }}" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        @endif
                        @if(settings('terms_page'))
                        <li><a href="{{ route('page', settings('terms_page')) }}" class="text-muted text-decoration-none">Terms of Service</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Recent Posts --}}
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-semibold mb-3 footer-heading">Recent Posts</h6>
                    @php
                        $footerPosts = $footerPosts ?? \App\Models\Post::published()->latest('published_at')->take(4)->get();
                    @endphp
                    <ul class="list-unstyled footer-recent-posts">
                        @foreach($footerPosts as $fp)
                        <li class="d-flex gap-2 mb-3">
                            @if($fp->thumbnail)
                            <img src="{{ asset($fp->thumbnail) }}" alt="{{ $fp->title }}" class="rounded flex-shrink-0" width="56" height="56" style="object-fit:cover;" loading="lazy">
                            @endif
                            <div>
                                <a href="{{ route('blog.show', $fp->slug) }}" class="text-muted text-decoration-none small fw-medium line-clamp-2">{{ $fp->title }}</a>
                                <div class="text-muted" style="font-size:.75rem;">{{ $fp->published_at?->format('M d, Y') }}</div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Newsletter --}}
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-semibold mb-3 footer-heading">Newsletter</h6>
                    <p class="text-muted small">Get the latest posts delivered to your inbox.</p>
                    @include('partials.newsletter-form', ['variant' => 'footer'])
                </div>
            </div>

            <hr class="border-secondary mt-4">

            {{-- Copyright --}}
            <div class="row align-items-center py-3">
                <div class="col-md-6 text-center text-md-start">
                    <small class="text-muted">
                        &copy; {{ date('Y') }} {{ settings('site_name', config('app.name')) }}.
                        {{ settings('copyright_text', 'All rights reserved.') }}
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <small class="text-muted">
                        Built with <i class="fas fa-heart text-danger"></i> using <a href="https://laravel.com" class="text-muted" target="_blank" rel="noopener">Laravel</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    {{-- Back to Top Button --}}
    <button class="btn btn-primary btn-back-to-top rounded-circle shadow" id="backToTop" aria-label="Back to top" style="display:none;">
        <i class="fas fa-chevron-up"></i>
    </button>

    {{-- Bootstrap 5.3 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmErciUSRKxVKXFcO1HiN7HHLoX5" crossorigin="anonymous"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    {{-- Custom JS --}}
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
