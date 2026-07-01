<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
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

    // public function generateBulk(\Illuminate\Support\Collection $payrolls): array
    // {
    //     $payrolls->load(['employee.company', 'details.component']);

    //     $files = [];

    //     foreach ($payrolls as $payroll) {
    //         $pdf = $this->generateSingle($payroll);

    //         $filename = 'Slip_' . $payroll->employee->employee_pengenal . '_'
    //             . $payroll->period_month . $payroll->period_year . '.pdf';

    //         $path = storage_path('app/temp-slips/' . $filename);

    //         if (!file_exists(dirname($path))) {
    //             mkdir(dirname($path), 0755, true);
    //         }

    //         $pdf->save($path);
    //         $files[] = $path;
    //     }

    //     return $files;
    // }
    public function downloadSlipBulk(Request $request, string $periodId)
{
    $period = PayrollPeriod::findOrFail($periodId);

    $payrolls = Payroll::with(['employee.company', 'details.component'])
        ->where('payroll_period_id', $periodId)
        ->whereIn('status', ['approved', 'paid'])
        ->get();

    if ($request->filled('ids')) {
        $payrolls = $payrolls->whereIn('id', $request->ids);
    }

    $filePaths = app(PayrollSlipService::class)->generateBulk($payrolls);

    if (empty($filePaths)) {
        return back()->with('error', 'Tidak ada slip yang bisa di-generate.');
    }

    $zipDir = storage_path('app/temp-zip');
    if (!file_exists($zipDir)) {
        mkdir($zipDir, 0755, true);
    }

    $zipFileName = 'Slip_Gaji_Bulk_' . $period->period_label . '_' . now()->timestamp . '.zip';
    $zipPath = $zipDir . '/' . $zipFileName;

    $zip = new \ZipArchive();
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        return back()->with('error', 'Gagal membuat file zip.');
    }

    foreach ($filePaths as $path) {
        if (file_exists($path)) {
            $zip->addFile($path, basename($path));
        }
    }
    $zip->close();

    // hapus file pdf sementara setelah di-zip
    foreach ($filePaths as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}
}