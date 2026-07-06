<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentNeedRevision extends Notification
{
    use Queueable;

    protected $submission;
    protected $notes;

    public function __construct($submission, $notes)
    {
        $this->submission = $submission;
        $this->notes = $notes;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $sub = $this->submission;
        $subject = $sub->assignment->subject->name ?? '';
        $class = $sub->assignment->schoolClass->name ?? '';
        $targetDesc = $sub->target->description ?? '';
        $deadline = $sub->target->deadline_date ?? '';

        return (new MailMessage)
                    ->subject('Action Required: Revision for Assessment - ' . $subject . ' - ' . $class)
                    ->greeting("Assalamu'alaikum Warahmatullahi Wabarakatuh, " . $notifiable->name)
                    ->line('To ensure we meet our targeted outcomes with excellence, your recent assessment submission requires some revisions.')
                    ->line('**Subject:** ' . $subject . ' (' . $class . ')')
                    ->line('**Target:** ' . $targetDesc . ' (Due: ' . $deadline . ')')
                    ->line('**Approver Comments:** ' . $this->notes)
                    ->action('Review and Update Assessment', url('/assessment/my'))
                    ->line('May Allah Subhaanahu Wa Ta\'aala grant you ease and barakah in refining this assessment. We look forward to your prompt resubmission.')
                    ->salutation("Wassalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
