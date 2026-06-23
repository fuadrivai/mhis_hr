<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LessonPlanSubmitted extends Notification
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
        $employeeName = $sub->employeeSubject->employee->name ?? 'An employee';
        $subject = $sub->employeeSubject->subject->name ?? '';
        $class = $sub->employeeSubject->schoolClass->name ?? '';
        $week = $sub->week_number;
        $month = $sub->lessonPlanTargetMonth->month . ' ' . $sub->lessonPlanTargetMonth->year;

        return (new MailMessage)
                    ->subject('Pending Approval: Lesson Plan Submission - ' . $subject . ' - ' . $class)
                    ->greeting("Assalamu'alaikum Warahmatullahi Wabarakatuh, " . $notifiable->name)
                    ->line('We pray this email finds you in the best of health and Iman.')
                    ->line('A new lesson plan has been submitted by **' . $employeeName . '** and is ready for your review. Timely evaluation is crucial for maintaining our educational targets and ensuring excellence in our teaching delivery.')
                    ->line('**Subject:** ' . $subject . ' (' . $class . ')')
                    ->line('**Target Timeline:** ' . $month . ' - Week ' . $week)
                    ->action('Review Submission', url('/lesson-plan/approvals'))
                    ->line('Jazakumullah khairan for your continuous support and leadership. May Allah Subhaanahu Wa Ta\'aala reward your efforts.')
                    ->salutation("Wassalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" . config('app.name'));
    }
}
