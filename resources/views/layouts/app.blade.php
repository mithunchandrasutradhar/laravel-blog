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
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Font Awesome 6 (local) --}}
    <link rel="stylesheet" href="{{ asset('css/fontawesome/all.min.css') }}">

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
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu shadow border-0" aria-labelledby="categoriesDropdown">
                                @foreach(($globalCategories ?? collect())->take(12) as $category)
                                <li>
                                    <a class="dropdown-item" href="{{ route('categories.show', $category->slug) }}">
                                        {{ $category->name }}
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
    <footer class="site-footer">

        {{-- Gradient accent strip --}}
        <div style="height:3px;background:linear-gradient(90deg,#4f46e5 0%,#f59e0b 50%,#4f46e5 100%);"></div>

        {{-- Main content --}}
        <div style="background:#0f0e1f;padding:4rem 0 2.5rem;">
            <div class="container">
                <div class="row g-5">

                    {{-- Brand + Description + Social --}}
                    <div class="col-lg-4 col-md-12">

                        {{-- Logo / Brand name --}}
                        @if(settings('logo_white') || settings('logo'))
                            <img src="{{ asset(settings('logo_white') ?? settings('logo')) }}"
                                 alt="{{ settings('site_name', config('app.name')) }}"
                                 style="height:38px;margin-bottom:1.25rem;display:block;">
                        @else
                            <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1.25rem;">
                                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#f59e0b);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-pen-nib" style="color:#fff;font-size:.85rem;"></i>
                                </div>
                                <span style="font-size:1.2rem;font-weight:800;color:#fff;letter-spacing:-.02em;">
                                    {{ settings('site_name', config('app.name')) }}
                                </span>
                            </div>
                        @endif

                        <p style="color:#8b8ba8;font-size:.875rem;line-height:1.75;margin-bottom:1.75rem;">
                            {{ settings('site_description', 'A modern space to read, write, and connect with ideas that inspire — quality content across technology, design, and life.') }}
                        </p>

                        {{-- Social icons — only render platforms that have a saved URL --}}
                        @php
                            $footerSocials = array_filter([
                                ['url' => settings('social_facebook'),  'icon' => 'fab fa-facebook-f',  'label' => 'Facebook'],
                                ['url' => settings('social_twitter'),   'icon' => 'fab fa-x-twitter',   'label' => 'Twitter'],
                                ['url' => settings('social_instagram'), 'icon' => 'fab fa-instagram',   'label' => 'Instagram'],
                                ['url' => settings('social_linkedin'),  'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
                                ['url' => settings('social_youtube'),   'icon' => 'fab fa-youtube',     'label' => 'YouTube'],
                                ['url' => settings('social_tiktok'),    'icon' => 'fab fa-tiktok',      'label' => 'TikTok'],
                                ['url' => settings('social_pinterest'), 'icon' => 'fab fa-pinterest-p', 'label' => 'Pinterest'],
                                ['url' => settings('social_github'),    'icon' => 'fab fa-github',      'label' => 'GitHub'],
                            ], fn($s) => !empty($s['url']));
                        @endphp
                        @if(!empty($footerSocials))
                        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                            @foreach($footerSocials as $s)
                            <a href="{{ $s['url'] }}" aria-label="{{ $s['label'] }}"
                               class="ft-social-icon"
                               target="_blank" rel="noopener noreferrer">
                                <i class="{{ $s['icon'] }}"></i>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p style="color:#5c5c7a;font-size:.8rem;font-style:italic;">
                            No social links configured.
                            <a href="{{ route('admin.settings.index') }}?tab=social" style="color:#f59e0b;">Add them →</a>
                        </p>
                        @endif
                    </div>

                    {{-- Quick Links --}}
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="ft-col-heading">Explore</div>
                        <ul style="list-style:none;margin:0;padding:0;">
                            @foreach([
                                ['route' => 'home',             'label' => 'Home'],
                                ['route' => 'blog.index',       'label' => 'Blog'],
                                ['route' => 'categories.index', 'label' => 'Categories'],
                                ['route' => 'about',            'label' => 'About'],
                                ['route' => 'contact',          'label' => 'Contact'],
                            ] as $link)
                            <li style="margin-bottom:.5rem;">
                                <a href="{{ route($link['route']) }}" class="ft-nav-link">
                                    <i class="fas fa-angle-right" style="font-size:.6rem;color:#f59e0b;margin-right:.4rem;"></i>
                                    {{ $link['label'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Recent Posts --}}
                    <div class="col-lg-3 col-sm-6">
                        <div class="ft-col-heading">Recent Posts</div>
                        @php
                            $footerPosts = $footerPosts ?? \App\Models\Post::published()
                                ->with(['category'])
                                ->latest('published_at')
                                ->take(3)
                                ->get();
                        @endphp
                        <div style="display:flex;flex-direction:column;gap:1rem;">
                            @foreach($footerPosts as $fp)
                            <div style="display:flex;gap:.875rem;align-items:flex-start;">
                                {{-- Thumbnail --}}
                                <a href="{{ route('blog.show', $fp->slug) }}"
                                   style="display:block;width:60px;height:54px;min-width:60px;border-radius:8px;overflow:hidden;flex-shrink:0;">
                                    @if($fp->thumbnail)
                                    <img src="{{ $fp->thumbnail }}" alt="{{ $fp->title }}"
                                         style="width:60px;height:54px;object-fit:cover;display:block;" loading="lazy">
                                    @else
                                    <div style="width:60px;height:54px;background:rgba(79,70,229,.3);display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image" style="color:#4f46e5;opacity:.5;font-size:.75rem;"></i>
                                    </div>
                                    @endif
                                </a>
                                {{-- Info --}}
                                <div style="flex:1;min-width:0;">
                                    <a href="{{ route('blog.show', $fp->slug) }}"
                                       style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:.8125rem;font-weight:600;line-height:1.45;color:#cccce0;text-decoration:none;margin-bottom:.3rem;display:block;">
                                        {{ $fp->title }}
                                    </a>
                                    <div style="font-size:.7rem;color:#5c5c7a;">
                                        @if($fp->category)
                                        <span style="color:#f59e0b;">{{ $fp->category->name }}</span>
                                        <span style="margin:0 .25rem;">·</span>
                                        @endif
                                        {{ $fp->published_at?->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                            <div style="border-top:1px solid rgba(255,255,255,.06);"></div>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Newsletter --}}
                    <div class="col-lg-3 col-md-12">
                        <div class="ft-col-heading">Stay Updated</div>
                        <p style="color:#8b8ba8;font-size:.875rem;line-height:1.7;margin-bottom:1rem;">
                            Get fresh articles delivered to your inbox every week.
                        </p>

                        {{-- Newsletter form --}}
                        <div x-data="newsletterForm()" id="newsletter-footer-v2">
                            <form @submit.prevent="submit" novalidate>
                                @csrf
                                {{-- Form — visible by default --}}
                                <div x-show="!submitted">
                                    <div style="margin-bottom:.5rem;">
                                        <input type="email" x-model="email"
                                               placeholder="Your email address"
                                               required
                                               style="width:100%;padding:.6rem .875rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:8px;color:#fff;font-size:.875rem;outline:none;box-sizing:border-box;"
                                               onfocus="this.style.borderColor='#4f46e5';this.style.boxShadow='0 0 0 3px rgba(79,70,229,.2)'"
                                               onblur="this.style.borderColor='rgba(255,255,255,.12)';this.style.boxShadow='none'">
                                    </div>
                                    <button type="submit" :disabled="loading"
                                            style="width:100%;padding:.65rem 1rem;background:linear-gradient(135deg,#4f46e5,#6366f1);border:none;border-radius:8px;color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;transition:opacity .2s;"
                                            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                                        <span x-show="!loading"><i class="fas fa-paper-plane" style="margin-right:.4rem;"></i>Subscribe</span>
                                        <span x-show="loading" style="display:none;"><i class="fas fa-spinner fa-spin" style="margin-right:.4rem;"></i>Subscribing…</span>
                                    </button>
                                    <div x-show="error" style="display:none;margin-top:.5rem;">
                                        <small style="color:#ef4444;" x-text="error"></small>
                                    </div>
                                </div>
                                {{-- Success — hidden by default --}}
                                <div x-show="submitted" style="display:none;text-align:center;padding:1.25rem;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);border-radius:10px;">
                                    <i class="fas fa-check-circle" style="color:#10b981;font-size:1.5rem;margin-bottom:.5rem;display:block;"></i>
                                    <p style="color:#fff;font-weight:600;margin-bottom:.25rem;">You're in!</p>
                                    <small style="color:#8b8ba8;">Thanks for subscribing.</small>
                                </div>
                            </form>
                        </div>

                        <p style="margin-top:.75rem;font-size:.72rem;color:#4a4a6a;">
                            <i class="fas fa-lock" style="margin-right:.3rem;"></i>No spam. Unsubscribe anytime.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div style="background:#0d0c1c;border-top:1px solid rgba(255,255,255,.06);">
            <div class="container">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1.125rem 0;flex-wrap:wrap;gap:.5rem;">
                    <span style="font-size:.78rem;color:#4a4a6a;">
                        &copy; {{ date('Y') }}
                        <strong style="color:#7b7b9a;">{{ settings('site_name', config('app.name')) }}</strong>.
                        {{ settings('copyright_text', 'All rights reserved.') }}
                    </span>
                    <span style="font-size:.78rem;color:#4a4a6a;">
                        Made with <i class="fas fa-heart" style="color:#f59e0b;font-size:.65rem;"></i> using
                        <a href="https://laravel.com" target="_blank" rel="noopener"
                           style="color:#f59e0b;text-decoration:none;font-weight:500;">Laravel</a>
                    </span>
                </div>
            </div>
        </div>

    </footer>

    {{-- Back to Top Button --}}
    <button class="btn btn-primary btn-back-to-top rounded-circle shadow" id="backToTop" aria-label="Back to top" style="display:none;">
        <i class="fas fa-chevron-up"></i>
    </button>

    {{-- Bootstrap 5.3 JS Bundle (local) --}}
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    {{-- Custom JS --}}
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
