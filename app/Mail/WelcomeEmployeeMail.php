<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Employee;

class WelcomeEmployeeMail extends Mailable
{
    use Queueable, SerializesModels;
   public $employee;
    /**
     * Create a new message instance.
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }
    /**
     * Get the message envelope.
     */
    public function build()
    {
        return $this->subject('Welcome to PT Mahendradata Jaya Mandiri')
                    ->view('emails.welcome', [
                        'employee' => $this->employee,
                        'department' => $this->employee->department,
                        'position' => $this->employee->position,
                        'company' => $this->employee->company,
                        'store' => $this->employee->store,
                     
                    ]);
    }
}
