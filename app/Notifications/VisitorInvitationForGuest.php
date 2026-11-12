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
            'title' => 'Visitor Invitation',
            'recipientName' => $visit->visitor->full_name,
            'intro' => 'You are invited to visit Mutiara Harapan Islamic School.',
            'details' => [
                'Visit date' => $visit->visit_date->format('d M Y'),
                'Visit time' => ($visit->expected_arrival_at?->format('H:i') ?? '—')
                    . ' - ' . ($visit->expected_departure_at?->format('H:i') ?? '—'),
                'Meeting location' => $visit->branch->name,
                'Host' => $visit->host->user?->name ?? 'School contact',
                'Purpose' => $visit->purpose->name,
                'Visit code' => $visit->visit_code,
            ],
        ];

        return (new MailMessage)
            ->subject('Your visitor invitation for ' . $visit->visit_date->format('d M Y'))
            ->view([
                'html' => 'email-template.visitor-notification',
                'text' => 'email-template.visitor-notification-text',
            ], $data);
    }
}
