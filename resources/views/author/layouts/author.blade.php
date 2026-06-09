<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Author Panel') — {{ config('app.name', 'Blog') }}</title>

    {{-- Bootstrap 5.3 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 240px;
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
            font-size: 1.05rem;
            margin-left: .75rem;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        #sidebar.collapsed .brand-name,
        #sidebar.collapsed .nav-label,
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
            transition: background .15s, color .15s;
            white-space: nowrap;
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

        .breadcrumb-item + .breadcrumb-item::before {
            color: #6c757d;
        }

        .toast-container {
            z-index: 9999;
        }

        /* Author role badge in sidebar footer */
        .role-badge {
            display: inline-block;
            background: rgba(13,110,253,.15);
            color: #6ea8fe;
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            border-radius: 20px;
            padding: 2px 8px;
            margin-left: .5rem;
            vertical-align: middle;
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
            <i class="fas fa-pen-nib text-white" style="font-size:.8rem;"></i>
        </div>
        <span class="brand-name">
            {{ config('app.name', 'Blog') }}
            <span class="role-badge">Author</span>
        </span>
    </div>

    {{-- Navigation --}}
    <div class="flex-grow-1 overflow-y-auto py-2" style="scrollbar-width:thin;scrollbar-color:#343a40 transparent;">

        {{-- MAIN --}}
        <div class="nav-section-title">Main</div>

        {{-- Dashboard --}}
        <a href="{{ route('author.dashboard') }}"
           class="nav-item-link {{ request()->routeIs('author.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>

        {{-- CONTENT --}}
        <div class="nav-section-title">Content</div>

        {{-- My Posts --}}
        <a href="{{ route('author.posts.index') }}"
           class="nav-item-link {{ request()->routeIs('author.posts.index') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
            <span class="nav-label">My Posts</span>
        </a>

        {{-- New Post --}}
        <a href="{{ route('author.posts.create') }}"
           class="nav-item-link {{ request()->routeIs('author.posts.create') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-plus-circle"></i></span>
            <span class="nav-label">New Post</span>
        </a>

        {{-- My Media --}}
        <a href="{{ route('author.media.index') }}"
           class="nav-item-link {{ request()->routeIs('author.media.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-images"></i></span>
            <span class="nav-label">My Media</span>
        </a>

    </div>

    {{-- Sidebar footer --}}
    <div class="border-top" style="border-color:rgba(255,255,255,.08)!important;">
        <a href="{{ route('home') }}" target="_blank"
           class="nav-item-link px-5 py-3" style="font-size:.85rem;">
            <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
            <span class="nav-label">View Blog</span>
        </a>

        {{-- User info --}}
        <div class="px-3 py-3 border-top d-flex align-items-center gap-2" style="border-color:rgba(255,255,255,.08)!important;">
            @if(auth()->user()->avatar ?? false)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="avatar" class="avatar-sm flex-shrink-0">
            @else
                <div class="avatar-sm bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                     style="font-size:.75rem;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </div>
            @endif
            <div class="overflow-hidden" style="transition:opacity .2s;" id="sidebarUserInfo">
                <div class="text-white small fw-semibold text-truncate" style="max-width:140px;">
                    {{ auth()->user()->name ?? 'Author' }}
                </div>
                <div class="text-muted" style="font-size:.7rem;">Author</div>
            </div>
        </div>
    </div>
</nav>

{{-- ════════════════════════ TOP BAR ════════════════════════ --}}
<header id="topbar">
    {{-- Toggle sidebar --}}
    <button class="btn btn-link text-secondary p-1" id="sidebarToggle" title="Toggle Sidebar">
        <i class="fas fa-bars fa-lg"></i>
    </button>

    {{-- Page title slot --}}
    <div class="flex-grow-1 d-none d-md-block">
        @hasSection('page-title')
            <span class="fw-semibold text-dark" style="font-size:.95rem;">@yield('page-title')</span>
        @endif
    </div>

    {{-- Right side --}}
    <div class="d-flex align-items-center gap-2 ms-auto">

        {{-- View Blog shortcut --}}
        <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
            <i class="fas fa-globe" style="font-size:.8rem;"></i>
            <span style="font-size:.8rem;">View Blog</span>
        </a>

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
                    {{ auth()->user()->name ?? 'Author' }}
                </span>
                <i class="fas fa-chevron-down text-muted" style="font-size:.65rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ auth()->user()->name ?? 'Author' }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ auth()->user()->email ?? '' }}</div>
                    <div class="mt-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.65rem;">Author</span>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-edit fa-fw me-2 text-muted"></i>My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('author.posts.create') }}">
                        <i class="fas fa-plus fa-fw me-2 text-muted"></i>New Post
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

        {{-- Flash alerts --}}
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
    const sidebar     = document.getElementById('sidebar');
    const topbar      = document.getElementById('topbar');
    const mainWrapper = document.getElementById('main-wrapper');
    const toggleBtn   = document.getElementById('sidebarToggle');
    const overlay     = document.getElementById('sidebarOverlay');
    const isMobile    = () => window.innerWidth < 992;

    function toggleSidebar() {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            const collapsed = sidebar.classList.toggle('collapsed');
            topbar.classList.toggle('sidebar-collapsed', collapsed);
            mainWrapper.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem('authorSidebarCollapsed', collapsed);
        }
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Restore state on desktop
    if (!isMobile() && localStorage.getItem('authorSidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        topbar.classList.add('sidebar-collapsed');
        mainWrapper.classList.add('sidebar-collapsed');
    }

    // ── CSRF token for fetch ──
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── SweetAlert2 delete confirmations (data-confirm-delete) ──
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm-delete]');
        if (!btn) return;
        e.preventDefault();
        const form  = btn.closest('form') || document.getElementById(btn.dataset.form);
        const title = btn.dataset.confirmTitle || 'Are you sure?';
        const text  = btn.dataset.confirmText  || 'This action cannot be undone.';
        Swal.fire({
            title, text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!',
        }).then(result => { if (result.isConfirmed) form.submit(); });
    });

    // ── Auto-dismiss alerts after 5 s ──
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);

    // ── SweetAlert2 toast from session('swal') ──
    @if(session('swal'))
        @php $swal = session('swal'); @endphp
        Swal.fire({
            icon:  '{{ $swal["icon"]  ?? "success" }}',
            title: '{{ $swal["title"] ?? "Done!" }}',
            text:  '{{ $swal["text"]  ?? "" }}',
            toast: true,
            position: 'bottom-end',
            timer: 3500,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    @endif

    // ── SweetAlert2 toast from session('success') ──
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session("success") }}',
            toast: true,
            position: 'bottom-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    @endif
</script>

@stack('scripts')
</body>
</html>
