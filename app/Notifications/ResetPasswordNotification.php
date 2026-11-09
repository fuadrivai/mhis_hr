<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $data = [
            'name' => $notifiable->name,
            'resetUrl' => $this->resetUrl($notifiable),
            'expiresIn' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
        ];

        return (new MailMessage)
            ->subject('Reset Password - MHIS HUB')
            ->view('email-template.password-reset', $data)
            ->text('email-template.password-reset-text', $data);
    }
}
