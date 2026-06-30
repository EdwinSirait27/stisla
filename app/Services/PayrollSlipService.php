<?php

namespace App\Services;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollSlipService
{
    public function generateSingle(Payroll $payroll): \Barryvdh\DomPDF\PDF
    {
        $payroll->load(['employee.company', 'details.component']);

        $pdf = Pdf::loadView('pages.Payroll.slip', [
            'payroll' => $payroll,
        ])->setPaper('a4', 'portrait');

        // ── Password protection pakai tanggal lahir format Ymd ──
        $password = $payroll->employee->date_of_birth
            ? \Carbon\Carbon::parse($payroll->employee->date_of_birth)->format('Ymd')
            : null;

        if ($password) {
            $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
            $canvas = $pdf->getDomPDF()->getCanvas();
            $canvas->get_cpdf()->setEncryption($password, null, ['copy', 'print']);
        }

        return $pdf;
    }

    public function generateBulk(\Illuminate\Support\Collection $payrolls): array
    {
        $payrolls->load(['employee.company', 'details.component']);

        $files = [];

        foreach ($payrolls as $payroll) {
            $pdf = $this->generateSingle($payroll);

            $filename = 'Slip_' . $payroll->employee->employee_pengenal . '_'
                . $payroll->period_month . $payroll->period_year . '.pdf';

            $path = storage_path('app/temp-slips/' . $filename);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);
            $files[] = $path;
        }

        return $files;
    }
}