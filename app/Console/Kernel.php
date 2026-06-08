<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * Commands are resolved from the IoC container, so you may type-hint any
     * dependencies in the command constructors.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ---- Post publishing ------------------------------------------------
        // Check for scheduled posts every minute so that posts go live as
        // close to their scheduled time as possible.
        $schedule->command('posts:publish-scheduled')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/publish-scheduled.log'));

        // ---- Post view cleanup ----------------------------------------------
        // Remove post_views records older than 90 days once per day at midnight
        // to keep the table lean without impacting analytics completeness.
        $schedule->command('post-views:clean --days=90')
            ->dailyAt('00:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/clean-post-views.log'));

        // ---- Sitemap regeneration -------------------------------------------
        // Rebuild the public sitemap nightly so search engines always have an
        // up-to-date index.
        $schedule->call(function () {
            app(\App\Services\SitemapService::class)->generate();
        })
            ->dailyAt('01:00')
            ->name('generate-sitemap')
            ->withoutOverlapping();

        // ---- RSS feed regeneration ------------------------------------------
        $schedule->call(function () {
            app(\App\Services\RssService::class)->generate();
        })
            ->hourly()
            ->name('generate-rss-feed')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
