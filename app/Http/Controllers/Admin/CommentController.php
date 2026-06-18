<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('comments.viewAny'), 403);

        $query = Comment::with(['post', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%")
                    ->orWhere('body', 'LIKE', "%{$q}%");
            });
        }

        $comments = $query->paginate(self::PER_PAGE)->withQueryString();

        $pendingCount  = Comment::pending()->count();
        $approvedCount = Comment::approved()->count();

        return view('admin.comments.index', compact('comments', 'pendingCount', 'approvedCount'));
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('comments.viewAny'), 403);

        $ids    = array_filter((array) $request->input('ids', []), 'is_numeric');
        $action = $request->input('action');

        if (empty($ids)) {
            return back()->withErrors(['error' => 'No comments selected.']);
        }

        if ($action === 'approve') {
            abort_if(! auth()->user()->hasPermissionTo('comments.approve'), 403);
            Comment::whereIn('id', $ids)->update(['status' => 'approved']);
            ActivityLogger::log('comment.bulk_approved', 'Bulk approved ' . count($ids) . ' comment(s)', ['ids' => $ids]);
        } elseif ($action === 'reject') {
            abort_if(! auth()->user()->hasPermissionTo('comments.reject'), 403);
            Comment::whereIn('id', $ids)->update(['status' => 'rejected']);
            ActivityLogger::log('comment.bulk_rejected', 'Bulk rejected ' . count($ids) . ' comment(s)', ['ids' => $ids]);
        } elseif ($action === 'delete') {
            abort_if(! auth()->user()->hasPermissionTo('comments.delete'), 403);
            Comment::whereIn('id', $ids)->each(function (Comment $c) { $c->delete(); });
            ActivityLogger::log('comment.bulk_deleted', 'Bulk deleted ' . count($ids) . ' comment(s)', ['ids' => $ids]);
        }

        return back()->with('success', 'Bulk action applied.');
    }

    public function approve(Comment $comment): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('comments.approve'), 403);

        $comment->approve();

        ActivityLogger::log('comment.approved', "Approved comment #{$comment->id} on post #{$comment->post_id}", [], $comment);

        return back()->with('success', 'Comment approved.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('comments.reject'), 403);

        $comment->reject();

        ActivityLogger::log('comment.rejected', "Rejected comment #{$comment->id} on post #{$comment->post_id}", [], $comment);

        return back()->with('success', 'Comment rejected.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('comments.delete'), 403);

        ActivityLogger::log('comment.deleted', "Deleted comment #{$comment->id} on post #{$comment->post_id}", ['id' => $comment->id]);

        $comment->allReplies()->delete();
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
