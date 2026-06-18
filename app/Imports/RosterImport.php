<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Shifts;
use App\Models\Roster;
use App\Models\Stores;
use App\Models\PublicHoliday;
use App\Models\RosterPhCarryover;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Facades\Log;
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

        // ── Pre-load Public Holiday dalam rentang (untuk deteksi PH asli) ──
        // Rentang: startDate s/d (startDate + jumlah kolom terlebar - 1)
        $maxCols  = 0;
        foreach ($rows as $row) {
            $maxCols = max($maxCols, count($row));
        }
        // kolom tanggal mulai index 2; tanggal terakhir = start + (maxCols - 1 - 2)
        $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
        $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);

        // ── Nama store (untuk cek PH hangus di Minggu pada store statis) ──
        $storeName = Stores::find($this->storeId)?->name;

        // ── Loop baris data (setelah header) ──
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;
            $pengenal = trim((string)($row[0] ?? ''));
            if ($pengenal === '') continue;

            //         $employee = Employee::where('employee_pengenal', $pengenal)
            // ->where('store_id', $this->storeId)
            // ->whereIn('status', [
            //     'Active',
            //     'On Leave',
            //     'Pending'
            // ])
            // ->first();
            $employee = Employee::where('employee_pengenal', $pengenal)
                ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
                ->whereIn('status', ['Active', 'On Leave', 'Pending']) // ← samakan dengan export
                ->first();

            if (!$employee) {
                $this->errors[] = "Pengenal '{$pengenal}' tidak ditemukan di store ini.";
                continue;
            }

            // ── Loop kolom shift (index 2 = kolom C = tanggal mulai) ──
            // for ($col = 3; $col < count($row); $col++) {
            //     $raw = strtoupper(trim((string)($row[$col] ?? '')));
            //     if ($raw === '') continue;

            //     $date = $start->copy()->addDays($col - 3)->toDateString();
            //     // Log::info("col={$col} raw={$raw} date={$date}");
            //     Log::info(
            //         "col={$col} raw={$raw} date={$date} isPH=" .
            //             ($this->isPublicHolidayForEmployee($phMap, $date, $employee->religion) ? 'true' : 'false') .
            //             " phMapHasDate=" . (isset($phMap[$date]) ? 'true' : 'false')
            //     );

            //     // Sick ditolak (butuh bukti)
            //     if ($raw === 'SICK') {
            //         $this->errors[] = "{$employee->employee_name} ({$date}): Sick tidak bisa via import (butuh bukti).";
            //         continue;
            //     }

            //     // Kode non-shift → day_type
            //     if (isset($dayTypeMap[$raw])) {
            //         $notes = null;
            //         if ($raw === 'PH') {
            //             $notes = $this->getPublicHolidayRemark($phMap, $date);
            //         }

            //         Roster::updateOrCreate(
            //             ['employee_id' => $employee->id, 'date' => $date],
            //             ['shift_id' => null, 'day_type' => $dayTypeMap[$raw], 'notes' => $notes]
            //         );
            //         $this->created++;
            //         continue;
            //     }

            //     // Selain itu → shift (Work)
            //     $shift = $shiftMap->get($raw);
            //     if (!$shift) {
            //         $this->errors[] = "{$employee->employee_name} ({$date}): shift '{$raw}' tidak ada di store ini.";
            //         continue;
            //     }

            //     // ── PH TUKAR: kalau tanggal ini aslinya PH tapi diisi Work (shift) → SIMPAN saldo ──
            //     // (konsisten dengan RosterController::store(): isPH && day_type === 'Work')
            //     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
            //     if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
            //         $isPH = false; // PH hangus di Minggu untuk store statis
            //     }
            //     $phName = null;

            //     if ($isPH) {
            //         $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';

            //         // Anti-duplikat: 1 saldo per karyawan per tanggal asal
            //         RosterPhCarryover::firstOrCreate(
            //             [
            //                 'employee_id' => $employee->id,
            //                 'ph_date'     => $date,
            //             ],
            //             [
            //                 'ph_name'    => $phName,
            //                 'expired_at' => $this->phCarryoverExpiry($date)->toDateString(),
            //                 'status'     => 'available',
            //             ]
            //         );
            //     }

            //     Roster::updateOrCreate(
            //         ['employee_id' => $employee->id, 'date' => $date],
            //         ['shift_id' => $shift->id, 'day_type' => 'Work', 'notes' => $phName]
            //     );
            //     $this->created++;
            // }
            // Sebelum loop kolom, siapkan array penampung
