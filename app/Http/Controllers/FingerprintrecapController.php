<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\Roster;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class FingerprintrecapController extends Controller
{
    private const TOLERANSI_TINGGI_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];
    private const TOLERANSI_TINGGI_MENIT = 10;
    private const TOLERANSI_NORMAL_MENIT = 5;

    public function index()
    {
        Log::info('FingerprintRecap@index dipanggil');

        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return view('pages.FingerprintRecap.FingerprintRecap', compact('stores'));
    }

    public function getData(Request $request)
    {
        Log::info('FingerprintRecap@getData dipanggil', [
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'store_name' => $request->input('store_name'),
        ]);

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'store_name' => 'nullable|string|max:100',
        ], [
            'start_date.date'         => 'Format start_date tidak valid.',
            'end_date.date'           => 'Format end_date tidak valid.',
            'end_date.after_or_equal' => 'End date harus setelah atau sama dengan start date.',
            'store_name.max'          => 'Nama store terlalu panjang.',
        ]);

        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date', Carbon::now()->toDateString());
            $storeName = $request->input('store_name');

            // ── 1. Ambil semua employees + status_employee ──
            $employeesQuery = Employee::with('store:id,name')
                ->select('id', 'employee_name', 'store_id', 'status_employee')
                ->whereNotNull('pin')
                ->whereNull('deleted_at');

            if ($storeName) {
                $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
            }

            $employees   = $employeesQuery->get();
            $employeeIds = $employees->pluck('id')->toArray();

            // ── 2. Ambil semua recap dalam periode ──
            $recaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('employee_id');

            // ── 3. Ambil roster Work + Cuti + PH + shift ──
            $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
                ->whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereIn('day_type', ['Work', 'Leave', 'Cuti Melahirkan', 'Public Holiday'])
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

            $rostersByEmployee = $rosters->groupBy('employee_id');

            // ── 4. Ambil roster Public Holiday untuk Remarks ──
            $publicHolidayRosters = Roster::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('day_type', 'Public Holiday')
                ->get()
                ->groupBy('employee_id');

            // ── 5. Build result ──
            $result = $employees->map(function ($employee) use (
                $recaps, $rosters, $rostersByEmployee, $publicHolidayRosters
            ) {
                $employeeRecaps    = $recaps->get($employee->id, collect());
                $employeeRosters   = $rostersByEmployee->get($employee->id, collect());
                $employeePhRosters = $publicHolidayRosters->get($employee->id, collect());

                $storeName   = optional($employee->store)->name ?? '';
                $statusEmp   = strtoupper($employee->status_employee ?? '');
                $toleransi   = in_array($storeName, self::TOLERANSI_TINGGI_STORES)
                    ? self::TOLERANSI_TINGGI_MENIT
                    : self::TOLERANSI_NORMAL_MENIT;

                // ── Eligibilitas berdasarkan status_employee ──
                // PKWT → dapat PH + Cuti
                // OJT  → dapat PH saja
                // DW   → tidak dapat keduanya
                $eligibleForPH   = !in_array($statusEmp, ['DW']);
                $eligibleForCuti = !in_array($statusEmp, ['DW', 'On Job Training']);

                // ── Set semua tanggal dari roster (Work + Cuti) ──
                $rosterAllDates    = $employeeRosters->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
                $rosterAllDatesSet = array_flip($rosterAllDates);

                // ── Set tanggal cuti — hanya untuk karyawan yang eligible ──
                $cutiDates = $eligibleForCuti
                    ? $employeeRosters
                        ->whereIn('day_type', ['Leave', 'Cuti Melahirkan'])
                        ->pluck('date')
                        ->map(fn($d) => Carbon::parse($d)->toDateString())
                        ->toArray()
                    : [];
                $cutiDatesSet = array_flip($cutiDates);

                // ── Set tanggal Work saja (untuk hitung telat) ──
                $rosterWorkOnly  = $employeeRosters->where('day_type', 'Work');
                $rosterWorkDates = $rosterWorkOnly->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();

                // ── Hitung telat (hanya dari hari Work, bukan cuti) ──
                $telatDates = [];

                foreach ($employeeRecaps as $recap) {
                    $dateStr = Carbon::parse($recap->date)->toDateString();

                    if (!$recap->time_in && !$recap->time_out) {
                        continue;
                    }

                    if (isset($cutiDatesSet[$dateStr])) {
                        continue;
                    }

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

                // ── Total Hari Kerja (Work + Cuti yang is_counted=1) ──
                $countedDates = $employeeRecaps
                    ->where('is_counted', 1)
                    ->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->filter(fn($dateStr) => isset($rosterAllDatesSet[$dateStr]))
                    ->values()
                    ->toArray();

                // ── Public Holiday otomatis terhitung — hanya untuk yang eligible ──
                $phDates = $eligibleForPH
                    ? $employeePhRosters
                        ->pluck('date')
                        ->map(fn($d) => Carbon::parse($d)->toDateString())
                        ->toArray()
                    : [];

                $allCountedDates = array_unique(array_merge($countedDates, $phDates));
                $totalHariKerja  = count($allCountedDates);
                $totalHariTelat  = count($telatDates);

                // ── Tidak masuk = roster Work yang tidak counted, exclude cuti ──
                $tidakMasukDates = array_diff($rosterWorkDates, $countedDates, $cutiDates);

                // ── Remarks: telat + tidak masuk ──
                $remarksItems = collect(array_merge($telatDates, array_values($tidakMasukDates)))
                    ->unique()
                    ->map(fn($d) => [
                        'date'    => $d,
                        'display' => Carbon::parse($d)->format('d/m/Y'),
                    ]);

                // ── Remarks: Public Holiday — hanya untuk yang eligible ──
                $phItems = $eligibleForPH
                    ? $employeePhRosters->map(function ($phRoster) {
                        $dateStr = Carbon::parse($phRoster->date)->toDateString();
                        $remark  = $phRoster->notes ?: 'Public Holiday';
                        return [
                            'date'    => $dateStr,
                            'display' => Carbon::parse($phRoster->date)->format('d/m/Y') . ' (' . $remark . ')',
                        ];
                    })
                    : collect();

                // ── Remarks: Cuti — hanya untuk yang eligible ──
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

                // ── Combine semua remarks, sort by date ──
                $bermasalahDates = $remarksItems
                    ->concat($phItems)
                    ->concat($cutiItems)
                    ->unique('date')
                    ->sortBy('date')
                    ->pluck('display')
                    ->implode(', ');

                return [
                    'employee_name'    => $employee->employee_name ?? '-',
                    'store_name'       => $storeName ?: '-',
                    'total_hari'       => $totalHariKerja . ' hari',
                    'total_hari_telat' => $totalHariTelat . ' hari',
                    'remarks'          => $bermasalahDates ?: '-',
                    'period_in'        => Carbon::parse(request('start_date', Carbon::now()->startOfMonth()))->format('d-m-Y'),
                    'period_out'       => Carbon::parse(request('end_date', Carbon::now()))->format('d-m-Y'),
                ];
            });

            Log::info('FingerprintRecap@getData berhasil');

            return DataTables::of($result)->make(true);

        } catch (\Exception $e) {
            Log::error('FingerprintRecap@getData ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}