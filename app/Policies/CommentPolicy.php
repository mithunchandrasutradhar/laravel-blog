<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Grant all abilities to admins unconditionally.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * Any authenticated user (or guest) can list public approved comments —
     * this policy governs admin-panel access.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']);
    }

    /**
     * Determine whether the user can view this specific comment in the admin panel.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Authors can see comments on their own posts.
        return $user->hasRole('admin')
            || ($comment->post?->user_id === $user->id);
    }

    /**
     * Authenticated users can create comments (guest comments are handled
     * at the controller level and don't require a policy check).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the given comment.
     * Users may only edit their own comments; authors may moderate comments on
     * their posts.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id
            || $comment->post?->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the given comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id
            || $comment->post?->user_id === $user->id;
    }

    /**
     * Determine whether the user can approve or reject the given comment.
     * Post authors and admins may moderate comments.
     */
    public function moderate(User $user, Comment $comment): bool
    {
        return $comment->post?->user_id === $user->id;
    }
}
