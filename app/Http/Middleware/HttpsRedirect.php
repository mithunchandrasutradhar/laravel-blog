<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpsRedirect
{
    /**
     * Handle an incoming request.
     *
     * In production environments (APP_ENV=production) any HTTP request is
     * permanently redirected (301) to its HTTPS equivalent. In other
     * environments the middleware is a no-op so local development is
     * unaffected.
     *
     * The middleware also sets the Strict-Transport-Security header on all
     * responses in production so browsers remember to use HTTPS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
