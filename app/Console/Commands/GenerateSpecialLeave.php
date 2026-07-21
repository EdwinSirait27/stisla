<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Leavebalance;
use App\Models\Leavetypes;
use Carbon\Carbon;


class GenerateSpecialLeave extends Command
{
    protected $signature = 'leave:generate-special
        {--dry-run : Tampilkan siapa yang akan dapat saldo tanpa menyimpan}
        {--type= : Batasi ke satu jenis cuti tertentu (nama persis)}
        {--year= : Tahun saldo (default: tahun berjalan)}';

    protected $description = 'Generate saldo untuk jenis cuti khusus (maternity, pendamping, dll) berdasarkan aturan di DB';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $year   = (int) ($this->option('year') ?: now()->year);
        $onlyTy = trim((string) $this->option('type'));

        if ($dryRun) {
            $this->warn('=== MODE DRY-RUN: tidak ada data yang disimpan ===');
        }
        $this->info("Tahun saldo: {$year}");

        // Ambil jenis cuti khusus yang aktif
        $query = Leavetypes::where('is_special', true)->where('is_active', true);

        if ($onlyTy !== '') {
            $query->where('name', $onlyTy);
        }

        $types = $query->get();

        if ($types->isEmpty()) {
            $this->error('Tidak ada jenis cuti khusus (is_special) yang aktif' .
                ($onlyTy !== '' ? " dengan nama \"{$onlyTy}\"." : '.'));
            return self::FAILURE;
        }

        // Batas masa kerja: minimal 1 tahun (sama seperti annual leave)
        $tenureCutoff = now()->subYear()->toDateString();

        $grandCreated = 0;
        $grandSkipped = 0;
        $grandType    = 0;

        foreach ($types as $type) {
            $grandType++;
            $this->line('');
            $this->info("── {$type->name} (jatah: " . ($type->default_balance ?? 0) . " hari) ──");

            $balanceDays = $type->default_balance;

            if ($balanceDays === null || (float) $balanceDays <= 0) {
                $this->warn("  Dilewati: default_balance kosong/nol. Isi dulu lewat form.");
                continue;
            }

            // Bangun filter karyawan berdasarkan aturan jenis cuti ini
            $employees = $this->eligibleEmployees($type, $tenureCutoff);

            if ($employees->isEmpty()) {
                $this->warn("  Tidak ada karyawan yang memenuhi syarat.");
                continue;
            }

            $this->line("  Kandidat memenuhi syarat: {$employees->count()} karyawan");

            $created = 0;
            $skipped = 0;

            foreach ($employees as $emp) {
                // Anti-dobel: sudah punya saldo jenis ini di tahun ini?
                $exists = Leavebalance::where('employee_id', $emp->id)
                    ->where('leave_type_id', $type->id)
                    ->where('year', $year)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("    [akan dibuat] {$emp->employee_name}");
                    $created++;
                    continue;
                }

                Leavebalance::create([
                    'employee_id'   => $emp->id,
                    'leave_type_id' => $type->id,
                    'balance_days'  => $balanceDays,
                    'year'          => $year,
                ]);

                $created++;
            }

            $this->line("  " . ($dryRun ? 'Akan dibuat' : 'Dibuat') . ": {$created}, dilewati (sudah ada): {$skipped}");

            $grandCreated += $created;
            $grandSkipped += $skipped;
        }

        $this->line('');
        $this->info('═══════════════════════════════════');
        $this->info("Jenis cuti diproses : {$grandType}");
        $this->info(($dryRun ? 'Akan dibuat' : 'Saldo dibuat') . " : {$grandCreated}");
        $this->info("Dilewati (sudah ada): {$grandSkipped}");
        if ($dryRun) {
            $this->warn('DRY-RUN: tidak ada yang benar-benar disimpan. Jalankan tanpa --dry-run untuk eksekusi.');
        }

        return self::SUCCESS;
    }

    /**
     * Karyawan yang memenuhi aturan jenis cuti tertentu.
     * Aturan dibaca dari kolom DB: gender_rule, require_married, allowed_status.
     * Selalu: status = Active + masa kerja >= 1 tahun.
     */
    private function eligibleEmployees(Leavetypes $type, string $tenureCutoff)
    {
        $q = Employee::query()
            ->where('status', 'Active')
            ->whereDate('join_date', '<=', $tenureCutoff);

        // gender_rule: all / male / female
        $gender = strtolower(trim($type->gender_rule ?? 'all'));
        if ($gender === 'female') {
            $q->where('gender', 'Female');
        } elseif ($gender === 'male') {
            $q->where('gender', 'Male');
        }

        // require_married
        if ($type->require_married) {
            $q->where('marriage', 'Yes');
        }

        // allowed_status (csv, mis. "PKWT" atau "PKWT,PKWTT")
        $allowed = trim((string) ($type->allowed_status ?? ''));
        if ($allowed !== '') {
            $list = array_filter(array_map('trim', explode(',', $allowed)));
            if (!empty($list)) {
                $q->whereIn('status_employee', $list);
            }
        } else {
            // Tanpa allowed_status: tetap tolak DW & On Job Training
            // (konsisten dgn guard canHaveLeave di controller pengajuan).
            $q->whereNotIn('status_employee', ['DW', 'On Job Training']);
        }

        return $q->get(['id', 'employee_name', 'gender', 'marriage', 'status_employee', 'join_date']);
    }
}