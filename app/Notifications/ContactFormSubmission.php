<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormSubmission extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly ContactMessage $contactMessage
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteName    = setting('site_name', config('app.name', 'Blog'));
        $adminUrl    = route('admin.contact-messages.index');
        $bodyExcerpt = \Illuminate\Support\Str::limit($this->contactMessage->message, 300);

        return (new MailMessage)
            ->subject("[{$siteName}] New contact form message: {$this->contactMessage->subject}")
            ->greeting('Hello Admin,')
            ->line('A new message has been submitted via the contact form.')
            ->line("**From:** {$this->contactMessage->name} ({$this->contactMessage->email})")
            ->line("**Subject:** {$this->contactMessage->subject}")
            ->line("**Message:**")
            ->line($bodyExcerpt)
            ->action('View Messages', $adminUrl)
            ->line("Please log in to the admin panel to read and respond to this message.")
            ->salutation("The {$siteName} notification system");
    }

    /**
     * Get the array representation for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'contact_form_submission',
            'message_id' => $this->contactMessage->id,
            'from_name'  => $this->contactMessage->name,
            'from_email' => $this->contactMessage->email,
            'subject'    => $this->contactMessage->subject,
            'excerpt'    => \Illuminate\Support\Str::limit($this->contactMessage->message, 100),
        ];
    }
}
