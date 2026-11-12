<?php

namespace App\Notifications;

use App\Models\VisitorVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitorVisitNotification extends Notification
{
    use Queueable;

    public function __construct(
        private VisitorVisit $visit,
        private string $event
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $visit = $this->visit->loadMissing(['visitor', 'branch', 'purpose']);
        $details = [
            'Visitor' => $visit->visitor->full_name,
            'Visit date' => $visit->visit_date->format('d M Y'),
            'Meeting location' => $visit->branch->name,
            'Host' => $visit->host()->with('user')->first()?->user?->name ?? 'School contact',
            'Purpose' => $visit->purpose->name,
            'Visit code' => $visit->visit_code,
        ];
        $data = [
            'recipientName' => $notifiable->name,
            'details' => $details,
            'qrPng' => null,
            'actionLabel' => 'Open Visitor Dashboard',
            'actionUrl' => url('/visitor/dashboard'),
            'actionNote' => null,
        ];

        if ($this->event === 'arrival') {
            $data['title'] = 'Visitor Arrival Notification';
            $data['intro'] = $visit->visitor->full_name . ' has checked in at ' . $visit->branch->name . '.';
            $data['details']['Check-in time'] = now()->format('d M Y H:i');

            return (new MailMessage)
                ->subject('Visitor has arrived: ' . $visit->visitor->full_name)
                ->view([
                    'html' => 'email-template.visitor-notification',
                    'text' => 'email-template.visitor-notification-text',
                ], $data);
        }

        if ($this->event === 'approval') {
            $data['title'] = 'Visitor Approval Request';
            $data['intro'] = $visit->visitor->full_name . ' is expected at ' . $visit->branch->name
                . ' on ' . $visit->expected_arrival_at?->format('d M Y H:i') . '.';
            $data['details']['Expected departure'] = $visit->expected_departure_at?->format('d M Y H:i') ?? '—';
            $data['actionLabel'] = 'Review Visitor Approvals';
            $data['actionUrl'] = url('/visitor/approvals');
            $data['actionNote'] = 'Your approval is required before check-in.';
            $subject = 'Visitor approval requested: ' . $visit->visitor->full_name;
        } else {
            $data['title'] = 'Visitor Invitation Created';
            $data['intro'] = $visit->visitor->full_name . ' is expected at ' . $visit->branch->name
                . ' on ' . $visit->expected_arrival_at?->format('d M Y H:i') . '.';
            $data['details']['Expected departure'] = $visit->expected_departure_at?->format('d M Y H:i') ?? '—';
            $subject = 'Visitor invitation created: ' . $visit->visitor->full_name;
        }

        return (new MailMessage)
            ->subject($subject)
            ->view([
                'html' => 'email-template.visitor-notification',
                'text' => 'email-template.visitor-notification-text',
            ], $data);
    }
}
