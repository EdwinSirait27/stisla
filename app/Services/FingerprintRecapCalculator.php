<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\Roster;
use Carbon\Carbon;

// class FingerprintRecapCalculator
// {
//     private const TOLERANSI_TINGGI_STORES = [
//         'Head Office',
//         'Holding',
//         'Distribution Center',
//     ];
//     private const TOLERANSI_TINGGI_MENIT = 10;
//     private const TOLERANSI_NORMAL_MENIT = 5;

//     /**
//      * Hitung rekap (total hari kerja, total telat, remarks) untuk
//      * sekumpulan karyawan dalam satu periode.
//      *
//      * @param  \Illuminate\Support\Collection  $employees  Koleksi Employee (sudah eager-load store)
//      * @param  string  $startDate  Y-m-d
//      * @param  string  $endDate    Y-m-d
//      * @return \Illuminate\Support\Collection  baris rekap per karyawan
//      */
//     public function calculate($employees, string $startDate, string $endDate)
//     {
//         $employeeIds = $employees->pluck('id')->toArray();

//         // ── Recap dalam periode ──
//         $recaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
//             ->whereBetween('date', [$startDate, $endDate])
//             ->get()
//             ->groupBy('employee_id');

//         // ── Roster Work + Cuti + PH ──
//         $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
//             ->whereIn('employee_id', $employeeIds)
//             ->whereBetween('date', [$startDate, $endDate])
//             ->whereIn('day_type', ['Work', 'Leave', 'Cuti Melahirkan', 'Public Holiday'])
//             ->get()
//             ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

//         $rostersByEmployee = $rosters->groupBy('employee_id');

//         // ── Roster Public Holiday untuk Remarks ──
//         $publicHolidayRosters = Roster::whereIn('employee_id', $employeeIds)
//             ->whereBetween('date', [$startDate, $endDate])
//             ->where('day_type', 'Public Holiday')
//             ->get()
//             ->groupBy('employee_id');

//         // ── Build result ──
//         return $employees->map(function ($employee) use (
//             $recaps,
//             $rosters,
//             $rostersByEmployee,
//             $publicHolidayRosters,
//             $startDate,
//             $endDate
//         ) {
//             $employeeRecaps    = $recaps->get($employee->id, collect());
//             $employeeRosters   = $rostersByEmployee->get($employee->id, collect());
//             $employeePhRosters = $publicHolidayRosters->get($employee->id, collect());

//             $storeName = optional($employee->store)->name ?? '';
//             $statusEmp = strtoupper($employee->status_employee ?? '');
//             $toleransi = in_array($storeName, self::TOLERANSI_TINGGI_STORES)
//                 ? self::TOLERANSI_TINGGI_MENIT
//                 : self::TOLERANSI_NORMAL_MENIT;

//             $eligibleForPH   = !in_array($statusEmp, ['DW']);
//             $eligibleForCuti = !in_array($statusEmp, ['DW', 'On Job Training']);

//             $rosterAllDates    = $employeeRosters->pluck('date')
//                 ->map(fn($d) => Carbon::parse($d)->toDateString())
//                 ->toArray();
//             $rosterAllDatesSet = array_flip($rosterAllDates);

//             $cutiDates = $eligibleForCuti
//                 ? $employeeRosters
//                 ->whereIn('day_type', ['Leave', 'Cuti Melahirkan'])
//                 ->pluck('date')
//                 ->map(fn($d) => Carbon::parse($d)->toDateString())
//                 ->toArray()
//                 : [];
//             $cutiDatesSet = array_flip($cutiDates);

//             $rosterWorkOnly  = $employeeRosters->where('day_type', 'Work');
//             $rosterWorkDates = $rosterWorkOnly->pluck('date')
//                 ->map(fn($d) => Carbon::parse($d)->toDateString())
//                 ->toArray();

//             // ── Hitung telat ──
//             $telatDates = [];

