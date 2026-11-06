<?php

namespace App\Notifications;

use App\Models\VisitorVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class VisitorInvitationForGuest extends Notification
{
    use Queueable;

    public function __construct(
        private VisitorVisit $visit,
        private string $qrToken
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $visit = $this->visit->loadMissing(['visitor', 'branch', 'host.user', 'purpose']);
        $qrOptions = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 8,
        ]);
        $qrPng = (new QRCode($qrOptions))->render($this->qrToken);
        $data = [
            'visit' => $visit,
            'qrPng' => $qrPng,
        ];

        return (new MailMessage)
            ->subject('Your visitor invitation for ' . $visit->visit_date->format('d M Y'))
            ->view([
                'html' => 'emails.visitor-invitation',
                'text' => 'emails.visitor-invitation-text',
            ], $data);
    }
}
