<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope; 
use Illuminate\Queue\SerializesModels;
use App\Models\Payrolls;
use Illuminate\Support\Facades\Storage;
class PayrollMail extends Mailable
{
    use Queueable, SerializesModels;
    public $payroll;
    public $employee;
    public $subject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payrolls $payroll, $subject = null)
    {
        $this->payroll = $payroll;
        $this->employee = $payroll->employee;
        $this->subject = $subject ?? 'Slip Gaji Periode ' . $payroll->month_year->format('F Y');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->subject)
                    ->view('emails.payroll')  // Pastikan nama viewnya benar
                    ->with([
                        'employeeName' => $this->employee->employee_name,
                        'payrollPeriod' => $this->payroll->month_year->format('F Y'),
                        'payrollDate' => $this->payroll->month_year->format('d F Y'),
                        'basicSalary' => $this->payroll->salary ? ($this->payroll->salary) : 0,
                        'grossSalary' => $this->payroll->deductions ? ($this->payroll->deductions) : 0,
                    ]);
        // Cek apakah attachment file ada
        if ($this->payroll->attachment_path && Storage::disk('public')->exists($this->payroll->attachment_path)) {
            $mail->attach(Storage::disk('public')->path($this->payroll->attachment_path), [
                'as' => 'Slip_Gaji_' . $this->payroll->month_year->format('F_Y') . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        return $mail;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Payroll Mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
   
}
