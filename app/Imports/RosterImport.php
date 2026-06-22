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
//     public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}

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

//         // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));

//         // ── Kode non-shift → day_type ──
//         $dayTypeMap = [
//             'OFF'             => 'Off',
//             'PH'              => 'Public Holiday',
//             'LEAVE'           => 'Leave',
//             'TOIL OFF'        => 'TOIL Off',
//             'CUTI MELAHIRKAN' => 'Cuti Melahirkan',
//         ];

//         $start = Carbon::parse($this->startDate);

//         // ── Pre-load Public Holiday dalam rentang (untuk deteksi PH asli) ──
//         // Rentang: startDate s/d (startDate + jumlah kolom terlebar - 1)
//         $maxCols  = 0;
//         foreach ($rows as $row) {
//             $maxCols = max($maxCols, count($row));
//         }
//         // kolom tanggal mulai index 2; tanggal terakhir = start + (maxCols - 1 - 2)
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
//         $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);

//         // ── Nama store (untuk cek PH hangus di Minggu pada store statis) ──
//         $storeName = Stores::find($this->storeId)?->name;

//         // ── Loop baris data (setelah header) ──
//         foreach ($rows as $i => $row) {
//             if ($i <= $headerIndex) continue;
//             $pengenal = trim((string)($row[0] ?? ''));
//             if ($pengenal === '') continue;

            
//             $employee = Employee::where('employee_pengenal', $pengenal)
//                 ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
//                 ->whereIn('status', ['Active', 'On Leave', 'Pending']) // ← samakan dengan export
//                 ->first();

//             if (!$employee) {
//                 $this->errors[] = "Pengenal '{$pengenal}' tidak ditemukan di store ini.";
//                 continue;
//             }

            
// $pendingPhRemarks = []; // ['date' => remark]

// for ($col = 3; $col < count($row); $col++) {
//     $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//     $date = $start->copy()->addDays($col - 3)->toDateString();

//     if ($raw === '') continue;

//     if ($raw === 'SICK') {
//         $this->errors[] = "...";
//         continue;
//     }

//     // ── Kode non-shift ──
//     if (isset($dayTypeMap[$raw])) {
//         $notes = null;

//         if ($raw === 'PH') {
//             if ($this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)) {
//                 // PH asli → ambil remark dari phMap
//                 $notes = $this->getPublicHolidayRemark($phMap, $date);
//             } else {
//                 // PH pengganti (tanggal geser) → ambil remark dari pending
//                 $notes = array_shift($pendingPhRemarks) ?? null;
//             }
//         }

//         Roster::updateOrCreate(
//             ['employee_id' => $employee->id, 'date' => $date],
//             ['shift_id' => null, 'day_type' => $dayTypeMap[$raw], 'notes' => $notes]
//         );
//         $this->created++;
//         continue;
//     }

//     // ── Shift (Work) ──
//     $shift = $shiftMap->get($raw);
//     if (!$shift) {
//         $this->errors[] = "...";
//         continue;
//     }

//     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//     if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//         $isPH = false;
//     }

//     $phName = null;
//     if ($isPH) {
//         $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';

//         // Simpan ke pending agar bisa dipakai tanggal pengganti
//         $pendingPhRemarks[] = $phName; // ← tambah ini

//         RosterPhCarryover::firstOrCreate(
//             ['employee_id' => $employee->id, 'ph_date' => $date],
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

//     private function isStaticStore(?string $storeName): bool
//     {
//         $staticStoreNames = ['Head Office', 'Holding', 'Distribution Center'];
//         return in_array($storeName ?? '', $staticStoreNames);
//     }

//     private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
//     {
//         return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
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




//    public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}

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

//         // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));

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
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
//         $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);

//         // ── Nama store (untuk cek PH hangus di Minggu pada store statis) ──
//         $storeName = Stores::find($this->storeId)?->name;

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
//                 for ($col = 3; $col < count($row); $col++) {
//                     $date = $start->copy()->addDays($col - 3)->toDateString();
//                     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);

//                     if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                         $isPH = false;
//                     }

