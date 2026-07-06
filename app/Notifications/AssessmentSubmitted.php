<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentSubmitted extends Notification
{
    use Queueable;

    protected $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $sub = $this->submission;
        $employeeName = $sub->assignment->employee->user->name ?? 'An employee';
        $subject = $sub->assignment->subject->name ?? '';
        $class = $sub->assignment->schoolClass->name ?? '';
        $targetDesc = $sub->target->description ?? '';
        $deadline = $sub->target->deadline_date ?? '';

        return (new MailMessage)
                    ->subject('Pending Approval: Assessment Submission - ' . $subject . ' - ' . $class)
                    ->greeting("Assalamu'alaikum Warahmatullahi Wabarakatuh, " . $notifiable->name)
                    ->line('We pray this email finds you in the best of health and Iman.')
                    ->line('A new assessment has been submitted by **' . $employeeName . '** and is ready for your review. Timely evaluation is crucial for maintaining our educational targets and ensuring excellence in our teaching delivery.')
                    ->line('**Subject:** ' . $subject . ' (' . $class . ')')
                    ->line('**Target:** ' . $targetDesc . ' (Due: ' . $deadline . ')')
                    ->action('Review Submission', url('/assessment/approvals'))
                    ->line('Jazakumullah khairan for your continuous support and leadership. May Allah Subhaanahu Wa Ta\'aala reward your efforts.')
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
