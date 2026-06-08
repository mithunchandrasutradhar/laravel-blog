<?php

namespace App\Console\Commands;

use App\Models\PostView;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanOldPostViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post-views:clean
                            {--days=90 : Delete post_views records older than this many days}
                            {--dry-run : Report the number of records that would be deleted without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove post_views records older than the specified number of days (default: 90).';

    // -------------------------------------------------------------------------
    // Handle
    // -------------------------------------------------------------------------

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days     = (int) $this->option('days');
        $isDryRun = (bool) $this->option('dry-run');
        $cutoff   = now()->subDays($days);

        $query = PostView::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info("No post_views records older than {$days} days found.");
            return Command::SUCCESS;
        }

        $this->info("Found {$count} post_views record(s) older than {$days} days (before {$cutoff->toDateTimeString()}).");

        if ($isDryRun) {
            $this->line('<comment>[dry-run] No records were deleted.</comment>');
            return Command::SUCCESS;
        }

        try {
            // Delete in chunks to avoid long-running DELETE queries on large tables.
            $deleted = 0;
            do {
                $chunk    = PostView::where('created_at', '<', $cutoff)->limit(1000)->delete();
                $deleted += $chunk;
            } while ($chunk > 0);

            $this->info("Successfully deleted {$deleted} record(s).");

            Log::info("CleanOldPostViews: deleted {$deleted} post_views records older than {$days} days.");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to clean post_views: {$e->getMessage()}");

            Log::error('CleanOldPostViews: unexpected error.', [
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }
}
