<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $authorId = auth()->id();

        $query = Comment::with(['post', 'user'])
            ->whereHas('post', fn ($q) => $q->where('user_id', $authorId))
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('body', 'LIKE', "%{$term}%");
            });
        }

        $comments = $query->paginate(self::PER_PAGE)->withQueryString();

        $pendingCount  = Comment::whereHas('post', fn ($q) => $q->where('user_id', $authorId))->pending()->count();
        $approvedCount = Comment::whereHas('post', fn ($q) => $q->where('user_id', $authorId))->approved()->count();

        return view('author.comments.index', compact('comments', 'pendingCount', 'approvedCount'));
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->authorizeOwnership($comment);
        $comment->approve();

        return back()->with('success', 'Comment approved.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $this->authorizeOwnership($comment);
        $comment->reject();

        return back()->with('success', 'Comment rejected.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorizeOwnership($comment);
        $comment->allReplies()->delete();
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    private function authorizeOwnership(Comment $comment): void
    {
        abort_unless(
            $comment->post && $comment->post->user_id === auth()->id(),
            403,
            'You may only manage comments on your own posts.'
        );
    }
}
