<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Grant all abilities to admins before running any other checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null; // Continue to the specific policy method.
    }

    /**
     * Determine whether any authenticated user can list posts in the admin panel.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']);
    }

    /**
     * Determine whether the user can view the given post (admin/edit view).
     */
    public function view(User $user, Post $post): bool
    {
        // Authors can view their own posts; admins are handled by before().
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']);
    }

    /**
     * Determine whether the user can update the given post.
     */
    public function update(User $user, Post $post): bool
    {
        // Authors may only update their own posts.
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the given post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can restore a soft-deleted post.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can permanently delete a post.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only the admin role may permanently delete; handled by before().
        return false;
    }

    /**
     * Determine whether the user can publish (or unpublish) a post.
     */
    public function publish(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
