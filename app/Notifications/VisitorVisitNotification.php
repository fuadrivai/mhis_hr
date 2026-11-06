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
        $arrival = $visit->expected_arrival_at?->format('d M Y H:i') ?? $visit->visit_date->format('d M Y');
        $departure = $visit->expected_departure_at?->format('H:i');

        if ($this->event === 'arrival') {
            return (new MailMessage)
                ->subject('Visitor has arrived: ' . $visit->visitor->full_name)
                ->greeting('Hello ' . $notifiable->name)
                ->line($visit->visitor->full_name . ' has checked in at ' . $visit->branch->name . '.')
                ->line('Check-in time: ' . now()->format('H:i'))
                ->line('Purpose: ' . $visit->purpose->name)
                ->action('Open Visitor Dashboard', url('/visitor/dashboard'));
        }

        $message = (new MailMessage)
            ->subject(
                ($this->event === 'approval' ? 'Visitor approval requested: ' : 'Visitor invitation created: ')
                . $visit->visitor->full_name
            )
            ->greeting('Hello ' . $notifiable->name)
            ->line($visit->visitor->full_name . ' is expected at ' . $visit->branch->name . ' on ' . $arrival
                . ($departure ? ' until ' . $departure : '') . '.')
            ->line('Purpose: ' . $visit->purpose->name);

        if ($this->event === 'approval') {
            $message->line('Your approval is required before check-in.')
                ->action('Review Visitor Approvals', url('/visitor/approvals'));
        } else {
            $message->action('Open Visitor Dashboard', url('/visitor/dashboard'));
        }

        return $message;
    }
}
