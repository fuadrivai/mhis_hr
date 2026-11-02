<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\InterviewRound;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruitmentInterviewInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public InterviewRound $round;
    public Employee $interviewer;

    public function __construct(InterviewRound $round, Employee $interviewer)
    {
        $this->round = $round;
        $this->interviewer = $interviewer;
    }

    public function build()
    {
        $fromAddress = config('recruitment.mail.from_address', config('mail.from.address'));
        $fromName = config('recruitment.mail.from_name', config('mail.from.name'));

        return $this->from($fromAddress, $fromName)
            ->subject('Interview Invitation: ' . ($this->round->candidateApplication->candidate->full_name ?? 'Candidate'))
            ->html($this->renderContent());
    }

    protected function renderContent(): string
    {
        $candidateName = htmlspecialchars($this->round->candidateApplication->candidate->full_name ?? '-');
        $position = htmlspecialchars($this->round->candidateApplication->jobOpening->title ?? $this->round->candidateApplication->position_applied ?? '-');
        $date = $this->round->scheduled_at ? $this->round->scheduled_at->format('l, d F Y H:i') : '-';
        $location = htmlspecialchars($this->round->location_or_link ?? 'To be advised');
        $type = htmlspecialchars($this->round->round_type_label);
        $portalUrl = url('/recruitment/my-interviews');

        return <<<HTML
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 6px; padding: 24px;">
    <h2 style="color: #2A3F54; margin-top: 0;">Recruitment Interview Assignment</h2>
    <p>Dear {$this->interviewer->personal->first_name},</p>
    <p>You have been assigned as an interviewer for the following interview round:</p>
    <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
        <tr><td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; width: 140px;">Candidate:</td><td style="padding: 8px; border-bottom: 1px solid #eee;">{$candidateName}</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Position:</td><td style="padding: 8px; border-bottom: 1px solid #eee;">{$position}</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Round:</td><td style="padding: 8px; border-bottom: 1px solid #eee;">{$type} (Round {$this->round->round_no})</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Schedule:</td><td style="padding: 8px; border-bottom: 1px solid #eee;">{$date}</td></tr>
        <tr><td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Location / Link:</td><td style="padding: 8px; border-bottom: 1px solid #eee;">{$location}</td></tr>
    </table>
    <p>Please log in to the portal to view candidate details and submit your recommendation form after the session:</p>
    <p style="text-align: center; margin: 24px 0;">
        <a href="{$portalUrl}" style="background-color: #26B99A; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">Open My Interviews</a>
    </p>
    <p style="font-size: 12px; color: #888;">This is an automated notification from MHIS HR Recruitment.</p>
</div>
HTML;
    }
}

