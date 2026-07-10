<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TimeoffMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $approvalRequest;
    public function __construct($approvalRequest)
    {
        $this->approvalRequest = $approvalRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $approvalRequest = $this->approvalRequest;
        return $this->subject($approvalRequest['subject'])
            ->view($approvalRequest['template'])
            ->with('data',$this->approvalRequest);
    }
}
