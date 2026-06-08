<?php

namespace App\Notifications;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewsletterVerification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Subscriber $subscriber
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteName     = setting('site_name', config('app.name', 'Blog'));
        $verifyUrl    = route('newsletter.verify', ['token' => $this->subscriber->token]);
        $unsubUrl     = route('newsletter.unsubscribe', ['token' => $this->subscriber->token]);
        $greeting     = $this->subscriber->name
            ? "Hello {$this->subscriber->name},"
            : 'Hello,';

        return (new MailMessage)
            ->subject("Confirm your subscription to {$siteName}")
            ->greeting($greeting)
            ->line("Thank you for subscribing to the {$siteName} newsletter.")
            ->line('Please click the button below to confirm your email address and activate your subscription.')
            ->action('Confirm Subscription', $verifyUrl)
            ->line("If you did not subscribe, you can safely ignore this email or [unsubscribe]({$unsubUrl}).")
            ->salutation("Best regards,\nThe {$siteName} Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'newsletter_verification',
            'email' => $this->subscriber->email,
        ];
    }
}
