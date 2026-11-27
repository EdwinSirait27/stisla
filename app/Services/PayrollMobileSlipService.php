<?php

// namespace App\Services;

// use App\Models\PayrollMobile;
// use App\Models\PayrollMobilePeriod;
// use Barryvdh\DomPDF\Facade\Pdf;

// class PayrollMobileSlipService
// {
//     public function generateSingle(PayrollMobile $payroll): \Barryvdh\DomPDF\PDF
//     {
//         $payroll->load(['employee.company', 'details.component']);

//         $pdf = Pdf::loadView('pages.PayrollMobile.slip', [
//             'payroll' => $payroll,
//         ])->setPaper('a4', 'portrait');

//         // ── Password protection pakai tanggal lahir format Ymd ──
//         $password = $payroll->employee->date_of_birth
//             ? \Carbon\Carbon::parse($payroll->employee->date_of_birth)->format('Ymd')
//             : null;

//         if ($password) {
//             $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
//             $canvas = $pdf->getDomPDF()->getCanvas();
//             $canvas->get_cpdf()->setEncryption($password, null, ['copy', 'print']);
//         }

//         return $pdf;
//     }
// }
namespace App\Services;

use App\Models\PayrollMobile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class PayrollMobileSlipService
{
    // public function generateSingle(PayrollMobile $payroll): array
    // {
    //     $payroll->load(['employee.company', 'details.component']);

    //     $pdf = Pdf::loadView('pages.PayrollMobile.slip', [
    //         'payroll' => $payroll,
    //     ])->setPaper('a4', 'portrait');
    //     $password = $this->generateReadableRandomKey(8);
    //     $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
    //     $canvas = $pdf->getDomPDF()->getCanvas();
    //     $canvas->get_cpdf()->setEncryption($password, null, ['copy', 'print']);
    //     return [
    //         'pdf'      => $pdf,
    //         'password' => $password,
    //     ];
    // }
    /**
     * Untuk dikirim lewat EMAIL — password random, ditampilkan di body email.
     */
     public function generateSingle(PayrollMobile $payroll): array
    {
        $payroll->load(['employee.company', 'details.component']);

        $pdf = Pdf::loadView('pages.PayrollMobile.slip', [
            'payroll' => $payroll,
        ])->setPaper('a4', 'portrait');

        $password = $this->generateReadableRandomKey(8);

        $this->encryptPdf($pdf, $password);

        return [
            'pdf'      => $pdf,
            'password' => $password,
        ];
    }

     protected function generateReadableRandomKey(int $length = 8): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $key;
    }
    /**
     * Untuk DOWNLOAD internal oleh HR (single & bulk) — password pakai tanggal lahir.
     */
     public function generateForDownload(PayrollMobile $payroll): array
    {
        $payroll->load(['employee.company', 'details.component']);

        $pdf = Pdf::loadView('pages.PayrollMobile.slip', [
            'payroll' => $payroll,
        ])->setPaper('a4', 'portrait');

        $password = $payroll->employee->date_of_birth
            ? Carbon::parse($payroll->employee->date_of_birth)->format('Ymd')
            : null;

        if ($password) {
            $this->encryptPdf($pdf, $password);
        } else {
            Log::warning('PayrollMobileSlipService: PDF for download generated WITHOUT password', [
                'payroll_id'  => $payroll->id,
                'employee_id' => $payroll->employee->id,
                'reason'      => 'missing date_of_birth',
            ]);
        }

        return [
            'pdf'      => $pdf,
            'password' => $password,
        ];
    }
     protected function encryptPdf($pdf, string $password): void
    {
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->get_cpdf()->setEncryption($password, null, ['copy', 'print']);
    }
     /**
     * Untuk bulk download HR — semua pakai password tanggal lahir (konsisten dengan single download).
     */
    public function generateBulkForDownload($payrolls): array
    {
        $results = [];

        foreach ($payrolls as $payroll) {
            try {
                if (!$payroll->employee) {
                    Log::warning('PayrollMobileSlipService: skip, employee not found', [
                        'payroll_id' => $payroll->id,
                    ]);
                    continue;
                }

                ['pdf' => $pdf, 'password' => $password] = $this->generateForDownload($payroll);
                $results[] = ['payroll' => $payroll, 'pdf' => $pdf, 'password' => $password];
            } catch (\Throwable $e) {
                Log::error('PayrollMobileSlipService: failed to generate PDF for bulk download', [
                    'payroll_id' => $payroll->id,
                    'error'      => $e->getMessage(),
                ]);
                continue;
            }
        }

        return $results;
    }

    // public function generateBulk($payrolls): array
    // {
    //     $results = [];

    //     foreach ($payrolls as $payroll) {
    //         try {
    //             if (!$payroll->employee) {
    //                 Log::warning('PayrollMobileSlipService: skip, employee not found', [
    //                     'payroll_id' => $payroll->id,
    //                 ]);
    //                 continue;
    //             }

    //             ['pdf' => $pdf, 'password' => $password] = $this->generateSingle($payroll);

    //             $tempPath = storage_path(
    //                 'app/temp-slips-mobile/Slip_' . $payroll->employee->employee_pengenal
    //                 . '_' . $payroll->id . '_' . uniqid('', true) . '.pdf'
    //             );

    //             if (!file_exists(dirname($tempPath))) {
    //                 mkdir(dirname($tempPath), 0755, true);
    //             }

    //             $pdf->save($tempPath);
    //             $results[] = ['path' => $tempPath, 'password' => $password];
    //         } catch (\Throwable $e) {
    //             Log::error('PayrollMobileSlipService: failed to generate PDF in bulk', [
    //                 'payroll_id' => $payroll->id,
    //                 'error'      => $e->getMessage(),
    //             ]);
    //             continue;
    //         }
    //     }
    //     return $results;
    // }

}
