<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Unified panel middleware.
     *
     * Grants access to any authenticated user who holds either:
     *   - panel.admin  → full admin-panel access
     *   - panel.author → content-creator access (own posts, media, comments)
     *
     * What each user sees inside the panel is controlled by their other
     * permissions (posts.viewAny, categories.viewAny, etc.). The admin role
     * always passes regardless of explicit permission assignment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $user = $request->user();

        $hasPanel = $user->isAdmin()
            || $user->hasPermissionTo('panel.admin')
            || $user->hasPermissionTo('panel.author');

        if (! $hasPanel) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden. Panel access required.'], 403)
                : abort(403, 'You do not have permission to access this panel.');
        }

        if (! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
