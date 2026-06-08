<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    // -------------------------------------------------------------------------
    // Created
    // -------------------------------------------------------------------------

    /**
     * Handle the Post "created" event.
     *
     * - Ensures a unique URL slug is set (the model's boot hook handles the
     *   primary generation, but the observer acts as a safety net and enforces
     *   uniqueness after the record has been persisted).
     * - Calculates and persists the estimated reading time.
     */
    public function created(Post $post): void
    {
        $dirty = [];

        // Re-generate slug after creation to guarantee uniqueness now that the
        // row has a real ID (guards against race conditions in the boot hook).
        if (empty($post->slug)) {
            $dirty['slug'] = Post::generateUniqueSlug($post->title, $post->id);
        }

        // Ensure reading_time is calculated.
        if (empty($post->reading_time) && ! empty($post->content)) {
            $dirty['reading_time'] = Post::estimateReadingTime($post->content);
        }

        if (! empty($dirty)) {
            // Use updateQuietly to avoid triggering the observer recursively.
            $post->updateQuietly($dirty);
        }
    }

    // -------------------------------------------------------------------------
    // Updated
    // -------------------------------------------------------------------------

    /**
     * Handle the Post "updated" event.
     *
     * - Re-generates the slug when the title has changed and no explicit slug
     *   was supplied in the same request.
     * - Recalculates reading_time whenever the content body changes.
     */
    public function updated(Post $post): void
    {
        $dirty = [];

        if ($post->wasChanged('title') && ! $post->wasChanged('slug')) {
            $dirty['slug'] = Post::generateUniqueSlug($post->title, $post->id);
        }

        if ($post->wasChanged('content')) {
            $dirty['reading_time'] = Post::estimateReadingTime($post->content ?? '');
        }

        if (! empty($dirty)) {
            $post->updateQuietly($dirty);
        }
    }

    // -------------------------------------------------------------------------
    // Deleted (soft)
    // -------------------------------------------------------------------------

    /**
     * Handle the Post "deleted" event (soft delete).
     *
     * Detaches all tag pivot records.
     * The featured image is intentionally retained on soft delete so that the
     * post can be restored later.
     */
    public function deleted(Post $post): void
    {
        $post->tags()->detach();
    }

    // -------------------------------------------------------------------------
    // Force-deleted (permanent)
    // -------------------------------------------------------------------------

    /**
     * Handle the Post "forceDeleted" event (permanent deletion).
     *
     * Removes the featured image from storage and deletes all related media
     * and comment records.
     */
    public function forceDeleted(Post $post): void
    {
        // Remove the featured image from storage.
        if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
            Storage::disk('public')->delete($post->featured_image);
        }

        // Remove associated media records and their files.
        $post->media()->each(function ($media) {
            try {
                Storage::disk($media->disk)->delete($media->file_name);
                $media->delete();
            } catch (\Throwable $e) {
                Log::warning("PostObserver: failed to delete media [{$media->id}].", [
                    'error' => $e->getMessage(),
                ]);
            }
        });

        // Remove all comments for this post.
        $post->comments()->forceDelete();

        // Remove all view records.
        $post->postViews()->delete();

        // Detach tags pivot (in case soft-delete handler was skipped).
        $post->tags()->detach();
    }

    // -------------------------------------------------------------------------
    // Restored
    // -------------------------------------------------------------------------

    /**
     * Handle the Post "restored" event.
     * No additional action is required; slug and reading_time are still intact.
     */
    public function restored(Post $post): void
    {
        //
    }
}
