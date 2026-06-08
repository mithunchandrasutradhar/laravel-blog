<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscribeRequest;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email address via the API (AJAX footer widget).
     */
    public function subscribe(NewsletterSubscribeRequest $request): JsonResponse
    {
        $subscriber = Subscriber::where('email', $request->email)->first();

        if ($subscriber && $subscriber->isVerified()) {
            return response()->json([
                'message' => 'You are already subscribed to our newsletter.',
                'status'  => 'already_subscribed',
            ]);
        }

        if (! $subscriber) {
            $subscriber = Subscriber::create([
                'name'  => $request->input('name'),
                'email' => $request->email,
            ]);
        }

        // Send verification email
        try {
            Mail::send('emails.newsletter-verify', ['subscriber' => $subscriber], function ($mail) use ($subscriber) {
                $mail->to($subscriber->email, $subscriber->name ?? '')
                     ->subject('Confirm your newsletter subscription');
            });
        } catch (\Exception $e) {
            logger()->error('API newsletter verification email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Almost there! Please check your inbox to confirm your subscription.',
            'status'  => 'pending_verification',
        ]);
    }
}
