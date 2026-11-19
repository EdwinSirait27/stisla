<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Shifts;
use App\Models\Roster;
use App\Models\Stores;
use App\Models\PublicHoliday;
use App\Models\RosterPHCarryover;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class RosterImport implements ToArray, WithCalculatedFormulas, WithMultipleSheets
// {
 
//  public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}
 
//     // Hanya baca sheet pertama (index 0 = Template Roster)
//     // Sheet Tutorial dan Shift diabaikan
//     public function sheets(): array
//     {
//         return [0 => $this];
//     }
 
//     public function array(array $rows): void
//     {
//         // ── Cari baris HEADER (kolom A mengandung 'pengenal') ──
//         $headerIndex = null;
//         foreach ($rows as $i => $row) {
//             $colA = strtolower(trim((string)($row[0] ?? '')));
//             if (str_contains($colA, 'pengenal')) {
//                 $headerIndex = $i;
//                 break;
//             }
//         }
 
//         if ($headerIndex === null) {
//             $this->errors[] = "Tidak menemukan baris header (kolom pertama harus 'employee_pengenal').";
//             return;
//         }
 
//         // ── Pre-load shift store ini (CODE uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->code)));
 
//         // ── Kode non-shift → day_type ──
//         $dayTypeMap = [
//             'OFF'             => 'Off',
//             'PH'              => 'Public Holiday',
//             'LEAVE'           => 'Leave',
//             'TOIL OFF'        => 'TOIL Off',
//             'CUTI MELAHIRKAN' => 'Cuti Melahirkan',
//         ];
 
//         $start = Carbon::parse($this->startDate);
 
//         // ── Pre-load Public Holiday dalam rentang ──
//         $maxCols = 0;
//         foreach ($rows as $row) {
//             $maxCols = max($maxCols, count($row));
//         }
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 4))->toDateString();
//         $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);
 
//         // ── PASS 1: Hitung PH asli per employee di rentang ini ──
//         // Digunakan untuk validasi batas PH pengganti yang boleh dipakai
//         $employeePhQuota      = []; // [employee_id => int] jumlah PH asli yang diperoleh
//         $employeePhUsed       = []; // [employee_id => int] jumlah kode 'PH' pengganti yang dipakai
//         $employeeNames        = []; // [employee_id => string] untuk pesan error
//         $employeeStatusType   = []; // [employee_id => string] status_employee (PKWT/DW/OJT)
 
//         foreach ($rows as $i => $row) {
//             if ($i <= $headerIndex) continue;
//             $pengenal = trim((string)($row[0] ?? ''));
//             if ($pengenal === '') continue;
 
//             $employee = Employee::where('employee_pengenal', $pengenal)
//                 ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
//                 ->whereIn('status', ['Active', 'On Leave', 'Pending'])
//                 ->first();
 
//             if (!$employee) continue; // akan di-handle di pass 2
 
//             $employeeNames[$employee->id]      = $row[1] ?? $pengenal; // kolom B = nama
//             $employeeStatusType[$employee->id] = $employee->status_employee;
//             $employeePhQuota[$employee->id]    = 0;
//             $employeePhUsed[$employee->id]     = 0;
 
//             // DW tidak berhak dapat PH → quota tetap 0, skip hitung dari phMap
//             if ($employee->status_employee !== 'DW') {
//                 // Hitung PH asli: berapa tanggal di rentang ini yang PH berlaku bagi employee ini
//                 for ($col = 4; $col < count($row); $col++) {
//                     $date = $start->copy()->addDays($col - 4)->toDateString();
//                     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
 
//                     if ($isPH && $this->isPhVoidedOnSunday($date)) {
//                         $isPH = false;
//                     }
 
//                     if ($isPH) {
//                         $employeePhQuota[$employee->id]++;
//                     }
//                 }
//             }
 
//             // Hitung kode 'PH' pengganti yang dipakai di baris ini
//             // PH pengganti = kode 'PH' yang muncul di tanggal yang BUKAN PH asli untuk employee tsb
//             for ($col = 4; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 4)->toDateString();
 
