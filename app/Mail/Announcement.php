<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Announcment;

class Announcement extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
   public $announcement;
   public $employee;
    /**
     * Create a new message instance.
     */
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