//             foreach ($employeeRecaps as $recap) {
//                 $dateStr = Carbon::parse($recap->date)->toDateString();

//                 if (!$recap->time_in && !$recap->time_out) {
//                     continue;
//                 }
//                 if (isset($cutiDatesSet[$dateStr])) {
//                     continue;
//                 }

//                 $rosterKey = $employee->id . '_' . $dateStr;
//                 $roster    = $rosters->get($rosterKey);

//                 if (!$roster || $roster->day_type !== 'Work') continue;

//                 $scanTidakLengkap = (!$recap->time_in || !$recap->time_out);

//                 $masukTerlambat = false;
//                 if ($recap->time_in && $roster->shift) {
//                     $shiftStart = Carbon::parse($dateStr . ' ' . $roster->shift->start_time);
//                     $actualIn   = Carbon::parse($dateStr . ' ' . $recap->time_in);
//                     $batasMasuk = $shiftStart->copy()->addMinutes($toleransi);

//                     if ($actualIn->gt($batasMasuk)) {
//                         $masukTerlambat = true;
//                     }
//                 }

//                 if ($scanTidakLengkap || $masukTerlambat) {
//                     $telatDates[] = $dateStr;
//                 }
//             }

//             // ── Total Hari Kerja ──
//             $countedDates = $employeeRecaps
//                 ->where('is_counted', 1)
//                 ->pluck('date')
//                 ->map(fn($d) => Carbon::parse($d)->toDateString())
//                 ->filter(fn($dateStr) => isset($rosterAllDatesSet[$dateStr]))
//                 ->values()
//                 ->toArray();

//             $phDates = $eligibleForPH
//                 ? $employeePhRosters
//                 ->pluck('date')
//                 ->map(fn($d) => Carbon::parse($d)->toDateString())
//                 ->toArray()
//                 : [];
//             $allCountedDates = array_unique(array_merge($countedDates, $phDates));
//             $totalHariKerja  = count($allCountedDates);
//             $totalHariTelat  = count($telatDates);

//             // ── Tidak masuk ──
//             $tidakMasukDates = array_diff($rosterWorkDates, $countedDates, $cutiDates);

//             // ── Remarks: telat + tidak masuk ──
//             $remarksItems = collect(array_merge($telatDates, array_values($tidakMasukDates)))
//                 ->unique()
//                 ->map(fn($d) => [
//                     'date'    => $d,
//                     'display' => Carbon::parse($d)->format('d/m/Y'),
//                 ]);

//             // ── Remarks: Public Holiday ──
//             $phItems = $eligibleForPH
//                 ? $employeePhRosters->map(function ($phRoster) {
//                     $dateStr = Carbon::parse($phRoster->date)->toDateString();
//                     $remark  = $phRoster->notes ?: 'Public Holiday';
//                     return [
//                         'date'    => $dateStr,
//                         'display' => Carbon::parse($phRoster->date)->format('d/m/Y') . ' (' . $remark . ')',
//                     ];
//                 })
//                 : collect();

//             // ── Remarks: Cuti ──
//             $cutiItems = $eligibleForCuti
//                 ? $employeeRosters
//                 ->whereIn('day_type', ['Leave', 'Cuti Melahirkan'])
//                 ->map(function ($r) {
//                     return [
//                         'date'    => Carbon::parse($r->date)->toDateString(),
//                         'display' => Carbon::parse($r->date)->format('d/m/Y') . ' (' . $r->day_type . ')',
//                     ];
//                 })
//                 ->values()
//                 : collect();

//             // ── Combine remarks ──
//             $bermasalahDates = $remarksItems
//                 ->concat($phItems)
//                 ->concat($cutiItems)
//                 ->unique('date')
//                 ->sortBy('date')
//                 ->pluck('display')
//                 ->implode(', ');

