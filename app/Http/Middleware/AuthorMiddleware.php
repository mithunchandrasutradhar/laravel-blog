<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $user = $request->user();

        // Allow: admin role (always) OR any role with panel.admin OR panel.author permission
        $hasAccess = $user->isAdmin()
            || $user->hasPermissionTo('panel.admin')
            || $user->hasPermissionTo('panel.author');

        if (! $hasAccess) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden. Author access required.'], 403)
                : abort(403, 'You do not have permission to access the author panel.');
        }

        if (! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}