//                     if ($isPH) {
//                         $employeePhQuota[$employee->id]++;
//                     }
//                 }
//             }

//             // Hitung kode 'PH' pengganti yang dipakai di baris ini
//             // PH pengganti = kode 'PH' yang muncul di tanggal yang BUKAN PH asli untuk employee tsb
//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

//                 if ($raw !== 'PH') continue;

//                 $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isActualPH && !$this->isPhVoidedOnSunday($date, $storeName)) {
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

//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

//                 if ($raw === '') continue;

//                 if ($raw === 'SICK') {
//                     $this->errors[] = "...";
//                     continue;
//                 }

//                 // ── Kode non-shift ──
//                 if (isset($dayTypeMap[$raw])) {
//                     $notes = null;

//                     if ($raw === 'PH') {
//                         if ($this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)) {
//                             // PH asli → ambil remark dari phMap
//                             $notes = $this->getPublicHolidayRemark($phMap, $date);
//                         } else {
//                             // PH pengganti (tanggal geser) → ambil remark dari pending
//                             $notes = array_shift($pendingPhRemarks) ?? null;
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

//                 $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                     $isPH = false;
//                 }

//                 $phName = null;
//                 if ($isPH) {
//                     $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';

//                     // Simpan ke pending agar bisa dipakai tanggal pengganti
//                     $pendingPhRemarks[] = $phName;

//                     RosterPhCarryover::firstOrCreate(
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

//     private function isStaticStore(?string $storeName): bool
//     {
//         $staticStoreNames = ['Head Office', 'Holding', 'Distribution Center'];
//         return in_array($storeName ?? '', $staticStoreNames);
//     }

//     private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
//     {
//         return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
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
//  public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}

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

//         // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));

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
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
//         $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);

//         // ── Nama store (untuk cek PH hangus di Minggu pada store statis) ──
//         $storeName = Stores::find($this->storeId)?->name;

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
//                 for ($col = 3; $col < count($row); $col++) {
//                     $date = $start->copy()->addDays($col - 3)->toDateString();
//                     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);

//                     if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                         $isPH = false;
//                     }

//                     if ($isPH) {
//                         $employeePhQuota[$employee->id]++;
//                     }
//                 }
//             }

//             // Hitung kode 'PH' pengganti yang dipakai di baris ini
//             // PH pengganti = kode 'PH' yang muncul di tanggal yang BUKAN PH asli untuk employee tsb
//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

//                 if ($raw !== 'PH') continue;

//                 $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isActualPH && !$this->isPhVoidedOnSunday($date, $storeName)) {
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

//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

//                 if ($raw === '') continue;

//                 if ($raw === 'SICK') {
//                     $this->errors[] = "...";
//                     continue;
//                 }

//                 // ── Kode non-shift ──
//                 if (isset($dayTypeMap[$raw])) {
//                     $notes = null;

//                     if ($raw === 'PH') {
//                         $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)
//                             && !$this->isPhVoidedOnSunday($date, $storeName); // ← konsisten dengan logika shift

//                         if ($isActualPH) {
//                             // PH asli → ambil remark langsung dari phMap
//                             $notes = $this->getPublicHolidayRemark($phMap, $date);
//                         } else {
//                             // PH pengganti (tanggal geser) → ambil remark dari pending yang dikumpulkan saat kerja di PH
//                             $notes = array_shift($pendingPhRemarks) ?? null;
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

//                 $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                     $isPH = false;
//                 }

//                 $phName = null;
//                 if ($isPH) {
//                     $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';

//                     // Simpan ke pending agar bisa dipakai tanggal pengganti
//                     $pendingPhRemarks[] = $phName;

//                     RosterPhCarryover::firstOrCreate(
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

//     private function isStaticStore(?string $storeName): bool
//     {
//         $staticStoreNames = ['Head Office', 'Holding', 'Distribution Center'];
//         return in_array($storeName ?? '', $staticStoreNames);
//     }

//     private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
//     {
//         return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
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
//  public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}
 
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
 
//         // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));
 
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
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
//         $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);
 