//                 if ($raw !== 'PH') continue;
 
//                 $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isActualPH && !$this->isPhVoidedOnSunday($date)) {
//                     // Ini PH asli, bukan pengganti → tidak mengurangi kuota
//                     continue;
//                 }
 
//                 // Ini PH pengganti (tanggal geser)
//                 $employeePhUsed[$employee->id]++;
//             }
//         }
 
//         // ── PASS 1b: Validasi kuota PH sebelum melakukan perubahan apapun ──
//         $quotaErrors = [];
//         foreach ($employeePhUsed as $empId => $usedCount) {
//             $quota = $employeePhQuota[$empId] ?? 0;
//             if ($usedCount > $quota) {
//                 $name = $employeeNames[$empId] ?? $empId;
 
//                 $isDW = ($employeeStatusType[$empId] ?? '') === 'DW';
 
//                 if ($isDW) {
//                     $quotaErrors[] = "Karyawan '{$name}' berstatus DW dan tidak berhak mendapatkan PH, " .
//                                      "namun terdapat {$usedCount} kode PH di jadwalnya.";
//                 } else {
//                     $quotaErrors[] = "Karyawan '{$name}' menggunakan {$usedCount} PH pengganti, " .
//                                      "padahal hanya memiliki {$quota} jatah PH di periode ini.";
//                 }
//             }
//         }
 
//         if (!empty($quotaErrors)) {
//             foreach ($quotaErrors as $err) {
//                 $this->errors[] = $err;
//             }
//             // Hentikan import seluruhnya agar tidak ada data setengah masuk
//             return;
//         }
 
//         // ── PASS 2: Proses import setelah validasi lolos ──
//         foreach ($rows as $i => $row) {
//             if ($i <= $headerIndex) continue;
//             $pengenal = trim((string)($row[0] ?? ''));
//             if ($pengenal === '') continue;
 
//             $employee = Employee::where('employee_pengenal', $pengenal)
//                 ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
//                 ->whereIn('status', ['Active', 'On Leave', 'Pending'])
//                 ->first();
 
//             if (!$employee) {
//                 $this->errors[] = "Pengenal '{$pengenal}' tidak ditemukan di store ini.";
//                 continue;
//             }
 
//             $pendingPhRemarks = []; // ['date' => remark]
 
//             for ($col = 4; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 4)->toDateString();
 
//                 if ($raw === '') {
//                     // Sel kosong tapi tanggal ini PH asli → simpan remark ke pending
//                     // agar bisa dipakai di tanggal PH pengganti berikutnya
//                     // DW tidak berhak PH → skip
//                     $isEmptyButPH = $employee->status_employee !== 'DW'
//                         && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)
//                         && !$this->isPhVoidedOnSunday($date);
 
//                     if ($isEmptyButPH) {
//                         $emptyPhRemark      = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                         $pendingPhRemarks[] = $emptyPhRemark;
 
//                         // PH dinikmati langsung (sel kosong = tidak kerja) → cancel carryover lama
//                         RosterPHCarryover::where('employee_id', $employee->id)
//                             ->where('ph_date', $date)
//                             ->where('status', 'available')
//                             ->update(['status' => 'cancelled']);
//                     }
 
//                     continue;
//                 }
 
//                 if ($raw === 'SICK') {
//                     $this->errors[] = "...";
//                     continue;
//                 }
 
//                 // ── Kode non-shift ──
//                 if (isset($dayTypeMap[$raw])) {
//                     $notes = null;
 
//                     if ($raw === 'PH') {
//                         $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                         $isVoided   = $isActualPH && $this->isPhVoidedOnSunday($date);
 
//                         if ($isActualPH && !$isVoided) {
//                             // PH asli tidak hangus, employee tidak kerja → nikmati PH langsung
//                             // Ambil remark untuk notes Roster
//                             $notes = $this->getPublicHolidayRemark($phMap, $date);
 
//                             // Batalkan carryover lama jika ada (misal dari import sebelumnya
//                             // employee kerja di tanggal ini, sekarang diubah jadi PH)
//                             RosterPHCarryover::where('employee_id', $employee->id)
//                                 ->where('ph_date', $date)
//                                 ->where('status', 'available')
//                                 ->update(['status' => 'cancelled']);
 
