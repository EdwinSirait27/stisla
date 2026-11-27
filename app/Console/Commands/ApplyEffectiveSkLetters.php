<?php

namespace App\Console\Commands;

use App\Models\SkLetter;
use App\Services\SkLetterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Terapkan penempatan & status karyawan dari SK yang sudah disetujui penuh dan tanggal efektifnya
 * sudah tiba (SK yang disetujui sebelum tanggal efektif). Gaji sudah dicatat saat disetujui.
 */
class ApplyEffectiveSkLetters extends Command
{
    protected $signature = 'sk:apply-effective';
    protected $description = 'Terapkan perubahan jabatan/penempatan/status dari SK yang tanggal efektifnya sudah tiba';

    public function handle(SkLetterService $service)
    {
        $letters = SkLetter::where('status', SkLetter::FINAL_STATUS)
            ->whereDate('effective_date', '<=', today())
            ->whereHas('employees', fn($q) => $q->whereNull('sk_letter_employees.applied_at'))
            ->get();

        $count = 0;
        foreach ($letters as $sk) {
            try {
                $count += $service->applyPlacement($sk);
            } catch (Throwable $e) {
                Log::error('sk:apply-effective gagal', ['sk_letter_id' => $sk->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Karyawan diterapkan dari SK: {$count}");
    }
}