//         // ── Nama store (untuk cek PH hangus di Minggu pada store statis) ──
//         $storeName = Stores::find($this->storeId)?->name;
 
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
//                 for ($col = 3; $col < count($row); $col++) {
//                     $date = $start->copy()->addDays($col - 3)->toDateString();
//                     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
 
//                     if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                         $isPH = false;
//                     }
 
//                     if ($isPH) {
//                         $employeePhQuota[$employee->id]++;
//                     }
//                 }
//             }
 
//             // Hitung kode 'PH' pengganti yang dipakai di baris ini
//             // PH pengganti = kode 'PH' yang muncul di tanggal yang BUKAN PH asli untuk employee tsb
//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();
 
//                 if ($raw !== 'PH') continue;
 
//                 $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isActualPH && !$this->isPhVoidedOnSunday($date, $storeName)) {
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
 
//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();
 
//                 if ($raw === '') {
//                     // Sel kosong tapi tanggal ini PH asli → simpan remark ke pending
//                     // agar bisa dipakai di tanggal PH pengganti berikutnya
//                     // DW tidak berhak PH → skip
//                     $isEmptyButPH = $employee->status_employee !== 'DW'
//                         && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)
//                         && !$this->isPhVoidedOnSunday($date, $storeName);
 
//                     if ($isEmptyButPH) {
//                         $emptyPhRemark      = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                         $pendingPhRemarks[] = $emptyPhRemark;
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
//                         $isVoided   = $isActualPH && $this->isPhVoidedOnSunday($date, $storeName);
 
//                         if ($isActualPH && !$isVoided) {
//                             // PH asli tidak hangus → ambil remark langsung dari phMap
//                             $notes = $this->getPublicHolidayRemark($phMap, $date);
 
//                             // Simpan juga ke pending, karena PH asli yang kodenya 'PH' di Excel
//                             // (bukan kerja) tetap bisa punya tanggal pengganti di baris berikutnya
//                             // jika ada PH lain yang hangus (void Sunday) berturutan
//                             $pendingPhRemarks[] = $notes ?? 'Public Holiday';
 
//                         } elseif ($isVoided) {
//                             // PH hangus (Minggu di static store) → simpan ke pending agar bisa
//                             // dipakai tanggal PH pengganti berikutnya
//                             $voidedRemark       = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                             $pendingPhRemarks[] = $voidedRemark;
 
//                             // Tanggal void ini sendiri tetap masuk sebagai PH biasa tanpa remark khusus
//                             $notes = null;
 
//                         } else {
//                             // PH pengganti (tanggal geser, bukan PH di kalender) → ambil dari pending
//                             $notes = array_shift($pendingPhRemarks) ?? null;
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
//                 if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                     $isPH = false;
//                 }
 
//                 $phName = null;
//                 if ($isPH) {
//                     $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
 
//                     // Simpan ke pending agar bisa dipakai tanggal pengganti
//                     $pendingPhRemarks[] = $phName;
 
//                     RosterPhCarryover::firstOrCreate(
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
 
//     private function isStaticStore(?string $storeName): bool
//     {
//         $staticStoreNames = ['Head Office', 'Holding', 'Distribution Center'];
//         return in_array($storeName ?? '', $staticStoreNames);
//     }
 
//     private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
//     {
//         return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
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
//   public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}

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

//         // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));

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
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
//         $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);

//         // ── Nama store (untuk cek PH hangus di Minggu pada store statis) ──
//         $storeName = Stores::find($this->storeId)?->name;

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
//                 for ($col = 3; $col < count($row); $col++) {
//                     $date = $start->copy()->addDays($col - 3)->toDateString();
//                     $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);

//                     if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                         $isPH = false;
//                     }

//                     if ($isPH) {
//                         $employeePhQuota[$employee->id]++;
//                     }
//                 }
//             }

//             // Hitung kode 'PH' pengganti yang dipakai di baris ini
//             // PH pengganti = kode 'PH' yang muncul di tanggal yang BUKAN PH asli untuk employee tsb
//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

//                 if ($raw !== 'PH') continue;

//                 $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
//                 if ($isActualPH && !$this->isPhVoidedOnSunday($date, $storeName)) {
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

//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

