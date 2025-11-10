<?php

namespace App\Mail;

use App\Models\Submissionposition;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Sendpositionrequesttodir extends Mailable
{
      use Queueable, SerializesModels;
    public $submission;
    /**
     * Create a new message instance.
     */
    public function __construct(Submissionposition $submission)
    {
        $this->submission = $submission;
    }
    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Request Position')
                    ->view('emails.requestpositiontodir', [
                        'submission' => $this->submission,
                    ]);
    }
}