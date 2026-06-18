<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled
                            {--dry-run : List posts that would be published without actually publishing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all posts whose scheduled publish date has passed.';

    // -------------------------------------------------------------------------
    // Handle
    // -------------------------------------------------------------------------

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $posts = Post::where('status', 'scheduled')
            ->where('published_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts are due for publishing.');
            return Command::SUCCESS;
        }

        $this->info("Found {$posts->count()} post(s) ready to publish.");

        if ($isDryRun) {
            $this->table(
                ['ID', 'Title', 'Scheduled At'],
                $posts->map(fn (Post $p) => [$p->id, $p->title, $p->published_at->toDateTimeString()])
            );
            $this->line('<comment>[dry-run] No posts were actually published.</comment>');
            return Command::SUCCESS;
        }

        $published = 0;
        $failed    = 0;

        foreach ($posts as $post) {
            try {
                $post->updateQuietly(['status' => 'published']);
                $published++;

                Log::info("PublishScheduledPosts: published post [{$post->id}] \"{$post->title}\".");
            } catch (\Throwable $e) {
                $failed++;
                Log::error("PublishScheduledPosts: failed to publish post [{$post->id}].", [
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to publish post [{$post->id}]: {$e->getMessage()}");
            }
        }

        // Flush relevant caches so the front-end picks up the new posts.
        Cache::forget('home.page_data');
        Cache::forget('view.global_data');

        $this->info("Published: {$published} | Failed: {$failed}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
