<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Admin' }} &mdash; {{ settings('site_name', config('app.name')) }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">

    {{-- Favicon --}}
    @if(settings('favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset(settings('favicon')) }}">
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- Custom Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body class="admin-body">

    {{-- Sidebar --}}
    <nav id="adminSidebar" class="admin-sidebar bg-dark text-light d-flex flex-column">
        {{-- Sidebar Brand --}}
        <div class="sidebar-brand d-flex align-items-center px-3 py-3 border-bottom border-secondary">
            @if(settings('logo_white') || settings('logo'))
                <img src="{{ asset(settings('logo_white') ?? settings('logo')) }}" alt="{{ settings('site_name', config('app.name')) }}" height="32" class="me-2">
            @else
                <span class="fw-bold text-white fs-6">{{ settings('site_name', config('app.name')) }}</span>
            @endif
            <button class="btn btn-link text-secondary ms-auto p-0 d-lg-none" id="sidebarClose" aria-label="Close sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Sidebar Navigation --}}
        <div class="sidebar-nav flex-grow-1 overflow-auto py-2">
            <ul class="nav flex-column px-2">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt sidebar-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <small class="sidebar-section-title px-3 text-uppercase text-secondary">Content</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.posts.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt sidebar-icon"></i>
                        <span>Posts</span>
                        @php $draftCount = \App\Models\Post::draft()->count(); @endphp
                        @if($draftCount)
                        <span class="badge bg-warning text-dark ms-auto">{{ $draftCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-folder sidebar-icon"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.tags.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                        <i class="fas fa-tags sidebar-icon"></i>
                        <span>Tags</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.comments.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
                        <i class="fas fa-comments sidebar-icon"></i>
                        <span>Comments</span>
                        @php $pendingComments = \App\Models\Comment::pending()->count(); @endphp
                        @if($pendingComments)
                        <span class="badge bg-danger ms-auto">{{ $pendingComments }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.media.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-video sidebar-icon"></i>
                        <span>Media</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.pages.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                        <i class="fas fa-file sidebar-icon"></i>
                        <span>Pages</span>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <small class="sidebar-section-title px-3 text-uppercase text-secondary">Users</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users sidebar-icon"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.subscribers.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
                        <i class="fas fa-envelope sidebar-icon"></i>
                        <span>Subscribers</span>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <small class="sidebar-section-title px-3 text-uppercase text-secondary">Monetization</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.advertisements.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.advertisements.*') ? 'active' : '' }}">
                        <i class="fas fa-ad sidebar-icon"></i>
                        <span>Advertisements</span>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <small class="sidebar-section-title px-3 text-uppercase text-secondary">Analytics</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.analytics.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar sidebar-icon"></i>
                        <span>Analytics</span>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <small class="sidebar-section-title px-3 text-uppercase text-secondary">System</small>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog sidebar-icon"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Sidebar Footer --}}
        <div class="sidebar-footer border-top border-secondary p-3">
            <div class="d-flex align-items-center gap-2">
                @if(auth()->user()->avatar)
                    <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:13px;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white small fw-medium text-truncate">{{ auth()->user()->name }}</div>
                    <div class="text-secondary" style="font-size:.7rem;">{{ auth()->user()->getRoleNames()->first() ?? 'Admin' }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-secondary p-0" title="Logout" aria-label="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Sidebar Overlay (mobile) --}}
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

    {{-- Main Content Wrapper --}}
    <div class="admin-main d-flex flex-column min-vh-100">

        {{-- Topbar --}}
        <header class="admin-topbar bg-white border-bottom px-3 px-lg-4 py-2 d-flex align-items-center gap-3">
            {{-- Hamburger --}}
            <button class="btn btn-link text-dark p-1 d-lg-none" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars fs-5"></i>
            </button>

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="flex-grow-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>

            {{-- Topbar Right --}}
            <div class="d-flex align-items-center gap-3">
                {{-- View Site --}}
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex align-items-center gap-1" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Site</span>
                </a>

                {{-- Notifications --}}
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="fas fa-bell fs-5"></i>
                        @php $unreadNotifications = auth()->user()->unreadNotifications->count(); @endphp
                        @if($unreadNotifications)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
                            {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                        </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:320px;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Notifications</span>
                            @if($unreadNotifications)
                            <a href="{{ route('admin.notifications.mark-all-read') }}" class="small text-primary text-decoration-none">Mark all read</a>
                            @endif
                        </div>
                        <div style="max-height:300px;overflow-y:auto;">
                            @forelse(auth()->user()->notifications->take(10) as $notification)
                            <a href="{{ $notification->data['url'] ?? '#' }}" class="dropdown-item py-2 {{ $notification->read_at ? '' : 'bg-light' }}">
                                <div class="d-flex gap-2 align-items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <i class="fas fa-{{ $notification->data['icon'] ?? 'info-circle' }} text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-medium">{{ $notification->data['message'] ?? 'Notification' }}</div>
                                        <div class="text-muted" style="font-size:.7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                            @empty
                            <div class="dropdown-item text-center text-muted py-3">
                                <i class="fas fa-bell-slash d-block mb-1"></i>
                                No notifications
                            </div>
                            @endforelse
                        </div>
                        <div class="dropdown-footer text-center border-top p-2">
                            <a href="{{ route('admin.notifications.index') }}" class="small text-decoration-none">View all notifications</a>
                        </div>
                    </div>
                </div>

                {{-- User Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="d-none d-md-inline small fw-medium">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2 text-muted"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="fas fa-cog me-2 text-muted"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="admin-content flex-grow-1 p-3 p-lg-4">
            {{-- Flash Messages --}}
            @include('partials.flash-messages')

            {{-- Page Header --}}
            @if(isset($pageTitle))
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-0">{{ $pageTitle }}</h1>
                    @if(isset($pageSubtitle))
                    <p class="text-muted small mb-0 mt-1">{{ $pageSubtitle }}</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @yield('page-actions')
                </div>
            </div>
            @endif

            @yield('content')
        </main>

        {{-- Admin Footer --}}
        <footer class="admin-footer border-top py-2 px-4 text-center">
            <small class="text-muted">
                &copy; {{ date('Y') }} {{ settings('site_name', config('app.name')) }} Admin Panel
                &mdash; v{{ config('app.version', '1.0.0') }}
            </small>
        </footer>
    </div>

    {{-- Bootstrap 5.3 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmErciUSRKxVKXFcO1HiN7HHLoX5" crossorigin="anonymous"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    {{-- CKEditor 5 --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js" crossorigin></script>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" crossorigin>

    {{-- Custom Admin JS --}}
    <script src="{{ asset('js/admin.js') }}"></script>

    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            overlay.classList.add('active');
        }
        function closeSidebar() {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
        }
        sidebarToggle?.addEventListener('click', openSidebar);
        sidebarClose?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // SweetAlert for flash messages
        @if(session('swal_success'))
            Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('swal_success') }}', timer: 3000, showConfirmButton: false });
        @endif
        @if(session('swal_error'))
            Swal.fire({ icon: 'error', title: 'Error', text: '{{ session('swal_error') }}' });
        @endif

        // Confirm delete dialogs
        document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.querySelector(this.dataset.confirmDelete) || this.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: this.dataset.confirmText || 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!',
                }).then(result => { if (result.isConfirmed) form?.submit(); });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
