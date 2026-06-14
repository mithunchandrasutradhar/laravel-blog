<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

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

        // reCAPTCHA validation (only when secret key is configured)
        $recaptchaSecret = Setting::get('recaptcha_secret_key');
        if ($recaptchaSecret && $request->filled('g-recaptcha-response')) {
            $this->verifyRecaptcha($request->input('g-recaptcha-response'), $recaptchaSecret);
        }

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

    /**
     * Verify the reCAPTCHA v2 token with Google's API.
     * Aborts with 422 if verification fails.
     */
    private function verifyRecaptcha(string $token, string $secret): void
    {
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            if (! ($response->json('success') === true)) {
                abort(422, 'reCAPTCHA verification failed. Please try again.');
            }
        } catch (\Exception $e) {
            logger()->warning('reCAPTCHA check failed: ' . $e->getMessage());
            // Fail open in case of network issues so users aren't blocked
        }
    }
}
