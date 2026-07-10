<?php

namespace App\Mail;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayrollSlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payroll $payroll,
        public string $pdfPath,
        public string $slipPassword
    ) {}
 
    public function build()
    {
        $period = \Carbon\Carbon::parse($this->payroll->period_start)->translatedFormat('F Y');

        return $this->mailer('payroll')
            ->subject('Slip Gaji - ' . $period . ' - ' . $this->payroll->employee->employee_name)
            ->view('mail.PayrollSlipMail')
            ->with([
                'employeeName'   => $this->payroll->employee->employee_name,
                'payrollPeriod'  => $period,
                'netSalary'      => $this->payroll->net_salary,
                'totalDeduction' => $this->payroll->total_deduction,
                'companyName'    => $this->payroll->employee->company->name ?? 'PT. Asian Bay Development',
            ])
            ->from(
                env('PAYROLL_MAIL_FROM_ADDRESS'),
                env('PAYROLL_MAIL_FROM_NAME', 'HRX Payroll')
            )
            ->attach($this->pdfPath, [
                'as'   => 'Slip_Gaji_' . $this->payroll->employee->employee_pengenal . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}

   // public function build()
    // {
    //     $period = \Carbon\Carbon::parse($this->payroll->period_start)->translatedFormat('F Y');
    //     return $this->subject('Slip Gaji - ' . $period . ' - ' . $this->payroll->employee->employee_name)
    //         ->view('mail.PayrollSlipMail')
    //         ->with([
    //             'employeeName'   => $this->payroll->employee->employee_name,
    //             'payrollPeriod'  => $period,
    //             'netSalary'      => $this->payroll->net_salary,
    //             'totalDeduction' => $this->payroll->total_deduction,
    //             'companyName'    => $this->payroll->employee->company->name ?? 'PT. Asian Bay Development',
    //             'slipPassword'   => $this->slipPassword,
    //         ])
    //         ->attach($this->pdfPath, [
    //             'as'   => 'Slip_Gaji_' . $this->payroll->employee->employee_pengenal . '.pdf',
    //             'mime' => 'application/pdf',
    //         ]);
    // }