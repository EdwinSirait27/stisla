<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserUpdateRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $field;
    public $newValue;

    public function __construct($user, $field, $newValue)
    {
        $this->user = $user;
        $this->field = $field;
        $this->newValue = $newValue;
    }

    public function build()
    {
        return $this->subject(ucfirst($this->field) . ' Update Request')
            ->view('emails.user_update_requested')
            ->with([
                'user'     => $this->user,
                'field'    => $this->field,
                'newValue' => $this->newValue,
            ]);
    }
}
