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

class ExpireSuratTugas extends Command
{
    protected $signature = 'documents:expire-surat-tugas';
    protected $description = 'Kembalikan department/store/position karyawan yang expired_date Surat Tugas (ST) nya sudah lewat';

    public function handle(SuratTugasService $suratTugasService)
    {
        $expiredIds = Documents::whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('nickname', 'ST'))
            ->where('status', SuratTugasService::ACTIVE_STATUS)
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', '<=', now()->toDateString())
            ->pluck('id');

        $reverted = 0;

        foreach ($expiredIds as $documentId) {
            try {
                DB::transaction(function () use ($documentId, $suratTugasService) {
                    $document = Documents::where('id', $documentId)->lockForUpdate()->first();

                    // Already handled by a concurrent run, or no longer eligible.
                    if (!$document || $document->status !== SuratTugasService::ACTIVE_STATUS) {
                        return;
                    }

                    $employee = Employee::where('id', $document->employee_id)->lockForUpdate()->first();

                    if (!$employee) {
                        $document->update(['status' => 'expired']);
                        return;
                    }

                    $assignments = DocumentTemporaryAssignment::where('document_id', $document->id)
                        ->whereNull('reverted_at')
                        ->lockForUpdate()
                        ->get();

                    $suratTugasService->revertAssignments($employee, $assignments);

                    $document->update(['status' => 'expired']);
                }, 3);

                $reverted++;
            } catch (Throwable $e) {
                Log::error('ExpireSuratTugas failed for document', [
                    'document_id' => $documentId,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $this->info("Surat Tugas expired diproses: {$reverted}");
    }
}
