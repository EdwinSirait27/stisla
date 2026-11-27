<?php

namespace App\Console\Commands;

use App\Models\DocumentTemporaryAssignment;
use App\Models\Documents;
use App\Models\Employee;
use App\Services\SuratTugasService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Dijalankan tiap 5 menit (Kernel). Dua tahap, berurutan:
 * 1. Kedaluwarsa: ST yang tanggal selesainya SUDAH LEWAT (expired_date < hari ini) dikembalikan.
 *    Tanggal selesai masih termasuk masa tugas, jadi karyawan kembali keesokan harinya.
 * 2. Mulai: ST terjadwal yang tanggal mulainya sudah tiba diterapkan.
 * Urutan ini membuat ST berurutan (yang kedua mulai sehari setelah yang pertama selesai) berjalan benar.
 */
class ExpireSuratTugas extends Command
{
    protected $signature = 'documents:expire-surat-tugas';
    protected $description = 'Terapkan Surat Tugas (ST) yang tanggal mulainya tiba, dan kembalikan penempatan karyawan yang ST-nya sudah selesai';

    public function handle(SuratTugasService $service)
    {
        $st = fn() => Documents::whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('nickname', 'ST'))
            ->where('status', SuratTugasService::ACTIVE_STATUS);
        $today = today()->toDateString();

        $expired = $this->each(
            $st()->whereNotNull('expired_date')->whereDate('expired_date', '<', $today)->pluck('id'),
            function (Documents $document, ?Employee $employee) use ($service) {
                if ($employee) {
                    $service->revertDocument($employee, $document);
                }
                $document->update(['status' => 'expired']);
            }
        );

        $started = $this->each(
            $st()->whereDate('issued_date', '<=', $today)
                ->whereExists(fn($q) => $q->from((new DocumentTemporaryAssignment)->getTable() . ' as a')
                    ->whereColumn('a.document_id', 'documents.id')->whereNull('a.applied_at')->whereNull('a.reverted_at'))
                ->pluck('id'),
            function (Documents $document, ?Employee $employee) use ($service) {
                if ($employee && $service->hasStarted($document)) {
                    $service->applyPlanned($employee, $document);
                }
            }
        );

        $this->info("Surat Tugas dikembalikan: {$expired}, mulai diterapkan: {$started}");
    }

    /** Proses tiap dokumen dalam transaksinya sendiri; satu gagal tidak menghentikan yang lain. */
    private function each($ids, callable $fn): int
    {
        $done = 0;
        foreach ($ids as $documentId) {
            try {
                DB::transaction(function () use ($documentId, $fn) {
                    $document = Documents::where('id', $documentId)->lockForUpdate()->first();
                    // Sudah ditangani proses lain, atau tidak lagi berlaku
                    if (!$document || $document->status !== SuratTugasService::ACTIVE_STATUS) {
                        return;
                    }
                    $employee = Employee::where('id', $document->employee_id)->lockForUpdate()->first();
                    $fn($document, $employee);
                }, 3);
                $done++;
            } catch (Throwable $e) {
                Log::error('ExpireSuratTugas gagal untuk dokumen', ['document_id' => $documentId, 'error' => $e->getMessage()]);
            }
        }

        return $done;
    }
}
