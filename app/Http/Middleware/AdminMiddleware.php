<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Grants access only to authenticated users who hold the "admin" role
     * (via spatie/laravel-permission). Non-admins are either redirected to
     * the home page (browser requests) or receive a 403 JSON response (AJAX).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if (! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
            }

            abort(403, 'You do not have permission to access the admin panel.');
        }

        if (! $request->user()->isActive()) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
