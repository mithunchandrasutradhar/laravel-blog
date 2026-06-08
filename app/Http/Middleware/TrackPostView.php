<?php

namespace App\Http\Middleware;

use App\Models\Post;
use App\Models\PostView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPostView
{
    /**
     * Throttle window in minutes.
     * A second visit from the same IP within this window is not counted.
     */
    private const THROTTLE_MINUTES = 60;

    /**
     * Handle an incoming request.
     *
     * Resolves the Post model from the route parameter and, after the request
     * has been handled and a successful (2xx) response is about to be sent,
     * records a view in post_views and increments the denormalised views_count
     * on the post.
     *
     * Views from bots (common crawler user-agents) are skipped.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests that return a successful HTML response.
        if (
            $request->isMethod('GET')
            && $response->isSuccessful()
            && ! $this->isBot($request->userAgent() ?? '')
        ) {
            // The route parameter is named "slug" on the public post route.
            $slug = $request->route('slug');

            if ($slug) {
                $post = Post::where('slug', $slug)->first();

                if ($post && $post->isPublished()) {
                    PostView::record(
                        $post,
                        $request->ip() ?? '0.0.0.0',
                        $request->userAgent() ?? '',
                        self::THROTTLE_MINUTES
                    );
                }
            }
        }

        return $response;
    }

    /**
     * Determine whether the given user-agent string belongs to a common bot.
     */
    private function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        $bots = [
            'bot', 'crawler', 'spider', 'slurp', 'mediapartners',
            'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduckbot',
            'facebookexternalhit', 'twitterbot', 'linkedinbot', 'whatsapp',
            'applebot', 'semrushbot', 'ahrefsbot', 'mj12bot', 'dotbot',
        ];

        $lower = strtolower($userAgent);

        foreach ($bots as $bot) {
            if (str_contains($lower, $bot)) {
                return true;
            }
        }

        return false;
    }
}
