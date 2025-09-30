<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserUpdateRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $changes;

    /**
     * Buat instance mail baru
     */
    public function __construct($user, array $changes)
    {
        $this->user = $user;
        $this->changes = $changes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Employee Data Update Request')
            ->view('emails.user_update_requested')
            ->with([
                'user'    => $this->user,
                'changes' => $this->changes,
            ]);
    }
}