//             return [
//                 'employee_id'      => $employee->id,
//                 'employee_name'    => $employee->employee_name ?? '-',
//                 'store_name'       => $storeName ?: '-',
//                 'total_hari_kerja' => $totalHariKerja,
//                 'total_hari_telat' => $totalHariTelat,
//                 'remarks'          => $bermasalahDates ?: '-',
//                 'period_start'     => $startDate,
//                 'period_end'       => $endDate,
//             ];
//         });
//     }
// }
class FingerprintRecapCalculator
{
    private const TOLERANSI_TINGGI_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];
    private const TOLERANSI_TINGGI_MENIT = 10;
    private const TOLERANSI_NORMAL_MENIT = 5;

    private const STATIC_STORE_IDS = [
        '019623ad-de58-7368-8873-e3cbff2b0aff',
        '019963a7-cdb8-7002-b10b-163645c199d0',
        '019a230d-6146-7001-848d-046ccdbdf163',
    ];

    public function calculate($employees, string $startDate, string $endDate)
    {
        $employeeIds = $employees->pluck('id')->toArray();

        // ── Recap dalam periode ──
        $recaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('employee_id');

        // ── Roster Work + Cuti + PH ──
        $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('day_type', ['Work', 'Leave', 'Cuti Melahirkan', 'Public Holiday'])
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        $rostersByEmployee = $rosters->groupBy('employee_id');

        // ── Roster Public Holiday untuk Remarks ──
        $publicHolidayRosters = Roster::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('day_type', 'Public Holiday')
            ->get()
            ->groupBy('employee_id');

        // ── Pre-load primary store id per employee (sekali saja, hindari N+1) ──
        $employeePrimaryStoreIds = Employee::with([
            'store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id'),
        ])
        ->select('id')
        ->whereIn('id', $employeeIds)
        ->get()
        ->mapWithKeys(fn($e) => [$e->id => $e->store->first()?->id]);

        // ── Build result ──
        return $employees->map(function ($employee) use (
            $recaps,
            $rosters,
            $rostersByEmployee,
            $publicHolidayRosters,
            $startDate,
            $endDate,
            $employeePrimaryStoreIds
        ) {
            $employeeRecaps    = $recaps->get($employee->id, collect());
            $employeeRosters   = $rostersByEmployee->get($employee->id, collect());
            $employeePhRosters = $publicHolidayRosters->get($employee->id, collect());

            $storeName = optional($employee->store)->name ?? '';
            $statusEmp = strtoupper($employee->status_employee ?? '');
            $toleransi = in_array($storeName, self::TOLERANSI_TINGGI_STORES)
                ? self::TOLERANSI_TINGGI_MENIT
                : self::TOLERANSI_NORMAL_MENIT;

            $eligibleForPH   = !in_array($statusEmp, ['DW']);
            $eligibleForCuti = !in_array($statusEmp, ['DW', 'ON JOB TRAINING']);

            // ── Cek apakah employee di static store (PH Minggu hangus) ──
            $primaryStoreId = $employeePrimaryStoreIds->get($employee->id);
            $isStaticStore  = in_array($primaryStoreId, self::STATIC_STORE_IDS);

            $rosterAllDates    = $employeeRosters->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();
            $rosterAllDatesSet = array_flip($rosterAllDates);

            $cutiDates = $eligibleForCuti
                ? $employeeRosters
                    ->whereIn('day_type', ['Leave', 'Cuti Melahirkan'])
                    ->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray()
                : [];
            $cutiDatesSet = array_flip($cutiDates);

            $rosterWorkOnly  = $employeeRosters->where('day_type', 'Work');
            $rosterWorkDates = $rosterWorkOnly->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            // ── Hitung telat ──
            $telatDates = [];

            foreach ($employeeRecaps as $recap) {
                $dateStr = Carbon::parse($recap->date)->toDateString();

                if (!$recap->time_in && !$recap->time_out) continue;
                if (isset($cutiDatesSet[$dateStr])) continue;

                $rosterKey = $employee->id . '_' . $dateStr;
                $roster    = $rosters->get($rosterKey);

                if (!$roster || $roster->day_type !== 'Work') continue;

                $scanTidakLengkap = (!$recap->time_in || !$recap->time_out);

                $masukTerlambat = false;
                if ($recap->time_in && $roster->shift) {
                    $shiftStart = Carbon::parse($dateStr . ' ' . $roster->shift->start_time);
                    $actualIn   = Carbon::parse($dateStr . ' ' . $recap->time_in);
                    $batasMasuk = $shiftStart->copy()->addMinutes($toleransi);

                    if ($actualIn->gt($batasMasuk)) {
                        $masukTerlambat = true;
                    }
                }

                if ($scanTidakLengkap || $masukTerlambat) {
                    $telatDates[] = $dateStr;
                }
            }

            // ── Total Hari Kerja ──
            $countedDates = $employeeRecaps
                ->where('is_counted', 1)
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->filter(fn($dateStr) => isset($rosterAllDatesSet[$dateStr]))
                ->values()
                ->toArray();

            // ← Fix: filter PH Minggu untuk static store
            $phDates = $eligibleForPH
                ? $employeePhRosters
                    ->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->filter(function ($dateStr) use ($isStaticStore) {
                        if ($isStaticStore && Carbon::parse($dateStr)->isSunday()) {
                            return false; // ← PH Minggu hangus untuk static store
                        }
                        return true;
                    })
                    ->toArray()
                : [];

            $allCountedDates = array_unique(array_merge($countedDates, $phDates));
            $totalHariKerja  = count($allCountedDates);
            $totalHariTelat  = count($telatDates);

            // ── Tidak masuk ──
            $tidakMasukDates = array_diff($rosterWorkDates, $countedDates, $cutiDates);

            // ── Remarks: telat + tidak masuk ──
            $remarksItems = collect(array_merge($telatDates, array_values($tidakMasukDates)))
                ->unique()
                ->map(fn($d) => [
                    'date'    => $d,
                    'display' => Carbon::parse($d)->format('d/m/Y'),
                ]);

            // ← Fix: filter PH Minggu dari remarks juga
            $phItems = $eligibleForPH
                ? $employeePhRosters
                    ->filter(function ($phRoster) use ($isStaticStore) {
                        $dateStr = Carbon::parse($phRoster->date)->toDateString();
                        return !($isStaticStore && Carbon::parse($dateStr)->isSunday());
                    })
                    ->map(function ($phRoster) {
                        $dateStr = Carbon::parse($phRoster->date)->toDateString();
                        $remark  = $phRoster->notes ?: 'Public Holiday';
                        return [
                            'date'    => $dateStr,
                            'display' => Carbon::parse($phRoster->date)->format('d/m/Y') . ' (' . $remark . ')',
                        ];
                    })
                    ->values()
                : collect();

            // ── Remarks: Cuti ──
            $cutiItems = $eligibleForCuti
                ? $employeeRosters
                    ->whereIn('day_type', ['Leave', 'Cuti Melahirkan'])
                    ->map(function ($r) {
                        return [
                            'date'    => Carbon::parse($r->date)->toDateString(),
                            'display' => Carbon::parse($r->date)->format('d/m/Y') . ' (' . $r->day_type . ')',
                        ];
                    })
                    ->values()
                : collect();

            // ── Combine remarks ──
            $bermasalahDates = $remarksItems
                ->concat($phItems)
                ->concat($cutiItems)
                ->unique('date')
                ->sortBy('date')
                ->pluck('display')
                ->implode(', ');

            return [
                'employee_id'      => $employee->id,
                'employee_name'    => $employee->employee_name ?? '-',
                'store_name'       => $storeName ?: '-',
                'total_hari_kerja' => $totalHariKerja,
                'total_hari_telat' => $totalHariTelat,
                'remarks'          => $bermasalahDates ?: '-',
                'period_start'     => $startDate,
                'period_end'       => $endDate,
            ];
        });
    }
}