//                         } elseif ($isVoided) {
//                             // PH hangus di static store (Minggu) → remark tetap masuk ke notes Roster
//                             // tapi TIDAK disimpan ke pending karena tidak bisa ditukar
//                             $voidedRemark = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                             $notes        = $voidedRemark; // ← tetap tampil di notes Roster
 
//                         } else {
//                             // PH pengganti (tanggal geser, bukan PH di kalender) → ambil dari pending
//                             $notes = array_shift($pendingPhRemarks) ?? null;
 
//                             // Tandai carryover yang matching sebagai 'used'
//                             if ($notes) {
//                                 $carryover = RosterPHCarryover::where('employee_id', $employee->id)
//                                     ->where('status', 'available')
//                                     ->where('ph_name', $notes)
//                                     ->whereDate('expired_at', '>=', $date)
//                                     ->orderBy('ph_date')
//                                     ->first();
 
//                                 if ($carryover) {
//                                     $carryover->update([
//                                         'status'    => 'used',
//                                         'used_date' => $date,
//                                     ]);
//                                 }
//                             }
//                         }
//                     }
 
//                     Roster::updateOrCreate(
//                         ['employee_id' => $employee->id, 'date' => $date],
//                         ['shift_id' => null, 'day_type' => $dayTypeMap[$raw], 'notes' => $notes]
//                     );
//                     $this->created++;
//                     continue;
//                 }
 
//                 // ── Shift (Work) ──
//                 $shift = $shiftMap->get($raw);
//                 if (!$shift) {
//                     $this->errors[] = "...";
//                     continue;
//                 }
 
//                 $isPH = $employee->status_employee !== 'DW'
//                     && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isPH && $this->isPhVoidedOnSunday($date)) {
//                     $isPH = false;
//                 }
 
//                 $phName = null;
//                 if ($isPH) {
//                     $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
 
//                     // Simpan ke pending agar bisa dipakai tanggal pengganti
//                     $pendingPhRemarks[] = $phName;
 
//                     RosterPHCarryover::firstOrCreate(
//                         ['employee_id' => $employee->id, 'ph_date' => $date],
//                         [
//                             'ph_name'    => $phName,
//                             'expired_at' => $this->phCarryoverExpiry($date)->toDateString(),
//                             'status'     => 'available',
//                         ]
//                     );
//                 }
 
//                 Roster::updateOrCreate(
//                     ['employee_id' => $employee->id, 'date' => $date],
//                     ['shift_id' => $shift->id, 'day_type' => 'Work', 'notes' => $phName]
//                 );
//                 $this->created++;
//             }
//         }
//     }
 
//     // ═════════════════════════════════════════════════════════════
//     //  HELPER PH (disalin dari RosterController agar konsisten)
//     // ═════════════════════════════════════════════════════════════
 
//     private function getPublicHolidaysMap(string $startDate, string $endDate): array
//     {
//         $holidays = PublicHoliday::whereBetween('date', [$startDate, $endDate])->get();
 
//         $map = [];
//         foreach ($holidays as $ph) {
//             $dateStr = Carbon::parse($ph->date)->toDateString();
//             $map[$dateStr][] = [
//                 'type'   => $ph->type,   // 'Hindu' | 'Non Hindu' | 'All'
//                 'remark' => $ph->remark,
//             ];
//         }
//         return $map;
//     }
 
//     private function resolveRelevantPhTypes(?string $religion): array
//     {
//         return ($religion === 'Hindu')
//             ? ['Hindu', 'All']
//             : ['Non Hindu', 'All'];
//     }
 
//     private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
//     {
//         $dateStr = Carbon::parse($date)->toDateString();
//         if (!isset($phMap[$dateStr])) {
//             return false;
//         }
 
//         $relevantTypes = $this->resolveRelevantPhTypes($religion);
 
//         foreach ($phMap[$dateStr] as $ph) {
//             if (in_array($ph['type'], $relevantTypes)) {
//                 return true;
//             }
//         }
//         return false;
//     }
 
