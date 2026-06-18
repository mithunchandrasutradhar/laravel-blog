<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Store a new comment on a post.
     *
     * - Validates reCAPTCHA if the site key is configured.
     * - Auto-approves comments from authenticated users; guests go to "pending".
     */
    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $post = Post::where('id', $request->post_id)->published()->firstOrFail();

        Comment::create([
            'post_id'    => $post->id,
            'user_id'    => auth()->id(),
            'parent_id'  => $request->parent_id,
            'name'       => auth()->check() ? auth()->user()->name : $request->name,
            'email'      => auth()->check() ? auth()->user()->email : $request->email,
            'body'       => $request->body,
            'status'     => 'pending',
            'ip_address' => $request->ip(),
        ]);

        $message = 'Your comment is awaiting moderation.';

        return redirect()->route('blog.show', $post->slug)
            ->with('success', $message)
            ->withFragment('comments');
    }

}
