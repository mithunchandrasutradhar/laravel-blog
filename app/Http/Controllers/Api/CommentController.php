<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class CommentController extends Controller
{
    /**
     * Store a new comment via the API (AJAX form submission).
     */
    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        // Ensure the post is published (or the author/admin is previewing)
        if (! $post->isPublished()) {
            abort_unless(
                auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $post->user_id),
                404
            );
        }

        // reCAPTCHA check for guests
        $recaptchaSecret = Setting::get('recaptcha_secret_key');

        if ($recaptchaSecret && ! auth()->check() && $request->filled('g-recaptcha-response')) {
            try {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => $recaptchaSecret,
                    'response' => $request->input('g-recaptcha-response'),
                    'remoteip' => $request->ip(),
                ]);

                if (! ($response->json('success') === true)) {
                    return response()->json(['message' => 'reCAPTCHA verification failed.'], 422);
                }
            } catch (\Exception $e) {
                logger()->warning('API reCAPTCHA check failed: ' . $e->getMessage());
            }
        }

        $status = auth()->check() ? 'approved' : 'pending';

        $comment = Comment::create([
            'post_id'    => $post->id,
            'user_id'    => auth()->id(),
            'parent_id'  => $request->parent_id,
            'name'       => auth()->check() ? auth()->user()->name : $request->name,
            'email'      => auth()->check() ? auth()->user()->email : $request->email,
            'body'       => $request->body,
            'status'     => $status,
            'ip_address' => $request->ip(),
        ]);

        $message = $status === 'approved'
            ? 'Comment posted successfully.'
            : 'Your comment is awaiting moderation.';

        return response()->json([
            'message' => $message,
            'status'  => $status,
            'comment' => $status === 'approved' ? [
                'id'         => $comment->id,
                'name'       => $comment->commenter_name,
                'body'       => $comment->body,
                'created_at' => $comment->created_at->diffForHumans(),
            ] : null,
        ], 201);
    }
}