//     private function getPublicHolidayRemark(array $phMap, string $date): ?string
//     {
//         $dateStr = Carbon::parse($date)->toDateString();
//         if (!isset($phMap[$dateStr]) || empty($phMap[$dateStr])) {
//             return null;
//         }
//         return $phMap[$dateStr][0]['remark'] ?? null;
//     }
 
//     private function isStaticStore(): bool
//     {
//         $staticStoreIds = [
//             '019623ad-de58-7368-8873-e3cbff2b0aff',
//             '019a230d-6146-7001-848d-046ccdbdf163',
//             '019963a7-cdb8-7002-b10b-163645c199d0',
//         ];
//         return in_array($this->storeId, $staticStoreIds);
//     }
 
//     private function isPhVoidedOnSunday(string $date): bool
//     {
//         return Carbon::parse($date)->isSunday() && $this->isStaticStore();
//     }
 
//     private function periodEndFor(Carbon $date): Carbon
//     {
//         if ($date->day >= 26) {
//             return $date->copy()->addMonth()->day(25);
//         }
//         return $date->copy()->day(25);
//     }
 
//     private function phCarryoverExpiry(string $phDate): Carbon
//     {
//         $end = $this->periodEndFor(Carbon::parse($phDate));
//         return $end->copy()->addMonths(2);
//     }
// }
{
    public int $created = 0;
    public array $errors = [];
    public function __construct(
        private string $storeId,
        private string $startDate
    ) {}
 
    // Hanya baca sheet pertama (index 0 = Template Roster)
    // Sheet Tutorial dan Shift diabaikan
    public function sheets(): array
    {
        return [0 => $this];
    }
 
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
 
        // ── Pre-load shift store ini (CODE uppercase → object) ──
        $shiftMap = Shifts::where('store_id', $this->storeId)
            ->get()
            ->keyBy(fn($s) => strtoupper(trim($s->code)));
 
        // ── Kode non-shift → day_type ──
        $dayTypeMap = [
            'OFF'             => 'Off',
            'PH'              => 'Public Holiday',
            'LEAVE'           => 'Leave',
            'CUTI MELAHIRKAN' => 'Cuti Melahirkan',
        ];
 
        $start = Carbon::parse($this->startDate);
 
        // ── Pre-load Public Holiday dalam rentang ──
        $maxCols = 0;
        foreach ($rows as $row) {
            $maxCols = max($maxCols, count($row));
        }
        $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 4))->toDateString();
        $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);
 
        // ── PASS 1: DW tidak berhak PH pengganti → hentikan import sebelum ada perubahan ──
        // Kode PH pengganti karyawan lain divalidasi per tanggal di Pass 2 memakai saldo sah,
        // termasuk saldo dari periode lalu.
        $quotaErrors = [];
 
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;
            $pengenal = trim((string)($row[0] ?? ''));
            if ($pengenal === '') continue;
 
            $employee = Employee::where('employee_pengenal', $pengenal)
                ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
                ->whereIn('status', ['Active', 'On Leave', 'Pending'])
                ->first();
 
            if (!$employee || $employee->status_employee !== 'DW') continue;
 
            $usedCount = 0;
            for ($col = 4; $col < count($row); $col++) {
                $raw  = strtoupper(trim((string)($row[$col] ?? '')));
                $date = $start->copy()->addDays($col - 4)->toDateString();
 
                if ($raw !== 'PH') continue;
 
                $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
                if (!$isActualPH || $this->isPhVoidedOnSunday($date, $employee)) {
                    $usedCount++;
                }
            }
 
            if ($usedCount > 0) {
                $name = $row[1] ?? $pengenal;
                $quotaErrors[] = "Karyawan '{$name}' berstatus DW dan tidak berhak mendapatkan PH, " .
                                 "namun terdapat {$usedCount} kode PH di jadwalnya.";
            }
        }
 
        if (!empty($quotaErrors)) {
            foreach ($quotaErrors as $err) {
                $this->errors[] = $err;
            }
            // Hentikan import seluruhnya agar tidak ada data setengah masuk
            return;
        }
 
        // ── Tanggal TOIL Approved tidak boleh tertimpa import ──
        $toilApprovedKeys = ToilLeaveRequests::where('status', 'Approved')
            ->whereBetween('leave_date', [$this->startDate, $rangeEnd])
            ->get(['employee_id', 'leave_date'])
            ->mapWithKeys(fn($t) => [$t->employee_id . '_' . $t->leave_date->toDateString() => true]);

        // ── PASS 2: Proses import setelah validasi lolos ──
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;
            $pengenal = trim((string)($row[0] ?? ''));
            if ($pengenal === '') continue;
 
            $employee = Employee::where('employee_pengenal', $pengenal)
                ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
                ->whereIn('status', ['Active', 'On Leave', 'Pending'])
                ->first();
 
            if (!$employee) {
                $this->errors[] = "Pengenal '{$pengenal}' tidak ditemukan di store ini.";
                continue;
            }
 
            for ($col = 4; $col < count($row); $col++) {
                $raw  = strtoupper(trim((string)($row[$col] ?? '')));
                $date = $start->copy()->addDays($col - 4)->toDateString();
 
                if ($raw === '') {
                    // Sel kosong tapi tanggal ini PH asli → PH dinikmati langsung
                    // DW tidak berhak PH → skip
                    $isEmptyButPH = $employee->status_employee !== 'DW'
                        && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)
                        && !$this->isPhVoidedOnSunday($date, $employee);
 
                    if ($isEmptyButPH) {
                        // PH dinikmati langsung (sel kosong = tidak kerja) → cancel carryover lama,
                        // kecuali sudah terbukti masuk atau saldonya sudah dipakai
                        RosterPHCarryover::enjoyPh($employee->id, $date);
                    }
 
                    continue;
                }
 
                if ($raw === 'SICK') {
                    $this->errors[] = "...";
                    continue;
                }
 
                if ($toilApprovedKeys->has($employee->id . '_' . $date)) {
                    if ($raw !== 'TOIL OFF') {
                        $this->errors[] = "Pengenal '{$pengenal}' tanggal {$date} dilewati: ada TOIL Leave Approved. Cancel dulu via menu Approval.";
                    }
                    continue;
                }
 
                if ($raw === 'TOIL OFF') {
                    $this->errors[] = "Pengenal '{$pengenal}' tanggal {$date} dilewati: TOIL Off hanya bisa dibuat dari menu TOIL Approval.";
                    continue;
                }
 
                // DW & On Job Training tidak punya hak cuti (sama dengan aturan di RosterController)
                if (in_array($raw, ['LEAVE', 'CUTI MELAHIRKAN'], true)
                    && in_array(strtoupper($employee->status_employee ?? ''), ['DW', 'ON JOB TRAINING'], true)) {
                    $this->errors[] = "Pengenal '{$pengenal}' tanggal {$date} dilewati: status {$employee->status_employee} tidak bisa di-set {$raw}.";
                    continue;
                }
 
                if ($raw === 'PH' && strtoupper($employee->status_employee ?? '') === 'DW') {
                    $this->errors[] = "Pengenal '{$pengenal}' tanggal {$date} dilewati: status DW tidak mendapat Public Holiday.";
                    continue;
                }
 
                // ── Kode non-shift ──
                if (isset($dayTypeMap[$raw])) {
                    $notes   = null;
                    $dayType = $dayTypeMap[$raw];
 
                    // Hari pengganti yang tertimpa kode lain: saldonya kembali tersedia
                    if ($raw !== 'PH') {
                        RosterPHCarryover::refundUsedOn($employee->id, $date);
                    }
 
                    if ($raw === 'PH') {
                        $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
                        $isVoided   = $isActualPH && $this->isPhVoidedOnSunday($date, $employee);
 
                        if ($isActualPH && !$isVoided) {
                            // PH asli tidak hangus, employee tidak kerja → nikmati PH langsung
                            // Ambil remark untuk notes Roster
                            $notes = $this->getPublicHolidayRemark($phMap, $date, $employee->religion);
 
                            // Batalkan carryover lama jika ada (misal dari import sebelumnya
                            // employee kerja di tanggal ini, sekarang diubah jadi PH)
                            if (!RosterPHCarryover::enjoyPh($employee->id, $date)) {
                                $this->errors[] = "Pengenal '{$pengenal}' tanggal {$date} dilewati: karyawan sudah masuk di hari PH atau saldonya sudah dipakai.";
                                continue;
                            }
 
                        } elseif ($isVoided) {
                            // PH hangus di static store (Minggu) → hari itu Off,
                            // remark tetap masuk ke notes Roster
                            $notes   = $this->getPublicHolidayRemark($phMap, $date, $employee->religion) ?? 'Public Holiday';
                            $dayType = 'Off';
                            RosterPHCarryover::refundUsedOn($employee->id, $date);
 
                        } else {
                            // PH pengganti (tanggal geser, bukan PH di kalender):
                            // harus memakai saldo sah, yaitu terbukti masuk di hari PH.
                            // Tanggal yang sudah jadi hari pengganti tidak memakai saldo lagi.
                            $carryover = RosterPHCarryover::where('employee_id', $employee->id)
                                ->where('status', 'used')
                                ->whereDate('used_date', $date)
                                ->first();
 
                            if (!$carryover) {
                                $carryover = RosterPHCarryover::where('employee_id', $employee->id)
                                    ->usableOn($date)
                                    ->orderBy('ph_date')
                                    ->first();
 
                                if (!$carryover) {
                                    $this->errors[] = "Pengenal '{$pengenal}' tanggal {$date} dilewati: belum ada saldo PH yang sah. "
                                        . "Pastikan kehadiran di hari PH sudah tercatat di rekap.";
                                    continue;
                                }
 
                                $carryover->update([
                                    'status'    => 'used',
                                    'used_date' => $date,
                                ]);
                            }
 
                            $notes = $carryover->ph_name;
                        }
                    }
 
                    Roster::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $date],
                        ['shift_id' => null, 'day_type' => $dayType, 'notes' => $notes]
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
 
                // Hari pengganti yang tertimpa shift: saldonya kembali tersedia
                RosterPHCarryover::refundUsedOn($employee->id, $date);
 
                $isPH = $employee->status_employee !== 'DW'
                    && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
                if ($isPH && $this->isPhVoidedOnSunday($date, $employee)) {
                    $isPH = false;
                }
 
                $phName = null;
                if ($isPH) {
                    $phName = $this->getPublicHolidayRemark($phMap, $date, $employee->religion) ?? 'Public Holiday';
 
                    RosterPHCarryover::rememberWorkOnPh(
                        $employee->id,
                        $date,
                        $phName,
                        $this->phCarryoverExpiry($date)->toDateString()
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
 
    private function getPublicHolidayRemark(array $phMap, string $date, ?string $religion): ?string
    {
        $relevantTypes = $this->resolveRelevantPhTypes($religion);
 
        foreach ($phMap[Carbon::parse($date)->toDateString()] ?? [] as $ph) {
            if (in_array($ph['type'], $relevantTypes)) {
                return $ph['remark'];
            }
        }
 
        return null;
    }
 
    /** [employee_id => bool], supaya primary store tidak di-query ulang per tanggal */
    private array $staticStoreCache = [];

    /**
     * Ikut primary store karyawan (sama dengan RosterController & Auto Roster),
     * bukan store yang sedang di-import: karyawan bisa terdaftar di beberapa store.
     */
    private function isStaticStore(Employee $employee): bool
    {
        return $this->staticStoreCache[$employee->id] ??= in_array(
            $employee->primaryStore()->value('stores_tables.id'),
            PublicHoliday::STATIC_STORE_IDS,
            true
        );
    }

    private function isPhVoidedOnSunday(string $date, Employee $employee): bool
    {
        return Carbon::parse($date)->isSunday() && $this->isStaticStore($employee);
    }
 
    private function phCarryoverExpiry(string $phDate): Carbon
    {
        return RosterPHCarryover::expiryFor($phDate);
    }
}