$pendingPhRemarks = []; // ['date' => remark]

for ($col = 3; $col < count($row); $col++) {
    $raw  = strtoupper(trim((string)($row[$col] ?? '')));
    $date = $start->copy()->addDays($col - 3)->toDateString();

    if ($raw === '') continue;

    if ($raw === 'SICK') {
        $this->errors[] = "...";
        continue;
    }

    // ── Kode non-shift ──
    if (isset($dayTypeMap[$raw])) {
        $notes = null;

        if ($raw === 'PH') {
            if ($this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)) {
                // PH asli → ambil remark dari phMap
                $notes = $this->getPublicHolidayRemark($phMap, $date);
            } else {
                // PH pengganti (tanggal geser) → ambil remark dari pending
                $notes = array_shift($pendingPhRemarks) ?? null;
            }
        }

        Roster::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            ['shift_id' => null, 'day_type' => $dayTypeMap[$raw], 'notes' => $notes]
        );
        $this->created++;
        continue;
    }

    // ── Shift (Work) ──
    $shift = $shiftMap->get($raw);
    if (!$shift) {
        $this->errors[] = "...";
        continue;
    }

    $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
    if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
        $isPH = false;
    }

    $phName = null;
    if ($isPH) {
        $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';

        // Simpan ke pending agar bisa dipakai tanggal pengganti
        $pendingPhRemarks[] = $phName; // ← tambah ini

        RosterPhCarryover::firstOrCreate(
            ['employee_id' => $employee->id, 'ph_date' => $date],
            [
                'ph_name'    => $phName,
                'expired_at' => $this->phCarryoverExpiry($date)->toDateString(),
                'status'     => 'available',
            ]
        );
    }

    Roster::updateOrCreate(
        ['employee_id' => $employee->id, 'date' => $date],
        ['shift_id' => $shift->id, 'day_type' => 'Work', 'notes' => $phName]
    );
    $this->created++;
}
        }
    }

    // ═════════════════════════════════════════════════════════════
    //  HELPER PH (disalin dari RosterController agar konsisten)
    // ═════════════════════════════════════════════════════════════

    private function getPublicHolidaysMap(string $startDate, string $endDate): array
    {
        $holidays = PublicHoliday::whereBetween('date', [$startDate, $endDate])->get();

        $map = [];
        foreach ($holidays as $ph) {
            $dateStr = Carbon::parse($ph->date)->toDateString();
            $map[$dateStr][] = [
                'type'   => $ph->type,   // 'Hindu' | 'Non Hindu' | 'All'
                'remark' => $ph->remark,
            ];
        }
        return $map;
    }

    private function resolveRelevantPhTypes(?string $religion): array
    {
        return ($religion === 'Hindu')
            ? ['Hindu', 'All']
            : ['Non Hindu', 'All'];
    }

    private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
    {
        $dateStr = Carbon::parse($date)->toDateString();
        if (!isset($phMap[$dateStr])) {
            return false;
        }

        $relevantTypes = $this->resolveRelevantPhTypes($religion);

        foreach ($phMap[$dateStr] as $ph) {
            if (in_array($ph['type'], $relevantTypes)) {
                return true;
            }
        }
        return false;
    }

    private function getPublicHolidayRemark(array $phMap, string $date): ?string
    {
        $dateStr = Carbon::parse($date)->toDateString();
        if (!isset($phMap[$dateStr]) || empty($phMap[$dateStr])) {
            return null;
        }
        return $phMap[$dateStr][0]['remark'] ?? null;
    }

    private function isStaticStore(?string $storeName): bool
    {
        $staticStoreNames = ['Head Office', 'Holding', 'Distribution Center'];
        return in_array($storeName ?? '', $staticStoreNames);
    }

    private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
    {
        return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
    }

    private function periodEndFor(Carbon $date): Carbon
    {
        if ($date->day >= 26) {
            return $date->copy()->addMonth()->day(25);
        }
        return $date->copy()->day(25);
    }

    private function phCarryoverExpiry(string $phDate): Carbon
    {
        $end = $this->periodEndFor(Carbon::parse($phDate));
        return $end->copy()->addMonths(2);
    }
}
