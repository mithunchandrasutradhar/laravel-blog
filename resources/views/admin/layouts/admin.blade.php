<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — {{ config('app.name', 'Blog') }}</title>

    {{-- Bootstrap 5.3 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Font Awesome 6 (local) --}}
    <link rel="stylesheet" href="{{ asset('css/fontawesome/all.min.css') }}">
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background-color: #1a1d23;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: width 0.25s ease;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        #sidebar .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            white-space: nowrap;
            overflow: hidden;
        }

        #sidebar .brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        #sidebar .brand-name {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            margin-left: .75rem;
            transition: opacity 0.2s;
        }

        #sidebar.collapsed .brand-name,
        #sidebar.collapsed .nav-label,
        #sidebar.collapsed .submenu-arrow,
        #sidebar.collapsed .nav-section-title {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
        }

        #sidebar .nav-section-title {
            color: #6c757d;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 1.25rem 1.25rem .4rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s;
        }

        #sidebar .nav-item-link {
            display: flex;
            align-items: center;
            padding: .55rem 1.25rem;
            color: #adb5bd;
            text-decoration: none;
            border-radius: 0;
            transition: background .15s, color .15s;
            white-space: nowrap;
            position: relative;
        }

        #sidebar .nav-item-link:hover,
        #sidebar .nav-item-link.active {
            background-color: rgba(255,255,255,.07);
            color: #fff;
        }

        #sidebar .nav-item-link.active {
            border-left: 3px solid #0d6efd;
        }

        #sidebar .nav-item-link .nav-icon {
            width: 20px;
            text-align: center;
            flex-shrink: 0;
            font-size: .9rem;
        }

        #sidebar .nav-label {
            margin-left: .75rem;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        #sidebar .submenu-arrow {
            margin-left: auto;
            font-size: .7rem;
            transition: transform .25s, opacity 0.2s;
        }

        #sidebar .submenu-arrow.open {
            transform: rotate(90deg);
        }

        #sidebar .submenu {
            background-color: rgba(0,0,0,.2);
            overflow: hidden;
        }

        #sidebar .submenu .nav-item-link {
            padding-left: 3.1rem;
            font-size: .875rem;
        }

        #sidebar.collapsed .submenu {
            display: none !important;
        }

        .badge-sidebar {
            margin-left: auto;
            font-size: .65rem;
        }

        /* ── Top bar ── */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            z-index: 1030;
            transition: left 0.25s ease;
            gap: .75rem;
        }

        #topbar.sidebar-collapsed {
            left: var(--sidebar-collapsed-width);
        }

        /* ── Main wrapper ── */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
            transition: margin-left 0.25s ease;
        }

        #main-wrapper.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        #page-content {
            padding: 1.5rem;
        }

        /* ── Utility ── */
        .avatar-sm {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
        }

        .notification-dot {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: #6c757d;
        }

        /* Toast container */
        .toast-container {
            z-index: 9999;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.mobile-open {
                left: 0;
            }
            #topbar {
                left: 0 !important;
            }
            #main-wrapper {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 1039;
            }
            .sidebar-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ════════════════════════ SIDEBAR ════════════════════════ --}}
