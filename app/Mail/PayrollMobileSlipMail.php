<?php

namespace App\Mail;

use App\Models\PayrollMobile;
use App\Models\PayrollMobilePeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class PayrollMobileSlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PayrollMobile $payroll,
        public string $pdfPath,
        public string $slipPassword
    ) {}
 
    public function build()
    {
        // Nama periode dari period_month + period_year (PayrollMobilePeriod::period_label), mis. "Juni 2026".
        // Dulu dihitung dari period_start, jadi slip periode Juni ter-subject "May 2026".
        // Fallback memakai period_month/period_year milik payroll sendiri dengan format yang sama.
        $period = $this->payroll->periodLabel();

        return $this->mailer('payroll')
            ->subject('Slip Gaji - ' . $period . ' - ' . $this->payroll->employee->employee_name)
            ->view('mail.PayrollMobileSlipMail')
            ->with([
                'employeeName'   => $this->payroll->employee->employee_name,
                'payrollPeriod'  => $period,
                'netSalary'      => $this->payroll->net_salary,
                'totalDeduction' => $this->payroll->total_deduction,
                'companyName'    => $this->payroll->employee->company->name ?? 'PT. Asian Bay Development',
            ])
            ->from(
                config('mail.mailers.payroll.from.address'),
                config('mail.mailers.payroll.from.name', 'HRX Payroll')
            )
            ->attach($this->pdfPath, [
                'as'   => $this->payroll->slipFilename(),
                'mime' => 'application/pdf',
            ]);
    }
}

   