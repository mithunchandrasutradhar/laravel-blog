<?php

namespace App\Services;

use App\Models\Subscriber;
use App\Notifications\NewsletterVerification;
use Illuminate\Support\Str;

class NewsletterService
{
    // -------------------------------------------------------------------------
    // Subscription lifecycle
    // -------------------------------------------------------------------------

    /**
     * Register a new subscriber (or re-send the verification email to an
     * existing unverified subscriber) and dispatch a confirmation email.
     *
     * Returns the Subscriber model and a boolean indicating whether the record
     * was freshly created (`true`) or already existed (`false`).
     *
     * @return array{subscriber: Subscriber, created: bool}
     */
    public function subscribe(string $email, ?string $name = null): array
    {
        $existing = Subscriber::where('email', $email)->first();

        if ($existing) {
            // Already subscribed and verified — nothing more to do.
            if ($existing->isVerified()) {
                return ['subscriber' => $existing, 'created' => false];
            }

            // Unverified — resend the confirmation.
            $this->sendConfirmationEmail($existing);

            return ['subscriber' => $existing, 'created' => false];
        }

        $subscriber = Subscriber::create([
            'email' => $email,
            'name'  => $name,
        ]);

        $this->sendConfirmationEmail($subscriber);

        return ['subscriber' => $subscriber, 'created' => true];
    }

    /**
     * Verify a subscriber using their unique token.
     *
     * Returns the verified Subscriber or null when the token is invalid.
     */
    public function verify(string $token): ?Subscriber
    {
        $subscriber = Subscriber::where('token', $token)->first();

        if (! $subscriber) {
            return null;
        }

        if (! $subscriber->isVerified()) {
            $subscriber->verify();
        }

        return $subscriber;
    }

    /**
     * Unsubscribe (delete) a subscriber by their unique token.
     *
     * Returns true on success, false when the token is not found.
     */
    public function unsubscribe(string $token): bool
    {
        $subscriber = Subscriber::where('token', $token)->first();

        if (! $subscriber) {
            return false;
        }

        return (bool) $subscriber->delete();
    }

    // -------------------------------------------------------------------------
    // Email
    // -------------------------------------------------------------------------

    /**
     * Send (or re-send) a verification / confirmation email to the subscriber.
     */
    public function sendConfirmationEmail(Subscriber $subscriber): void
    {
        // Ensure the subscriber always has a fresh token before sending.
        if (empty($subscriber->token)) {
            $subscriber->regenerateToken();
        }

        $subscriber->notify(new NewsletterVerification($subscriber));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the total count of verified subscribers.
     */
    public function verifiedCount(): int
    {
        return Subscriber::verified()->count();
    }

    /**
     * Return the total count of unverified (pending) subscribers.
     */
    public function pendingCount(): int
    {
        return Subscriber::unverified()->count();
    }
}
