<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Payrolls;
use App\Models\Employee;
class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;
    public $payroll;
    public $employee;
    public $attachment_path;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payrolls $payroll, Employee $employee, $attachment_path)
    {
        $this->payroll = $payroll;
        $this->employee = $employee;
        $this->attachment_path = $attachment_path;
    }
    public function build()
    {
        return $this->subject('Slip Gaji - ' . date('F Y', strtotime($this->payroll->month_year)))
                    ->view('emails.Payslip')
                    ->attach($this->attachment_path, [
                        'as' => 'slip-gaji-' . date('F-Y', strtotime($this->payroll->month_year)) . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Payslip Mail',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
