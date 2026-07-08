<?php

// namespace App\Services;

// use App\Models\Payroll;
// use App\Models\PayrollPeriod;
// use Barryvdh\DomPDF\Facade\Pdf;

// class PayrollSlipService
// {
//     public function generateSingle(Payroll $payroll): \Barryvdh\DomPDF\PDF
//     {
//         $payroll->load(['employee.company', 'details.component']);

//         $pdf = Pdf::loadView('pages.Payroll.slip', [
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

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class PayrollSlipService
{
    public function generateSingle(Payroll $payroll): array
    {
        $payroll->load(['employee.company', 'details.component']);

        $pdf = Pdf::loadView('pages.Payroll.slip', [
            'payroll' => $payroll,
        ])->setPaper('a4', 'portrait');
        $password = $this->generateReadableRandomKey(8);
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->get_cpdf()->setEncryption($password, null, ['copy', 'print']);
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

    public function generateBulk($payrolls): array
    {
        $results = [];

        foreach ($payrolls as $payroll) {
            try {
                if (!$payroll->employee) {
                    Log::warning('PayrollSlipService: skip, employee not found', [
                        'payroll_id' => $payroll->id,
                    ]);
                    continue;
                }

                ['pdf' => $pdf, 'password' => $password] = $this->generateSingle($payroll);

                $tempPath = storage_path(
                    'app/temp-slips/Slip_' . $payroll->employee->employee_pengenal
                    . '_' . $payroll->id . '_' . uniqid('', true) . '.pdf'
                );

                if (!file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }

                $pdf->save($tempPath);
                $results[] = ['path' => $tempPath, 'password' => $password];
            } catch (\Throwable $e) {
                Log::error('PayrollSlipService: failed to generate PDF in bulk', [
                    'payroll_id' => $payroll->id,
                    'error'      => $e->getMessage(),
                ]);
                continue;
            }
        }
        return $results;
    }
}
// class PayrollSlipService
// {
//     public function generateSingle(Payroll $payroll): \Barryvdh\DomPDF\PDF
//     {
//         $payroll->load(['employee.company', 'details.component']);

//         $pdf = Pdf::loadView('pages.Payroll.slip', [
//             'payroll' => $payroll,
//         ])->setPaper('a4', 'portrait');

//         // ── Password protection pakai tanggal lahir format Ymd ──
//         $password = $payroll->employee->date_of_birth
//             ? Carbon::parse($payroll->employee->date_of_birth)->format('Ymd')
//             : null;

//         if ($password) {
//             $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
//             $canvas = $pdf->getDomPDF()->getCanvas();
//             $canvas->get_cpdf()->setEncryption($password, null, ['copy', 'print']);
//         } else {
//             Log::warning('PayrollSlipService: PDF generated WITHOUT password protection', [
//                 'payroll_id'  => $payroll->id,
//                 'employee_id' => $payroll->employee->id,
//                 'reason'      => 'missing date_of_birth',
//             ]);
//         }

//         return $pdf;
//     }

//     /**
//      * Generate PDF untuk banyak payroll sekaligus, return array path file temporary.
//      * Dipakai oleh downloadSlipBulk() di controller.
//      */
//     public function generateBulk($payrolls): array
//     {
//         $filePaths = [];

//         foreach ($payrolls as $payroll) {
//             try {
//                 if (!$payroll->employee) {
//                     Log::warning('PayrollSlipService: skip, employee not found', [
//                         'payroll_id' => $payroll->id,
//                     ]);
//                     continue;
//                 }

//                 $pdf = $this->generateSingle($payroll);

//                 $tempPath = storage_path(
//                     'app/temp-slips/Slip_' . $payroll->employee->employee_pengenal
//                     . '_' . $payroll->id . '_' . uniqid('', true) . '.pdf'
//                 );

//                 if (!file_exists(dirname($tempPath))) {
//                     mkdir(dirname($tempPath), 0755, true);
//                 }

//                 $pdf->save($tempPath);
//                 $filePaths[] = $tempPath;
//             } catch (\Throwable $e) {
//                 Log::error('PayrollSlipService: failed to generate PDF in bulk', [
//                     'payroll_id' => $payroll->id,
//                     'error'      => $e->getMessage(),
//                 ]);
//                 continue; // lanjut ke payroll berikutnya, jangan hentikan seluruh proses
//             }
//         }

//         return $filePaths;
//     }
// }