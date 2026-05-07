<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Accept the plain token from the controller.
     */
    public function __construct(protected string $token)
    {
    }

    /**
     * Send this notification via email only.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the reset password email.
     * $notifiable is the User model instance.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Build the reset link using frontend URL from config,
        // plain token, and user's email from the notifiable
        $resetLink = config('app.frontend_url')
            . '/reset-password?token='
            . $this->token
            . '&email='
            . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetLink)
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Not used - only needed for database notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}