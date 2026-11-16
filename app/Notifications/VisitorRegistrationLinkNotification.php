<?php

namespace App\Notifications;

use App\Models\VisitorInvitationBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitorRegistrationLinkNotification extends Notification
{
    use Queueable;

    public function __construct(
        private VisitorInvitationBatch $batch,
        private string $registrationUrl
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $details = [
            'Invitation' => $this->batch->name,
            'Visit date' => $this->batch->visit_date->format('d M Y'),
            'Visit time' => $this->batch->expected_arrival_at->format('H:i')
                . ' - ' . $this->batch->expected_departure_at->format('H:i'),
            'Meeting location' => $this->batch->branch->name,
            'Host' => $this->batch->host()->with('user')->first()?->user?->name ?? 'School contact',
            'Purpose' => $this->batch->purpose->name,
        ];

        return (new MailMessage)
            ->subject('Visitor Invitation: ' . $this->batch->name)
            ->view([
                'html' => 'email-template.visitor-notification',
                'text' => 'email-template.visitor-notification-text',
            ], [
                'title' => 'Complete Your Visitor Registration',
                'recipientName' => 'Guest',
                'intro' => 'You are invited to visit Mutiara Harapan Islamic School. Please complete your visitor information using the secure link below.',
                'details' => $details,
                'qrPng' => null,
                'actionLabel' => 'Complete Visitor Information',
                'actionUrl' => $this->registrationUrl,
                'actionNote' => 'This link can only be used once and expires on '
                    . $this->batch->expires_at->format('d M Y H:i') . '.',
            ]);
    }
}
