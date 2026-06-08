<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Grants access to authenticated users who hold the "author" OR "admin"
     * role. Admins are always allowed so they can manage author-level content.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $user = $request->user();

        if (! $user->isAuthor() && ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Author access required.'], 403);
            }

            abort(403, 'You do not have permission to access the author panel.');
        }

        if (! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
