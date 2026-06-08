<?php

namespace App\Observers;

use App\Models\Comment;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Log;

class CommentObserver
{
    // -------------------------------------------------------------------------
    // Created
    // -------------------------------------------------------------------------

    /**
     * Handle the Comment "created" event.
     *
     * Notifies the post author that a new comment has been submitted.
     * The notification is only dispatched when:
     *   - The post has a linked author account.
     *   - The comment was not written by the author themselves (avoids
     *     self-notification spam).
     */
    public function created(Comment $comment): void
    {
        $post = $comment->post()->with('author')->first();

        if (! $post || ! $post->author) {
            return;
        }

        // Do not notify the author about their own comments.
        if ($comment->user_id && $comment->user_id === $post->author->id) {
            return;
        }

        try {
            $post->author->notify(new NewCommentNotification($comment));
        } catch (\Throwable $e) {
            Log::error('CommentObserver: failed to send NewCommentNotification.', [
                'comment_id' => $comment->id,
                'post_id'    => $post->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Updated
    // -------------------------------------------------------------------------

    /**
     * Handle the Comment "updated" event.
     * Currently unused — reserved for future functionality (e.g. moderation
     * status change notifications).
     */
    public function updated(Comment $comment): void
    {
        //
    }

    // -------------------------------------------------------------------------
    // Deleted
    // -------------------------------------------------------------------------

    /**
     * Handle the Comment "deleted" event.
     * Cascade-deletes any child replies to maintain data integrity, since the
     * comments table does not enforce this at the database level via a foreign
     * key with CASCADE.
     */
    public function deleted(Comment $comment): void
    {
        $comment->allReplies()->delete();
    }

    // -------------------------------------------------------------------------
    // Restored
    // -------------------------------------------------------------------------

    /**
     * Handle the Comment "restored" event.
     * Currently unused.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    // -------------------------------------------------------------------------
    // Force-deleted
    // -------------------------------------------------------------------------

    /**
     * Handle the Comment "forceDeleted" event.
     * Permanently removes all child replies.
     */
    public function forceDeleted(Comment $comment): void
    {
        $comment->allReplies()->forceDelete();
    }
}
