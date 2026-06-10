<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeProbationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $headHR;

    public function __construct($employee, $headHR)
    {
        $this->employee = $employee;
        $this->headHR   = $headHR;
    }

    public function build()
    {
        return $this->subject('Probation Reminder — ' . $this->employee->employee_name)
                    ->view('emails.probation-reminder');
    }
}