//                 if ($raw === '') {
//                     // Sel kosong tapi tanggal ini PH asli → simpan remark ke pending
//                     // agar bisa dipakai di tanggal PH pengganti berikutnya
//                     // DW tidak berhak PH → skip
//                     $isEmptyButPH = $employee->status_employee !== 'DW'
//                         && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)
//                         && !$this->isPhVoidedOnSunday($date, $storeName);

//                     if ($isEmptyButPH) {
//                         $emptyPhRemark      = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                         $pendingPhRemarks[] = $emptyPhRemark;
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
//                         $isVoided   = $isActualPH && $this->isPhVoidedOnSunday($date, $storeName);

//                         if ($isActualPH && !$isVoided) {
//                             // PH asli tidak hangus → ambil remark langsung dari phMap
//                             $notes = $this->getPublicHolidayRemark($phMap, $date);

//                             // Simpan juga ke pending, karena PH asli yang kodenya 'PH' di Excel
//                             // (bukan kerja) tetap bisa punya tanggal pengganti di baris berikutnya
//                             // jika ada PH lain yang hangus (void Sunday) berturutan
//                             $pendingPhRemarks[] = $notes ?? 'Public Holiday';

//                         } elseif ($isVoided) {
//                             // PH hangus (Minggu di static store) → remark tetap masuk ke notes Roster
//                             // dan simpan ke pending agar bisa dipakai tanggal PH pengganti berikutnya
//                             $voidedRemark       = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                             $notes              = $voidedRemark; // ← tetap tampil di notes
//                             $pendingPhRemarks[] = $voidedRemark; // ← sekaligus untuk tanggal pengganti

//                         } else {
//                             // PH pengganti (tanggal geser, bukan PH di kalender) → ambil dari pending
//                             $notes = array_shift($pendingPhRemarks) ?? null;
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
//                 if ($isPH && $this->isPhVoidedOnSunday($date, $storeName)) {
//                     $isPH = false;
//                 }

//                 $phName = null;
//                 if ($isPH) {
//                     $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';

//                     // Simpan ke pending agar bisa dipakai tanggal pengganti
//                     $pendingPhRemarks[] = $phName;

//                     RosterPhCarryover::firstOrCreate(
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

//     private function isStaticStore(?string $storeName): bool
//     {
//         $staticStoreNames = ['Head Office', 'Holding', 'Distribution Center'];
//         return in_array($storeName ?? '', $staticStoreNames);
//     }

//     private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
//     {
//         return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
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
//  public int $created = 0;
//     public array $errors = [];
//     public function __construct(
//         private string $storeId,
//         private string $startDate
//     ) {}

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

//         // ── Pre-load shift store ini (NAMA SHIFT uppercase → object) ──
//         $shiftMap = Shifts::where('store_id', $this->storeId)
//             ->get()
//             ->keyBy(fn($s) => strtoupper(trim($s->shift_name)));

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
//         $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
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
//                 for ($col = 3; $col < count($row); $col++) {
//                     $date = $start->copy()->addDays($col - 3)->toDateString();
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
//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

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

//             for ($col = 3; $col < count($row); $col++) {
//                 $raw  = strtoupper(trim((string)($row[$col] ?? '')));
//                 $date = $start->copy()->addDays($col - 3)->toDateString();

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
//                             // PH asli tidak hangus → ambil remark langsung dari phMap
//                             $notes = $this->getPublicHolidayRemark($phMap, $date);

//                             // Simpan juga ke pending, karena PH asli yang kodenya 'PH' di Excel
//                             // (bukan kerja) tetap bisa punya tanggal pengganti di baris berikutnya
//                             // jika ada PH lain yang hangus (void Sunday) berturutan
//                             $pendingPhRemarks[] = $notes ?? 'Public Holiday';

//                         } elseif ($isVoided) {
//                             // PH hangus di static store (Minggu) → remark tetap masuk ke notes Roster
//                             // tapi TIDAK disimpan ke pending karena tidak bisa ditukar
//                             $voidedRemark = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
//                             $notes        = $voidedRemark; // ← tetap tampil di notes Roster

