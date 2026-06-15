<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Shifts;
use App\Models\Roster;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class RosterImport implements ToArray, WithCalculatedFormulas
{
    public int $created = 0;
    public array $errors = [];

    public function __construct(
        private string $storeId,
        private string $startDate
    ) {}

    public function array(array $rows): void
    {
        // ── Cari baris HEADER (kolom A mengandung 'pengenal') ──
        $headerIndex = null;
        foreach ($rows as $i => $row) {
            $colA = strtolower(trim((string)($row[0] ?? '')));
            if (str_contains($colA, 'pengenal')) {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            $this->errors[] = "Tidak menemukan baris header (kolom pertama harus 'employee_pengenal').";
            return;
        }

        // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
        $shiftMap = Shifts::where('store_id', $this->storeId)
            ->get()
            ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));

        // ── Kode non-shift → day_type ──
        $dayTypeMap = [
            'OFF'             => 'Off',
            'PH'              => 'Public Holiday',
            'LEAVE'           => 'Leave',
            'TOIL OFF'        => 'TOIL Off',
            'CUTI MELAHIRKAN' => 'Cuti Melahirkan',
        ];

        $start = Carbon::parse($this->startDate);

        // ── Loop baris data (setelah header) ──
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;
            $pengenal = trim((string)($row[0] ?? ''));
            if ($pengenal === '') continue;

            $employee = Employee::where('employee_pengenal', $pengenal)
                ->where('store_id', $this->storeId)
                ->first();

            if (!$employee) {
                $this->errors[] = "Pengenal '{$pengenal}' tidak ditemukan di store ini.";
                continue;
            }

            // ── Loop kolom shift (index 2 = kolom C = tanggal mulai) ──
            for ($col = 2; $col < count($row); $col++) {
                $raw = strtoupper(trim((string)($row[$col] ?? '')));
                if ($raw === '') continue;

                $date = $start->copy()->addDays($col - 2)->toDateString();

                // Sick ditolak (butuh bukti)
                if ($raw === 'SICK') {
                    $this->errors[] = "{$employee->employee_name} ({$date}): Sick tidak bisa via import (butuh bukti).";
                    continue;
                }

                // Kode non-shift → day_type
                if (isset($dayTypeMap[$raw])) {
                    Roster::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $date],
                        ['shift_id' => null, 'day_type' => $dayTypeMap[$raw], 'notes' => null]
                    );
                    $this->created++;
                    continue;
                }

                // Selain itu → shift (Work)
                $shift = $shiftMap->get($raw);
                if (!$shift) {
                    $this->errors[] = "{$employee->employee_name} ({$date}): shift '{$raw}' tidak ada di store ini.";
                    continue;
                }

                Roster::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date],
                    ['shift_id' => $shift->id, 'day_type' => 'Work', 'notes' => null]
                );
                $this->created++;
            }
        }
    }
}