<nav id="sidebar">
    {{-- Brand --}}
    <div class="sidebar-brand">
        <img src="{{ asset('images/admin-logo.png') }}" alt="Logo" class="brand-logo"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div style="display:none;width:32px;height:32px;background:#0d6efd;border-radius:8px;align-items:center;justify-content:center;">
            <i class="fas fa-pencil-alt text-white" style="font-size:.8rem;"></i>
        </div>
        <span class="brand-name">{{ config('app.name', 'BlogAdmin') }}</span>
    </div>

    {{-- Navigation --}}
    <div class="flex-grow-1 overflow-y-auto py-2" style="scrollbar-width:thin;scrollbar-color:#343a40 transparent;">

        {{-- MAIN --}}
        <div class="nav-section-title">Main</div>

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>

        {{-- Posts --}}
        <div x-data="{ open: {{ request()->routeIs('admin.posts.*') ? 'true' : 'false' }} }">
            <a href="#" class="nav-item-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"
               @click.prevent="open = !open">
                <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
                <span class="nav-label">Posts</span>
                <i class="fas fa-chevron-right submenu-arrow" :class="{ open: open }"></i>
            </a>
            <div class="submenu" x-show="open" x-collapse>
                <a href="{{ route('admin.posts.index') }}"
                   class="nav-item-link {{ request()->routeIs('admin.posts.index') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fas fa-list"></i></span>
                    <span class="nav-label">All Posts</span>
                </a>
                <a href="{{ route('admin.posts.create') }}"
                   class="nav-item-link {{ request()->routeIs('admin.posts.create') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fas fa-plus"></i></span>
                    <span class="nav-label">Add New</span>
                </a>
            </div>
        </div>

        {{-- Categories --}}
        <a href="{{ route('admin.categories.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-folder"></i></span>
            <span class="nav-label">Categories</span>
        </a>

        {{-- Tags --}}
        <a href="{{ route('admin.tags.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-tags"></i></span>
            <span class="nav-label">Tags</span>
        </a>

        {{-- Videos --}}
        <a href="{{ route('admin.videos.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fab fa-youtube"></i></span>
            <span class="nav-label">Videos</span>
        </a>

        {{-- Comments --}}
        <a href="{{ route('admin.comments.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-comments"></i></span>
            <span class="nav-label">Comments</span>
            @php $pendingComments = \App\Models\Comment::where('status','pending')->count() @endphp
            @if($pendingComments > 0)
                <span class="badge bg-danger badge-sidebar">{{ $pendingComments }}</span>
            @endif
        </a>

        {{-- Media Library --}}
        <a href="{{ route('admin.media.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-images"></i></span>
            <span class="nav-label">Media Library</span>
        </a>

        {{-- PEOPLE --}}
        <div class="nav-section-title">People</div>

        {{-- Users --}}
        <div x-data="{ open: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
            <a href="#" class="nav-item-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               @click.prevent="open = !open">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span class="nav-label">Users</span>
                <i class="fas fa-chevron-right submenu-arrow" :class="{ open: open }"></i>
            </a>
            <div class="submenu" x-show="open" x-collapse>
                <a href="{{ route('admin.users.index') }}"
                   class="nav-item-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fas fa-list"></i></span>
                    <span class="nav-label">All Users</span>
                </a>
                <a href="{{ route('admin.users.create') }}"
                   class="nav-item-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fas fa-plus"></i></span>
                    <span class="nav-label">Add New</span>
                </a>
            </div>
        </div>

        {{-- Subscribers --}}
        <a href="{{ route('admin.subscribers.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-envelope"></i></span>
            <span class="nav-label">Subscribers</span>
        </a>

        {{-- MONETIZATION --}}
        <div class="nav-section-title">Monetization</div>

        {{-- Advertisements --}}
        <a href="{{ route('admin.advertisements.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.advertisements.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-ad"></i></span>
            <span class="nav-label">Advertisements</span>
        </a>

        {{-- REPORTS --}}
        <div class="nav-section-title">Reports</div>

        {{-- Analytics --}}
        <a href="{{ route('admin.analytics.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            <span class="nav-label">Analytics</span>
        </a>

        {{-- SYSTEM --}}
        <div class="nav-section-title">System</div>

        {{-- Settings --}}
        <a href="{{ route('admin.settings.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-cog"></i></span>
            <span class="nav-label">Settings</span>
        </a>

    </div>

    {{-- Sidebar footer --}}
    <div class="p-3 border-top" style="border-color:rgba(255,255,255,.08)!important;">
        <a href="{{ route('home') }}" target="_blank"
           class="nav-item-link rounded px-2 py-2" style="font-size:.8rem;">
            <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
            <span class="nav-label">View Site</span>
        </a>
    </div>
</nav>

