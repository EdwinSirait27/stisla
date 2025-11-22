<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class AnnouncementMail extends Mailable
{
   use Queueable, SerializesModels;
    public $announcement;
    public $employee;

    public function __construct($announcement, $employee)
    {
        $this->announcement = $announcement;
        $this->employee = $employee;
    }

    public function build()
    {
        return $this->subject('New Announcement: ' . $this->announcement->title)
            ->view('emails.announcement');
    }
}