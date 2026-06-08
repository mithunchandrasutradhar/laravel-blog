<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Admin comments per page.
     */
    private const PER_PAGE = 15;

    /**
     * Display a paginated listing of comments with filters.
     */
    public function index(Request $request): View
    {
        $query = Comment::with(['post', 'user'])->latest();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Post filter
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        // Search by commenter name or email
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%")
                    ->orWhere('body', 'LIKE', "%{$q}%");
            });
        }

        $comments = $query->paginate(self::PER_PAGE)->withQueryString();

        $pendingCount = Comment::pending()->count();

        return view('admin.comments.index', compact('comments', 'pendingCount'));
    }

    /**
     * Approve a comment.
     */
    public function approve(Comment $comment): RedirectResponse
    {
        $comment->approve();

        return back()->with('success', 'Comment approved.');
    }

    /**
     * Reject a comment.
     */
    public function reject(Comment $comment): RedirectResponse
    {
        $comment->reject();

        return back()->with('success', 'Comment rejected.');
    }

    /**
     * Delete a comment and all its replies.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        // Recursively delete replies
        $comment->allReplies()->delete();
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
