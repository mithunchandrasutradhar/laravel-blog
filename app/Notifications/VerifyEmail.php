<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends BaseVerifyEmail
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $siteName = settings('site_name', config('app.name', 'Blog'));
        $url      = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject("Verify Your Email — {$siteName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Welcome to {$siteName}! Please verify your email address by clicking the button below.")
            ->action('Verify Email Address', $url)
            ->line("This link expires in " . config('auth.verification.expire', 60) . " minutes.")
            ->line("If you did not create an account on {$siteName}, no further action is required.")
            ->salutation("Thanks,\n{$siteName} Team");
    }

    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }
}
