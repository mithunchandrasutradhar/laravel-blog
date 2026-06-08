<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly User $user
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
        $siteName   = setting('site_name', config('app.name', 'Blog'));
        $siteUrl    = rtrim(config('app.url', url('/')), '/');
        $dashUrl    = route('dashboard');
        $profileUrl = route('profile.edit');

        return (new MailMessage)
            ->subject("Welcome to {$siteName}!")
            ->greeting("Hello {$this->user->name},")
            ->line("Thank you for joining {$siteName}. We're thrilled to have you.")
            ->line('Your account has been created and you can now log in to explore the platform.')
            ->action('Go to Dashboard', $dashUrl)
            ->line("Here are a few things you can do to get started:")
            ->line("- [Complete your profile]({$profileUrl}) — add a bio and profile picture.")
            ->line("- Browse our latest posts at [{$siteUrl}]({$siteUrl}).")
            ->line("- Leave comments and engage with the community.")
            ->line("If you have any questions, feel free to reach out via our contact page.")
            ->salutation("Welcome aboard,\nThe {$siteName} Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'welcome_email',
            'user_id' => $this->user->id,
            'name'    => $this->user->name,
            'email'   => $this->user->email,
        ];
    }
}
