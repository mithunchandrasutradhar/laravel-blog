<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Observers\CommentObserver;
use App\Observers\PostObserver;
use App\Services\AdvertisementService;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services as singletons so the cache and configuration are only
        // resolved once per request.
        $this->app->singleton(SettingService::class);
        $this->app->singleton(AdvertisementService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination across all views
        Paginator::useBootstrapFive();

        // ---- Eloquent model hardening ----------------------------------------
        Model::preventLazyLoading(! app()->isProduction());

        // ---- Apply mail settings from DB so all emails use admin-configured SMTP ----
        try {
            $s    = app(SettingService::class);
            $host = $s->get('mail_host');
            if ($host) {
                config([
                    'mail.default'                 => $s->get('mail_mailer', 'smtp'),
                    'mail.mailers.smtp.host'       => $host,
                    'mail.mailers.smtp.port'       => (int) $s->get('mail_port', 587),
                    'mail.mailers.smtp.encryption' => $s->get('mail_encryption', 'tls') ?: null,
                    'mail.mailers.smtp.username'   => $s->get('mail_username'),
                    'mail.mailers.smtp.password'   => $s->get('mail_password'),
                    'mail.from.address'            => $s->get('mail_from_address', config('mail.from.address')),
                    'mail.from.name'               => $s->get('mail_from_name', config('mail.from.name')),
                ]);
            }
        } catch (\Throwable) {
            // DB unavailable during migrations — silently fall back to .env defaults
        }

        // ---- Observers -------------------------------------------------------
        Post::observe(PostObserver::class);
        Comment::observe(CommentObserver::class);

        // ---- Global view data ------------------------------------------------
        // Share data that is needed by the main layout with every view.
        // All queries are wrapped in cache calls to avoid repeating the same
        // DB hits on every page load.
        View::composer('*', function (\Illuminate\View\View $view) {
            static $shared = null;

            if ($shared === null) {
                $shared = Cache::remember('view.global_data', config('blog.cache_ttl', 3600), function () {
                    $categories = $this->resolveCategories();
                    return [
                        'globalSettings'      => $this->resolveSettings(),
                        'globalCategories'    => $categories,
                        'navCategories'       => $categories->take(12),
                        'sidebarCategories'   => $categories->sortByDesc('posts_count')->take(10)->values(),
                        'globalRecentPosts'   => $this->resolveRecentPosts(),
                    ];
                });
            }

            $view->with($shared);

            // Ads are position-specific — expose the service for on-demand
            // fetching in individual views rather than loading all positions up
            // front.
            $view->with('adService', app(AdvertisementService::class));
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Return a flat key→value array of all general settings.
     *
     * @return array<string, mixed>
     */
    private function resolveSettings(): array
    {
        try {
            return app(SettingService::class)->getGroup('general');
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Return all visible categories with their post counts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function resolveCategories(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return \App\Models\Category::withCount(['posts' => fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now())])
                ->orderBy('name')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * Return the five most recently published posts for the sidebar.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function resolveRecentPosts(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return Post::published()
                ->with(['category', 'author'])
                ->latestPublished()
                ->limit(5)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
