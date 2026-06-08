<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title ?? 'Authentication' }} &mdash; {{ settings('site_name', config('app.name')) }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Favicon --}}
    @if(settings('favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset(settings('favicon')) }}">
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body class="auth-body bg-light min-vh-100 d-flex flex-column">

    {{-- Header Bar --}}
    <header class="auth-header py-3 bg-white border-bottom">
        <div class="container text-center">
            <a href="{{ route('home') }}" class="text-decoration-none">
                @if(settings('logo'))
                    <img src="{{ asset(settings('logo')) }}" alt="{{ settings('site_name', config('app.name')) }}" height="36">
                @else
                    <span class="fw-bold fs-5 text-primary">{{ settings('site_name', config('app.name')) }}</span>
                @endif
            </a>
        </div>
    </header>

    {{-- Main Auth Content --}}
    <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">

                    {{-- Flash Messages --}}
                    @include('partials.flash-messages')

                    {{-- Auth Card --}}
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-4 p-md-5">
                            @yield('content')
                        </div>
                    </div>

                    {{-- Footer Links --}}
                    <div class="text-center mt-4">
                        @yield('auth-footer')
                    </div>

                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-3 text-center">
        <small class="text-muted">
            &copy; {{ date('Y') }} {{ settings('site_name', config('app.name')) }}.
            <a href="{{ route('home') }}" class="text-muted">Back to Home</a>
        </small>
    </footer>

    {{-- Bootstrap 5.3 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmErciUSRKxVKXFcO1HiN7HHLoX5" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