{{-- ════════════════════════ TOP BAR ════════════════════════ --}}
<header id="topbar">
    {{-- Toggle sidebar --}}
    <button class="btn btn-link text-secondary p-1" id="sidebarToggle" title="Toggle Sidebar">
        <i class="fas fa-bars fa-lg"></i>
    </button>

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="d-none d-md-block flex-grow-1">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            @yield('breadcrumb')
        </ol>
    </nav>

    {{-- Right side --}}
    <div class="d-flex align-items-center gap-2 ms-auto">

        {{-- Notifications --}}
        <div class="dropdown">
            <button class="btn btn-link text-secondary p-1 position-relative" data-bs-toggle="dropdown" title="Notifications">
                <i class="fas fa-bell fa-lg"></i>
                <span class="notification-dot"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow" style="width:320px;max-height:400px;overflow-y:auto;">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <strong class="small">Notifications</strong>
                    <a href="#" class="small text-decoration-none">Mark all read</a>
                </div>
                <a class="dropdown-item py-2 border-bottom" href="#">
                    <div class="d-flex gap-2">
                        <div class="text-primary mt-1"><i class="fas fa-comment-dots"></i></div>
                        <div>
                            <div class="small fw-semibold">New comment awaiting approval</div>
                            <div class="text-muted" style="font-size:.75rem;">2 minutes ago</div>
                        </div>
                    </div>
                </a>
                <a class="dropdown-item py-2" href="#">
                    <div class="d-flex gap-2">
                        <div class="text-success mt-1"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <div class="small fw-semibold">New user registered</div>
                            <div class="text-muted" style="font-size:.75rem;">1 hour ago</div>
                        </div>
                    </div>
                </a>
                <div class="text-center py-2 border-top">
                    <a href="#" class="small text-decoration-none">View all notifications</a>
                </div>
            </div>
        </div>

        {{-- User dropdown --}}
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-1"
                    data-bs-toggle="dropdown">
                @if(auth()->user()->avatar ?? false)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="avatar" class="avatar-sm">
                @else
                    <div class="avatar-sm bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="font-size:.8rem;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                    </div>
                @endif
                <span class="d-none d-md-inline small fw-semibold text-dark">
                    {{ auth()->user()->name ?? 'Admin' }}
                </span>
                <i class="fas fa-chevron-down text-muted" style="font-size:.65rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ auth()->user()->email ?? '' }}</div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.users.edit', auth()->id()) }}">
                        <i class="fas fa-user-edit fa-fw me-2 text-muted"></i>Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cog fa-fw me-2 text-muted"></i>Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- ════════════════════════ MAIN CONTENT ════════════════════════ --}}
<div id="main-wrapper">
    <div id="page-content">

        {{-- Page header --}}
        @hasSection('page-title')
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">@yield('page-title')</h4>
                @hasSection('page-subtitle')
                    <p class="text-muted small mb-0">@yield('page-subtitle')</p>
                @endif
            </div>
            @hasSection('page-actions')
                <div class="d-flex gap-2">@yield('page-actions')</div>
            @endif
        </div>
        @endif

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Toast container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    @if(session('toast'))
        <div class="toast align-items-center text-bg-success border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>{{ session('toast') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
</div>

{{-- ════════════════════════ SCRIPTS ════════════════════════ --}}
{{-- Bootstrap 5.3 --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- Alpine.js --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    // ── Sidebar toggle ──
    const sidebar       = document.getElementById('sidebar');
    const topbar        = document.getElementById('topbar');
    const mainWrapper   = document.getElementById('main-wrapper');
    const toggleBtn     = document.getElementById('sidebarToggle');
    const overlay       = document.getElementById('sidebarOverlay');
    const isMobile      = () => window.innerWidth < 992;

    function toggleSidebar() {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            topbar.classList.toggle('sidebar-collapsed');
            mainWrapper.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Restore state on desktop
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        topbar.classList.add('sidebar-collapsed');
        mainWrapper.classList.add('sidebar-collapsed');
    }

    // ── CSRF setup for fetch/axios ──
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── SweetAlert2 delete confirmations ──
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm-delete]');
        if (!btn) return;
        e.preventDefault();
        const form   = btn.closest('form') || document.getElementById(btn.dataset.form);
        const title  = btn.dataset.confirmTitle  || 'Are you sure?';
        const text   = btn.dataset.confirmText   || 'This action cannot be undone.';
        Swal.fire({
            title, text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!',
        }).then(result => { if (result.isConfirmed) form.submit(); });
    });

    // ── Auto-dismiss alerts ──
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);

    // ── Flash SweetAlert from session ──
    @if(session('swal'))
        @php $swal = session('swal'); @endphp
        Swal.fire({
            icon:  '{{ $swal["icon"] ?? "success" }}',
            title: '{{ $swal["title"] ?? "Done!" }}',
            text:  '{{ $swal["text"] ?? "" }}',
            timer: 3000,
            showConfirmButton: false,
        });
    @endif
</script>

@stack('scripts')
</body>
</html>
