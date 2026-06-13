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
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
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

    {{-- Header --}}
    <header class="site-header sticky-top">

        {{-- 3-px gradient accent bar --}}
        <div class="header-accent-bar"></div>

        <nav class="navbar navbar-expand-lg" id="mainNavbar">
            <div class="container">

                {{-- ── Logo ── --}}
                <a class="navbar-brand d-flex align-items-center gap-2 me-4" href="{{ route('home') }}">
                    @if(settings('logo'))
                        <img src="{{ asset('storage/' . settings('logo')) }}"
                             alt="{{ settings('site_name', 'Mithun Blog') }}"
                             height="38" style="display:block;">
                    @else
                        {{-- Default logo: SVG icon + wordmark --}}
                        <span class="logo-icon-wrap" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="38" height="38">
                                <defs>
                                    <linearGradient id="hdr-g" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%"   stop-color="#4f46e5"/>
                                        <stop offset="60%"  stop-color="#6d28d9"/>
                                        <stop offset="100%" stop-color="#7c3aed"/>
                                    </linearGradient>
                                    <linearGradient id="hdr-s" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="rgba(255,255,255,.2)"/>
                                        <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
                                    </linearGradient>
                                </defs>
                                <rect width="48" height="48" rx="12" fill="url(#hdr-g)"/>
                                <rect width="48" height="24" rx="12" fill="url(#hdr-s)"/>
                                <path d="M9 34 L9 15 L24 27 L39 15 L39 34"
                                      fill="none" stroke="white" stroke-width="3.8"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="8" y="39" width="32" height="3" rx="1.5" fill="#f59e0b"/>
                            </svg>
                        </span>
                        <span class="logo-wordmark d-none d-sm-flex">
                            <span class="logo-name">{{ settings('site_name', 'Mithun') }}</span>
                            <span class="logo-sub">Blog</span>
                        </span>
                    @endif
                </a>

                {{-- ── Mobile controls ── --}}
                <div class="d-flex align-items-center gap-2 d-lg-none ms-auto me-2">
                    <a href="{{ route('search') }}" class="btn p-1 border-0" style="color:#6b7280;" aria-label="Search">
                        <i class="fas fa-search" style="font-size:.9rem;"></i>
                    </a>
                    <button class="navbar-toggler" type="button"
                            data-bs-toggle="collapse" data-bs-target="#navbarMain"
                            aria-controls="navbarMain" aria-expanded="false"
                            aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                {{-- ── Collapsible nav ── --}}
                <div class="collapse navbar-collapse" id="navbarMain">

                    {{-- Nav links --}}
                    <ul class="navbar-nav mx-auto gap-1 mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                               href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}"
                               href="{{ route('blog.index') }}">Blog</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                               href="{{ route('categories.index') }}"
                               id="navCategories" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                                <i class="fas fa-chevron-down caret-icon"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navCategories">
                                @foreach(($globalCategories ?? collect())->take(12) as $cat)
                                <li>
                                    <a class="dropdown-item" href="{{ route('categories.show', $cat->slug) }}">
                                        <span class="di-icon">
                                            <i class="{{ $cat->icon ?? 'fas fa-folder' }}"></i>
                                        </span>
                                        {{ $cat->name }}
                                    </a>
                                </li>
                                @endforeach
                                <li><hr class="dropdown-divider mx-2 my-1"></li>
                                <li>
                                    <a class="dropdown-item fw-semibold" href="{{ route('categories.index') }}">
                                        <span class="di-icon" style="background:var(--brand-accent-light);color:var(--brand-accent-dark);">
                                            <i class="fas fa-th-large"></i>
                                        </span>
                                        All Categories
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('videos.*') ? 'active' : '' }}"
                               href="{{ route('videos.index') }}">Videos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}"
                               href="{{ route('about') }}">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                               href="{{ route('contact') }}">Contact</a>
                        </li>
                    </ul>

                    {{-- Right side: Search + Auth --}}
                    <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">

                        {{-- Desktop search --}}
                        <form class="d-none d-lg-block header-search-wrap"
                              action="{{ route('search') }}" method="GET" role="search">
                            <i class="fas fa-search header-search-icon" aria-hidden="true"></i>
                            <input type="search" name="q"
                                   class="header-search-input"
                                   placeholder="Search articles…"
                                   value="{{ request('q') }}"
                                   autocomplete="off"
                                   aria-label="Search articles">
                            <div class="search-suggestions dropdown-menu w-100 shadow"
                                 id="searchSuggestions"></div>
                        </form>

                        {{-- Auth --}}
                        @guest
                            <a href="{{ route('login') }}" class="btn-nav-login">Sign in</a>
                            <a href="{{ route('register') }}" class="btn-nav-register">Get Started</a>
                        @else
                            <div class="dropdown">
                                <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset(auth()->user()->avatar) }}"
                                             alt="{{ auth()->user()->name }}"
                                             class="rounded-circle"
                                             width="34" height="34"
                                             style="object-fit:cover;border:2px solid #e2e8f0;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                             style="width:34px;height:34px;font-size:.8rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);border:2px solid #e2e8f0;">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="d-none d-xl-inline fw-semibold" style="font-size:.875rem;color:#374151;">
                                        {{ auth()->user()->name }}
                                    </span>
                                    <i class="fas fa-chevron-down d-none d-xl-inline" style="font-size:.55rem;color:#9ca3af;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="px-3 py-2 border-bottom mb-1">
                                        <div class="fw-semibold" style="font-size:.875rem;color:#111;">{{ auth()->user()->name }}</div>
                                        <div style="font-size:.75rem;color:#6b7280;">{{ auth()->user()->email }}</div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <span class="di-icon"><i class="fas fa-user"></i></span>Profile
                                        </a>
                                    </li>
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('editor'))
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <span class="di-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-tachometer-alt"></i></span>Dashboard
                                        </a>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider mx-2 my-1"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <span class="di-icon" style="background:#fee2e2;color:#ef4444;"><i class="fas fa-sign-out-alt"></i></span>Sign out
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endguest

                        {{-- Mobile search (inside collapse) --}}
                        <form class="d-lg-none w-100 mt-2" action="{{ route('search') }}" method="GET">
                            <div class="input-group input-group-sm">
                                <input type="search" name="q" class="form-control rounded-start-pill"
                                       placeholder="Search…" value="{{ request('q') }}">
                                <button class="btn btn-primary rounded-end-pill" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>

                    </div>
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
                            <img src="{{ asset('storage/' . (settings('logo_white') ?? settings('logo'))) }}"
                                 alt="{{ settings('site_name', 'Mithun Blog') }}"
                                 style="height:38px;margin-bottom:1.25rem;display:block;">
                        @else
                            <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;text-decoration:none;">
                                {{-- Same SVG icon, works on dark background --}}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="42" height="42" style="flex-shrink:0;">
                                    <defs>
                                        <linearGradient id="ft-g" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%"   stop-color="#4f46e5"/>
                                            <stop offset="60%"  stop-color="#6d28d9"/>
                                            <stop offset="100%" stop-color="#7c3aed"/>
                                        </linearGradient>
                                        <linearGradient id="ft-s" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="rgba(255,255,255,.2)"/>
                                            <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
                                        </linearGradient>
                                    </defs>
                                    <rect width="48" height="48" rx="12" fill="url(#ft-g)"/>
                                    <rect width="48" height="24" rx="12" fill="url(#ft-s)"/>
                                    <path d="M9 34 L9 15 L24 27 L39 15 L39 34"
                                          fill="none" stroke="white" stroke-width="3.8"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="8" y="39" width="32" height="3" rx="1.5" fill="#f59e0b"/>
                                </svg>
                                <span style="display:flex;flex-direction:column;line-height:1;gap:3px;">
                                    <span style="font-size:1.15rem;font-weight:800;color:#fff;letter-spacing:-.03em;">{{ settings('site_name', 'Mithun') }}</span>
                                    <span style="font-size:.52rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#f59e0b;">Blog</span>
                                </span>
                            </a>
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
                                ['route' => 'videos.index',     'label' => 'Videos'],
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
