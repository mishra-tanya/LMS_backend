<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        // Generate the signed verification URL
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->mailer('info') 
            ->subject('Verify Your Email Address')
            ->line('Click the button below to verify your email address.')
            ->action('Verify Email', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}