//                         } else {
//                             // PH pengganti (tanggal geser, bukan PH di kalender) → ambil dari pending
//                             $notes = array_shift($pendingPhRemarks) ?? null;
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

//                     RosterPhCarryover::firstOrCreate(
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
 
        // ── Pre-load Public Holiday dalam rentang ──
        $maxCols = 0;
        foreach ($rows as $row) {
            $maxCols = max($maxCols, count($row));
        }
        $rangeEnd = $start->copy()->addDays(max(0, $maxCols - 1 - 3))->toDateString();
        $phMap    = $this->getPublicHolidaysMap($this->startDate, $rangeEnd);
 
        // ── PASS 1: Hitung PH asli per employee di rentang ini ──
        // Digunakan untuk validasi batas PH pengganti yang boleh dipakai
        $employeePhQuota      = []; // [employee_id => int] jumlah PH asli yang diperoleh
        $employeePhUsed       = []; // [employee_id => int] jumlah kode 'PH' pengganti yang dipakai
        $employeeNames        = []; // [employee_id => string] untuk pesan error
        $employeeStatusType   = []; // [employee_id => string] status_employee (PKWT/DW/OJT)
 
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;
            $pengenal = trim((string)($row[0] ?? ''));
            if ($pengenal === '') continue;
 
            $employee = Employee::where('employee_pengenal', $pengenal)
                ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
                ->whereIn('status', ['Active', 'On Leave', 'Pending'])
                ->first();
 
            if (!$employee) continue; // akan di-handle di pass 2
 
            $employeeNames[$employee->id]      = $row[1] ?? $pengenal; // kolom B = nama
            $employeeStatusType[$employee->id] = $employee->status_employee;
            $employeePhQuota[$employee->id]    = 0;
            $employeePhUsed[$employee->id]     = 0;
 
            // DW tidak berhak dapat PH → quota tetap 0, skip hitung dari phMap
            if ($employee->status_employee !== 'DW') {
                // Hitung PH asli: berapa tanggal di rentang ini yang PH berlaku bagi employee ini
                for ($col = 3; $col < count($row); $col++) {
                    $date = $start->copy()->addDays($col - 3)->toDateString();
                    $isPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
 
                    if ($isPH && $this->isPhVoidedOnSunday($date)) {
                        $isPH = false;
                    }
 
                    if ($isPH) {
                        $employeePhQuota[$employee->id]++;
                    }
                }
            }
 
            // Hitung kode 'PH' pengganti yang dipakai di baris ini
            // PH pengganti = kode 'PH' yang muncul di tanggal yang BUKAN PH asli untuk employee tsb
            for ($col = 3; $col < count($row); $col++) {
                $raw  = strtoupper(trim((string)($row[$col] ?? '')));
                $date = $start->copy()->addDays($col - 3)->toDateString();
 
                if ($raw !== 'PH') continue;
 
                $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
                if ($isActualPH && !$this->isPhVoidedOnSunday($date)) {
                    // Ini PH asli, bukan pengganti → tidak mengurangi kuota
                    continue;
                }
 
                // Ini PH pengganti (tanggal geser)
                $employeePhUsed[$employee->id]++;
            }
        }
 
        // ── PASS 1b: Validasi kuota PH sebelum melakukan perubahan apapun ──
        $quotaErrors = [];
        foreach ($employeePhUsed as $empId => $usedCount) {
            $quota = $employeePhQuota[$empId] ?? 0;
            if ($usedCount > $quota) {
                $name = $employeeNames[$empId] ?? $empId;
 
                $isDW = ($employeeStatusType[$empId] ?? '') === 'DW';
 
                if ($isDW) {
                    $quotaErrors[] = "Karyawan '{$name}' berstatus DW dan tidak berhak mendapatkan PH, " .
                                     "namun terdapat {$usedCount} kode PH di jadwalnya.";
                } else {
                    $quotaErrors[] = "Karyawan '{$name}' menggunakan {$usedCount} PH pengganti, " .
                                     "padahal hanya memiliki {$quota} jatah PH di periode ini.";
                }
            }
        }
 
        if (!empty($quotaErrors)) {
            foreach ($quotaErrors as $err) {
                $this->errors[] = $err;
            }
            // Hentikan import seluruhnya agar tidak ada data setengah masuk
            return;
        }
 
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
 
            $pendingPhRemarks = []; // ['date' => remark]
 
            for ($col = 3; $col < count($row); $col++) {
                $raw  = strtoupper(trim((string)($row[$col] ?? '')));
                $date = $start->copy()->addDays($col - 3)->toDateString();
 
                if ($raw === '') {
                    // Sel kosong tapi tanggal ini PH asli → simpan remark ke pending
                    // agar bisa dipakai di tanggal PH pengganti berikutnya
                    // DW tidak berhak PH → skip
                    $isEmptyButPH = $employee->status_employee !== 'DW'
                        && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion)
                        && !$this->isPhVoidedOnSunday($date);
 
                    if ($isEmptyButPH) {
                        $emptyPhRemark      = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
                        $pendingPhRemarks[] = $emptyPhRemark;
                    }
 
                    continue;
                }
 
                if ($raw === 'SICK') {
                    $this->errors[] = "...";
                    continue;
                }
 
                // ── Kode non-shift ──
                if (isset($dayTypeMap[$raw])) {
                    $notes = null;
 
                    if ($raw === 'PH') {
                        $isActualPH = $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
                        $isVoided   = $isActualPH && $this->isPhVoidedOnSunday($date);
 
                        if ($isActualPH && !$isVoided) {
                            // PH asli tidak hangus, employee tidak kerja → nikmati PH langsung
                            // Ambil remark untuk notes Roster
                            $notes = $this->getPublicHolidayRemark($phMap, $date);
 
                            // Batalkan carryover lama jika ada (misal dari import sebelumnya
                            // employee kerja di tanggal ini, sekarang diubah jadi PH)
                            RosterPhCarryover::where('employee_id', $employee->id)
                                ->where('ph_date', $date)
                                ->where('status', 'available')
                                ->update(['status' => 'cancelled']);
 
                        } elseif ($isVoided) {
                            // PH hangus di static store (Minggu) → remark tetap masuk ke notes Roster
                            // tapi TIDAK disimpan ke pending karena tidak bisa ditukar
                            $voidedRemark = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
                            $notes        = $voidedRemark; // ← tetap tampil di notes Roster
 
                        } else {
                            // PH pengganti (tanggal geser, bukan PH di kalender) → ambil dari pending
                            $notes = array_shift($pendingPhRemarks) ?? null;
 
                            // Tandai carryover yang matching sebagai 'used'
                            if ($notes) {
                                $carryover = RosterPhCarryover::where('employee_id', $employee->id)
                                    ->where('status', 'available')
                                    ->where('ph_name', $notes)
                                    ->whereDate('expired_at', '>=', $date)
                                    ->orderBy('ph_date')
                                    ->first();
 
                                if ($carryover) {
                                    $carryover->update([
                                        'status'    => 'used',
                                        'used_date' => $date,
                                    ]);
                                }
                            }
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
 
                $isPH = $employee->status_employee !== 'DW'
                    && $this->isPublicHolidayForEmployee($phMap, $date, $employee->religion);
                if ($isPH && $this->isPhVoidedOnSunday($date)) {
                    $isPH = false;
                }
 
                $phName = null;
                if ($isPH) {
                    $phName = $this->getPublicHolidayRemark($phMap, $date) ?? 'Public Holiday';
 
                    // Simpan ke pending agar bisa dipakai tanggal pengganti
                    $pendingPhRemarks[] = $phName;
 
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
 
    private function isStaticStore(): bool
    {
        $staticStoreIds = [
            '019623ad-de58-7368-8873-e3cbff2b0aff',
            '019a230d-6146-7001-848d-046ccdbdf163',
            '019963a7-cdb8-7002-b10b-163645c199d0',
        ];
        return in_array($this->storeId, $staticStoreIds);
    }
 
    private function isPhVoidedOnSunday(string $date): bool
    {
        return Carbon::parse($date)->isSunday() && $this->isStaticStore();
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
