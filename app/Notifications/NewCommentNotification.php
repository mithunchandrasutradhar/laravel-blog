<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Comment $comment
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
        $post           = $this->comment->post;
        $commenterName  = $this->comment->commenter_name;
        $postTitle      = $post?->title ?? 'your post';
        $postUrl        = $post ? route('posts.show', $post->slug) : url('/');
        $commentBody    = \Illuminate\Support\Str::limit(strip_tags($this->comment->body), 200);

        return (new MailMessage)
            ->subject("New comment on \"{$postTitle}\"")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$commenterName} left a new comment on your post \"{$postTitle}\".")
            ->line("\"{$commentBody}\"")
            ->action('View Comment', $postUrl . '#comment-' . $this->comment->id)
            ->line('You are receiving this notification because you are the author of this post.');
    }

    /**
     * Get the array representation for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'new_comment',
            'comment_id'     => $this->comment->id,
            'post_id'        => $this->comment->post_id,
            'post_title'     => $this->comment->post?->title,
            'commenter_name' => $this->comment->commenter_name,
            'excerpt'        => \Illuminate\Support\Str::limit(strip_tags($this->comment->body), 100),
        ];
    }
}
