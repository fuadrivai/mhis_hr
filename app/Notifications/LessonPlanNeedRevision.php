<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LessonPlanNeedRevision extends Notification
{
    use Queueable;

    protected $submission;
    protected $comments;

    public function __construct($submission, $comments)
    {
        $this->submission = $submission;
        $this->comments = $comments;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $sub = $this->submission;
        $subject = $sub->employeeSubject->subject->name ?? '';
        $class = $sub->employeeSubject->schoolClass->name ?? '';
        $week = $sub->week_number;
        $month = $sub->lessonPlanTargetMonth->month . ' ' . $sub->lessonPlanTargetMonth->year;

        return (new MailMessage)
                    ->subject('Action Required: Revision for Lesson Plan - ' . $subject . ' - ' . $class)
                    ->greeting("Assalamu'alaikum Warahmatullahi Wabarakatuh, " . $notifiable->name)
                    ->line('To ensure we meet our targeted outcomes with excellence, your recent lesson plan submission requires some revisions.')
                    ->line('**Subject:** ' . $subject . ' (' . $class . ')')
                    ->line('**Target Timeline:** ' . $month . ' - Week ' . $week)
                    ->line('**Approver Comments:** ' . $this->comments)
                    ->action('Review and Update Lesson Plan', url('/lesson-plan/my'))
                    ->line('May Allah Subhaanahu Wa Ta\'aala grant you ease and barakah in refining this lesson plan. We look forward to your prompt resubmission.')
                    ->salutation("Wassalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" . config('app.name'));
    }
}
