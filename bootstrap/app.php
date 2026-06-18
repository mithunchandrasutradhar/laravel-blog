<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthorMiddleware;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\HttpsRedirect;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\TrackPostView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // -----------------------------------------------------------------------
        // Global middleware (runs on every request)
        // -----------------------------------------------------------------------
        $middleware->append(HttpsRedirect::class);
        $middleware->append(SecureHeaders::class);

        // -----------------------------------------------------------------------
        // Web middleware group additions
        // -----------------------------------------------------------------------
        // (session, CSRF, etc. are already included by default in the web group)
        $middleware->appendToGroup('web', CheckUserStatus::class);

        // -----------------------------------------------------------------------
        // Named / route-level middleware aliases
        // -----------------------------------------------------------------------
        $middleware->alias([
            'admin'           => AdminMiddleware::class,
            'author'          => AuthorMiddleware::class,
            'track.post.view' => TrackPostView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
