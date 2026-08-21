<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Mail\TimeoffMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 60;

    public int $announcementId;

    public string $email;

    public function __construct(int $announcementId, string $email)
    {
        $this->announcementId = $announcementId;
        $this->email = $email;
    }

    public function handle(): void
    {
        $announcement = Announcement::findOrFail($this->announcementId);

        $message = [
            'title' => $announcement->title,
            'content' => $announcement->content,
            'link' => $announcement->link,
            'subject' => 'You have a new announcement From MHIS HUB',
            'template' => 'email-template.announcement',
        ];

        $mail = new TimeoffMail($message);

        if ($announcement->attachment) {
            $attachmentFullPath = storage_path(
                'app/public/' . $announcement->attachment
            );

            if (file_exists($attachmentFullPath)) {
                $mail->attach($attachmentFullPath);
            }
        }

        Mail::mailer('smtp')
            ->to($this->email)
            ->send($mail);
    }
}
