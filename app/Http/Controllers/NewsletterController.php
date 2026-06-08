<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscribeRequest;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    /**
     * Subscribe a new email address to the newsletter.
     *
     * If the email already exists and is verified, returns an appropriate
     * message. If it exists but is unverified, resends the verification email.
     * Otherwise creates a new subscriber and dispatches a verification email.
     */
    public function subscribe(NewsletterSubscribeRequest $request): RedirectResponse|JsonResponse
    {
        $subscriber = Subscriber::where('email', $request->email)->first();

        if ($subscriber && $subscriber->isVerified()) {
            $msg = 'You are already subscribed to our newsletter.';
        } else {
            if (! $subscriber) {
                $subscriber = Subscriber::create([
                    'name'  => $request->input('name'),
                    'email' => $request->email,
                ]);
            }

            $this->sendVerificationEmail($subscriber);
            $msg = 'Almost there! Please check your inbox to confirm your subscription.';
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $msg]);
        }

        return back()->with('newsletter_message', $msg);
    }

    /**
     * Verify a subscriber's email address via the token link sent in the email.
     */
    public function verify(string $token): RedirectResponse
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();

        if (! $subscriber->isVerified()) {
            $subscriber->verify();
        }

        return redirect()->route('home')
            ->with('success', 'Thank you! Your newsletter subscription has been confirmed.');
    }

    /**
     * Unsubscribe a subscriber via their unique token.
     */
    public function unsubscribe(string $token): RedirectResponse
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();
        $subscriber->delete();

        return redirect()->route('home')
            ->with('success', 'You have been successfully unsubscribed from our newsletter.');
    }

    /**
     * Send the email verification / confirmation email to a subscriber.
     */
    private function sendVerificationEmail(Subscriber $subscriber): void
    {
        try {
            Mail::send('emails.newsletter-verify', ['subscriber' => $subscriber], function ($mail) use ($subscriber) {
                $mail->to($subscriber->email, $subscriber->name ?? '')
                     ->subject('Confirm your newsletter subscription');
            });
        } catch (\Exception $e) {
            logger()->error('Newsletter verification email failed: ' . $e->getMessage());
        }
    }
}
